<?php

namespace App\Http\Requests;

use App\MaritalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phones = collect($this->input('phones', []))
            ->filter(fn (mixed $phone): bool => is_array($phone))
            ->map(fn (array $phone): array => [
                'country_code' => trim((string) ($phone['country_code'] ?? '')),
                'number' => trim((string) ($phone['number'] ?? '')),
            ])
            ->filter(fn (array $phone): bool => $phone['country_code'] !== '' || $phone['number'] !== '')
            ->values()
            ->all();
        $maritalStatus = $this->input('marital_status');

        $this->merge([
            'email' => $this->filled('email') ? $this->string('email')->trim()->toString() : null,
            'phones' => $phones === [] ? null : $phones,
            'marital_status' => is_string($maritalStatus) && ctype_digit($maritalStatus)
                ? (int) $maritalStatus
                : $maritalStatus,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'email' => ['nullable', 'required_without:phones', 'email:rfc', 'max:255'],
            'phones' => ['nullable', 'required_without:email', 'array', 'max:6'],
            'phones.*' => ['array:country_code,number'],
            'phones.*.country_code' => ['required_with:phones.*.number', Rule::in(['+375', '+7'])],
            'phones.*.number' => [
                'required_with:phones.*.country_code',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $index = explode('.', $attribute)[1];
                    $countryCode = data_get($this->input('phones'), "$index.country_code");
                    $expectedLength = match ($countryCode) {
                        '+375' => 9,
                        '+7' => 10,
                        default => null,
                    };

                    if ($expectedLength !== null && ! preg_match("/^\\d{{$expectedLength}}$/", (string) $value)) {
                        $fail('Numer telefonu ma nieprawidłową liczbę cyfr dla wybranego kraju.');
                    }
                },
            ],
            'marital_status' => ['required', Rule::enum(MaritalStatus::class)],
            'about' => ['nullable', 'string', 'max:1000'],
            'accepted_rules' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Imię jest wymagane.',
            'last_name.required' => 'Nazwisko jest wymagane.',
            'birth_date.required' => 'Data urodzenia jest wymagana.',
            'birth_date.before_or_equal' => 'Data urodzenia nie może być datą przyszłą.',
            'email.email' => 'Podaj prawidłowy adres e-mail.',
            'email.required_without' => 'Podaj adres e-mail lub co najmniej jeden numer telefonu.',
            'phones.required_without' => 'Podaj numer telefonu lub adres e-mail.',
            'phones.max' => 'Możesz podać maksymalnie 6 numerów telefonu.',
            'phones.*.country_code.in' => 'Wybierz prawidłowy kod kraju.',
            'phones.*.country_code.required_with' => 'Wybierz kod kraju.',
            'phones.*.number.required_with' => 'Podaj numer telefonu.',
            'phones.*.number.digits' => 'Numer telefonu ma nieprawidłową liczbę cyfr dla wybranego kraju.',
            'marital_status.required' => 'Wybierz stan cywilny.',
            'marital_status.in' => 'Wybierz prawidłowy stan cywilny.',
            'marital_status.enum' => 'Wybierz prawidłowy stan cywilny.',
            'about.max' => 'Opis nie może mieć więcej niż 1000 znaków.',
            'accepted_rules.accepted' => 'Musisz zaakceptować zasady.',
        ];
    }
}
