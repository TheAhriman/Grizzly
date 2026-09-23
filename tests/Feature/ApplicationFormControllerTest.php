<?php

namespace Tests\Feature;

use App\Models\ApplicationForm;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ApplicationFormControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_page_renders_application_form(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Szukasz najlepszej oferty?')
            ->assertSee('name="first_name"', false)
            ->assertSee('name="phone_numbers[]"', false);
    }

    public function test_valid_email_payload_creates_application_and_shows_success(): void
    {
        $response = $this->post(route('applications.store'), $this->validPayload());

        $response->assertRedirectToRoute('home')
            ->assertSessionHas('success', true);
        $this->assertDatabaseHas(ApplicationForm::class, [
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'email' => 'jan@example.com',
            'marital_status' => 'single',
            'accepted_rules' => true,
        ]);
        $this->get(route('home'))
            ->assertSee('Pomyślnie')
            ->assertDontSee('name="first_name"', false);
    }

    public function test_valid_phone_payload_creates_application_with_all_numbers(): void
    {
        $payload = $this->validPayload([
            'email' => null,
            'country_code' => '+48',
            'phone_numbers' => ['29 123-45-67', '33 765-43-21'],
        ]);

        $this->post(route('applications.store'), $payload)
            ->assertRedirectToRoute('home')
            ->assertSessionHasNoErrors();

        $application = ApplicationForm::query()->sole();
        $this->assertSame(['29 123-45-67', '33 765-43-21'], $application->phone_numbers);
        $this->assertSame('+48', $application->country_code);
    }

    public function test_missing_required_fields_are_rejected_and_input_is_preserved(): void
    {
        $response = $this->from(route('home'))->post(route('applications.store'), [
            'first_name' => 'Jan',
            'email' => '',
            'phone_numbers' => [''],
        ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors([
                'last_name' => 'Nazwisko jest wymagane.',
                'birth_date' => 'Data urodzenia jest wymagana.',
                'email' => 'Podaj adres e-mail lub co najmniej jeden numer telefonu.',
                'phone_numbers.0' => 'Podaj numer telefonu lub adres e-mail.',
                'marital_status' => 'Wybierz stan cywilny.',
                'accepted_rules' => 'Musisz zaakceptować zasady.',
            ])
            ->assertSessionHasInput('first_name', 'Jan');
        $this->assertDatabaseEmpty('application_forms');
    }

    #[TestWith(['email', 'not-an-email', 'Podaj prawidłowy adres e-mail.'])]
    #[TestWith(['birth_date', '2099-01-01', 'Data urodzenia nie może być datą przyszłą.'])]
    #[TestWith(['marital_status', 'unknown', 'Wybierz prawidłowy stan cywilny.'])]
    #[TestWith(['about', 'too-long', 'Opis nie może mieć więcej niż 1000 znaków.'])]
    public function test_invalid_field_is_rejected(string $field, string $value, string $message): void
    {
        if ($field === 'about') {
            $value = str_repeat('a', 1001);
        }

        $response = $this->from(route('home'))->post(route('applications.store'), $this->validPayload([
            $field => $value,
        ]));

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors([$field => $message]);
        $this->assertDatabaseEmpty('application_forms');
    }

    public function test_more_than_six_phones_are_rejected(): void
    {
        $response = $this->from(route('home'))->post(route('applications.store'), $this->validPayload([
            'country_code' => '+48',
            'phone_numbers' => array_fill(0, 7, '999 123-45-67'),
        ]));

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors(['phone_numbers' => 'Możesz podać maksymalnie 6 numerów telefonu.']);
        $this->assertDatabaseEmpty('application_forms');
    }

    public function test_non_polish_country_code_is_rejected(): void
    {
        $response = $this->from(route('home'))->post(route('applications.store'), $this->validPayload([
            'email' => null,
            'country_code' => '+375',
            'phone_numbers' => ['29 123-45-67'],
        ]));

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors(['country_code' => 'Wybierz prawidłowy kod kraju.']);
        $this->assertDatabaseEmpty('application_forms');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_replace([
            'first_name' => 'Jan',
            'last_name' => 'Kowalski',
            'middle_name' => 'Piotr',
            'birth_date' => '1990-05-20',
            'email' => 'jan@example.com',
            'country_code' => null,
            'phone_numbers' => [],
            'marital_status' => 'single',
            'about' => 'Kilka słów o mnie.',
            'accepted_rules' => '1',
        ], $overrides);
    }
}
