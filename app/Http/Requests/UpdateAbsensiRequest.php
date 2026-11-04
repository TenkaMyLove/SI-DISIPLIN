<?php

namespace App\Http\Requests;

use App\Models\Absensi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
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
                'required',
                'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
            ],
            'jam_pulang' => [
                'required',
                'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $masuk = $this->input('jam_masuk');
            $pulang = $this->input('jam_pulang');
            if ($masuk && $pulang) {
                if (strtotime($pulang) <= strtotime($masuk)) {
                    $validator->errors()->add('jam_pulang', 'Jam Pulang harus lebih besar dari Jam Masuk.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'nip.required' => 'Pegawai wajib dipilih.',
            'nip.exists' => 'Pegawai tidak ditemukan dalam database.',
            'periode.required' => 'Tanggal (periode) wajib diisi.',
            'jam_masuk.required' => 'Jam Masuk wajib diisi.',
            'jam_pulang.required' => 'Jam Pulang wajib diisi.',
            'status.required' => 'Status absensi bermasalah wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'durasi_menit.integer' => 'Durasi harus berupa angka menit.',
            'durasi_menit.min' => 'Durasi tidak boleh bernilai negatif.',
        ];
    }
}
