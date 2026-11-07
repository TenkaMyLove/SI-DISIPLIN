<?php

namespace App\Http\Requests;

use App\Models\DivisiBagian;
use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $currentNip = $this->route('pegawai');
        if ($currentNip instanceof Pegawai) {
            $currentNip = $currentNip->nip;
        }

        $validDivisi = DivisiBagian::pluck('nama')->toArray();
        if (empty($validDivisi)) {
            $validDivisi = Pegawai::DIVISI_LIST;
        }

        return [
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'divisi' => [
                'required',
                Rule::in($validDivisi),
            ],
            'divisi_bagian_id' => [
                'nullable',
                'exists:divisi_bagian,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nama.max' => 'Nama tidak boleh melebihi 255 karakter.',
            'divisi.required' => 'Divisi / Bagian wajib dipilih.',
            'divisi.in' => 'Pilihan Divisi / Bagian tidak valid.',
        ];
    }
}
