<?php

namespace App\Http\Requests;

use App\Models\Absensi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class AbsensiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $isHanyaAbsen = $this->input('status') === Absensi::STATUS_HANYA_ABSEN;
        $timeRegex = 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';

        return [
            'nip' => [
                'required',
                'digits:18',
                'exists:pegawai,nip',
            ],
            'periode' => [
                'required',
                'date',
            ],
            'jam_masuk' => [
                Rule::requiredIf(! $isHanyaAbsen),
                'nullable',
                $timeRegex,
            ],
            'jam_pulang' => [
                Rule::requiredIf(! $isHanyaAbsen),
                'nullable',
                $timeRegex,
            ],
            'status' => [
                'required',
                Rule::in(Absensi::STATUS_LIST),
            ],
            'alasan' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'durasi_menit' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $status = $this->input('status');
            $masuk = $this->input('jam_masuk');
            $pulang = $this->input('jam_pulang');

            if ($status === Absensi::STATUS_HANYA_ABSEN) {
                $hasMasuk = filled($masuk);
                $hasPulang = filled($pulang);

                if (! $hasMasuk && ! $hasPulang) {
                    $validator->errors()->add('jam_masuk', 'Isi salah satu antara Jam Masuk atau Jam Pulang.');
                } elseif ($hasMasuk && $hasPulang) {
                    $validator->errors()->add('jam_pulang', 'Untuk status Hanya Absen Masuk / Pulang, hanya isi salah satu jam absen saja.');
                }

                return;
            }

            if ($masuk && $pulang && strtotime($pulang) <= strtotime($masuk)) {
                $validator->errors()->add('jam_pulang', 'Jam Pulang harus lebih besar dari Jam Masuk.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'Pegawai wajib dipilih.',
            'nip.exists' => 'Pegawai tidak ditemukan dalam database.',
            'periode.required' => 'Tanggal (periode) wajib diisi.',
            'periode.date' => 'Format tanggal tidak valid.',
            'jam_masuk.required' => 'Jam Masuk wajib diisi pada status ini.',
            'jam_masuk.regex' => 'Format Jam Masuk harus JJ:MM (misal 08:00).',
            'jam_pulang.required' => 'Jam Pulang wajib diisi pada status ini.',
            'jam_pulang.regex' => 'Format Jam Pulang harus JJ:MM (misal 16:00).',
            'status.required' => 'Status absensi bermasalah wajib dipilih.',
            'status.in' => 'Status tidak valid. Pilihan: '.implode(', ', Absensi::STATUS_LIST).'.',
            'durasi_menit.integer' => 'Durasi harus berupa angka menit.',
            'durasi_menit.min' => 'Durasi tidak boleh bernilai negatif.',
        ];
    }
}
