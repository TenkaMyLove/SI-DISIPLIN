<?php

namespace App\Http\Requests;

use App\Models\DivisiBagian;
use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $validDivisi = DivisiBagian::pluck('nama')->toArray();
        if (empty($validDivisi)) {
            $validDivisi = Pegawai::DIVISI_LIST;
        }

        return [
            'nip' => [
                'required',
                'digits:18',
                'unique:pegawai,nip',
            ],
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
            'nip.required' => 'NIP wajib diisi.',
            'nip.digits' => 'NIP harus terdiri dari 18 digit angka.',
            'nip.unique' => 'NIP sudah terdaftar dalam sistem.',
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nama.max' => 'Nama tidak boleh melebihi 255 karakter.',
            'divisi.required' => 'Divisi / Bagian wajib dipilih.',
            'divisi.in' => 'Pilihan Divisi / Bagian tidak valid.',
        ];
    }
}
