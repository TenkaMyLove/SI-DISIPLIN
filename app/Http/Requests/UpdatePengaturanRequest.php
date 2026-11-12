<?php

namespace App\Http\Requests;

use App\Models\Pengaturan;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $timeRegex = '/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/';

        $rules = [
            'selasa_kamis_masuk' => ['nullable', 'regex:' . $timeRegex],
            'selasa_kamis_pulang' => ['nullable', 'regex:' . $timeRegex],
            'jam_masuk_standar' => ['nullable', 'regex:' . $timeRegex],
            'jam_pulang_standar' => ['nullable', 'regex:' . $timeRegex],
        ];

        foreach (array_keys(Pengaturan::DAYS) as $dayKey) {
            $rules["{$dayKey}_masuk"] = ['nullable', 'regex:' . $timeRegex];
            $rules["{$dayKey}_pulang"] = ['nullable', 'regex:' . $timeRegex];
            $rules["{$dayKey}_flex"] = ['nullable', 'boolean'];
            $rules["{$dayKey}_flex_masuk_mulai"] = ['nullable', 'regex:' . $timeRegex];
            $rules["{$dayKey}_flex_masuk_akhir"] = ['nullable', 'regex:' . $timeRegex];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $currentJadwal = Pengaturan::getJadwal();
            $input = $this->all();

            foreach (array_keys(Pengaturan::DAYS) as $dayKey) {
                $resolved = Pengaturan::resolveDayInput($dayKey, $input, $currentJadwal[$dayKey]);
                $label = $resolved['label'];

                $masukMin = Pengaturan::timeToMinutes($resolved['masuk']);
                $pulangMin = Pengaturan::timeToMinutes($resolved['pulang']);

                if ($pulangMin <= $masukMin) {
                    $validator->errors()->add("{$dayKey}_pulang", "Jam Pulang {$label} harus lebih besar dari Jam Masuk.");
                    continue;
                }

                if (! $resolved['is_flex']) {
                    continue;
                }

                $flexMulaiMin = Pengaturan::timeToMinutes($resolved['flex_masuk_mulai']);
                $flexAkhirMin = Pengaturan::timeToMinutes($resolved['flex_masuk_akhir']);

                if ($flexMulaiMin > $masukMin) {
                    $validator->errors()->add(
                        "{$dayKey}_flex_masuk_mulai",
                        "Batas awal masuk flexi {$label} tidak boleh melebihi Jam Masuk."
                    );
                }

                if ($flexAkhirMin < $masukMin) {
                    $validator->errors()->add(
                        "{$dayKey}_flex_masuk_akhir",
                        "Batas akhir masuk flexi {$label} tidak boleh lebih awal dari Jam Masuk."
                    );
                }

                if ($flexAkhirMin < $flexMulaiMin) {
                    $validator->errors()->add(
                        "{$dayKey}_flex_masuk_akhir",
                        "Batas akhir masuk flexi {$label} harus lebih besar atau sama dengan batas awal masuk flexi."
                    );
                }

                if ($flexAkhirMin >= $pulangMin) {
                    $validator->errors()->add(
                        "{$dayKey}_flex_masuk_akhir",
                        "Batas akhir masuk flexi {$label} harus lebih awal dari Jam Pulang."
                    );
                }
            }
        });
    }
}
