<?php

namespace App\Http\Requests;

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

        return [
            'nama' => [
                'required',
                'string',
                'max:255',
            ],
            'divisi' => [
                'required',
                Rule::in(Pegawai::DIVISI_LIST),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama pegawai wajib diisi.',
            'nama.max' => 'Nama tidak boleh melebihi 255 karakter.',
            'divisi.required' => 'Divisi wajib dipilih.',
            'divisi.in' => 'Pilihan divisi tidak valid.',
        ];
    }
}
