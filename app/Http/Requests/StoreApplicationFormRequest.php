<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreApplicationFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $phoneNumbers = collect($this->input('phone_numbers', []))
            ->filter(fn (mixed $number): bool => is_string($number) && trim($number) !== '')
            ->map(fn (string $number): string => trim($number))
            ->values()
            ->all();

        $this->merge([
            'email' => $this->filled('email') ? $this->string('email')->trim()->toString() : null,
            'country_code' => $phoneNumbers === [] ? null : $this->input('country_code'),
            'phone_numbers' => $phoneNumbers === [] ? null : $phoneNumbers,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'country_code' => ['nullable', Rule::in(['+48'])],
            'phone_numbers' => ['nullable', 'array', 'max:6'],
            'phone_numbers.*' => ['nullable', 'string', 'regex:/^[0-9\s()\-]{7,20}$/'],
            'marital_status' => ['required', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'about' => ['nullable', 'string', 'max:1000'],
            'accepted_rules' => ['accepted'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $phoneNumbers = collect($this->input('phone_numbers', []))
                    ->filter(fn (mixed $number): bool => is_string($number) && trim($number) !== '');

                if (! $this->filled('email') && $phoneNumbers->isEmpty()) {
                    $validator->errors()->add('email', 'Podaj adres e-mail lub co najmniej jeden numer telefonu.');
                    $validator->errors()->add('phone_numbers.0', 'Podaj numer telefonu lub adres e-mail.');
                }

                if ($phoneNumbers->isNotEmpty() && ! $this->filled('country_code')) {
                    $validator->errors()->add('country_code', 'Wybierz kod kraju.');
                }
            },
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
            'country_code.in' => 'Wybierz prawidłowy kod kraju.',
            'phone_numbers.max' => 'Możesz podać maksymalnie 6 numerów telefonu.',
            'phone_numbers.*.regex' => 'Podaj prawidłowy numer telefonu.',
            'marital_status.required' => 'Wybierz stan cywilny.',
            'marital_status.in' => 'Wybierz prawidłowy stan cywilny.',
            'about.max' => 'Opis nie może mieć więcej niż 1000 znaków.',
            'accepted_rules.accepted' => 'Musisz zaakceptować zasady.',
        ];
    }
}
