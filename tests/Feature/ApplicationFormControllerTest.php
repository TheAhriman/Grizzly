<?php

namespace Tests\Feature;

use App\MaritalStatus;
use App\Models\ApplicationForm;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            ->assertSee('name="phones[0][number]"', false);
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
            'marital_status' => 1,
            'accepted_rules' => true,
        ]);
        $this->assertSame(1, DB::table('application_forms')->value('marital_status'));
        $this->get(route('home'))
            ->assertSee('Pomyślnie')
            ->assertDontSee('name="first_name"', false);
    }

    public function test_valid_phone_payload_creates_application_with_all_numbers(): void
    {
        $payload = $this->validPayload([
            'email' => null,
            'phones' => [
                ['country_code' => '+375', 'number' => '291234567'],
                ['country_code' => '+7', 'number' => '9991234567'],
            ],
        ]);

        $this->post(route('applications.store'), $payload)
            ->assertRedirectToRoute('home')
            ->assertSessionHasNoErrors();

        $application = ApplicationForm::query()->sole();
        $this->assertSame([
            ['country_code' => '+375', 'number' => '291234567'],
            ['country_code' => '+7', 'number' => '9991234567'],
        ], $application->phone_numbers);
        $this->assertSame(MaritalStatus::Single, $application->marital_status);
    }

    public function test_missing_required_fields_are_rejected_and_input_is_preserved(): void
    {
        $response = $this->from(route('home'))->post(route('applications.store'), [
            'first_name' => 'Jan',
            'email' => '',
            'phones' => [['country_code' => '', 'number' => '']],
        ]);

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors([
                'last_name' => 'Nazwisko jest wymagane.',
                'birth_date' => 'Data urodzenia jest wymagana.',
                'email' => 'Podaj adres e-mail lub co najmniej jeden numer telefonu.',
                'phones' => 'Podaj numer telefonu lub adres e-mail.',
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
            'phones' => array_fill(0, 7, ['country_code' => '+375', 'number' => '999123456']),
        ]));

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors(['phones' => 'Możesz podać maksymalnie 6 numerów telefonu.']);
        $this->assertDatabaseEmpty('application_forms');
    }

    public function test_unsupported_country_code_is_rejected(): void
    {
        $response = $this->from(route('home'))->post(route('applications.store'), $this->validPayload([
            'email' => null,
            'phones' => [['country_code' => '+48', 'number' => '291234567']],
        ]));

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors(['phones.0.country_code' => 'Wybierz prawidłowy kod kraju.']);
        $this->assertDatabaseEmpty('application_forms');
    }

    #[TestWith(['+375', '12345678'])]
    #[TestWith(['+375', '1234567890'])]
    #[TestWith(['+7', '123456789'])]
    #[TestWith(['+7', '12345678901'])]
    #[TestWith(['+375', '123-456-789'])]
    public function test_phone_length_must_match_country_code(string $countryCode, string $phoneNumber): void
    {
        $response = $this->from(route('home'))->post(route('applications.store'), $this->validPayload([
            'email' => null,
            'phones' => [['country_code' => $countryCode, 'number' => $phoneNumber]],
        ]));

        $response->assertRedirect(route('home'))
            ->assertSessionHasErrors([
                'phones.0.number' => 'Numer telefonu ma nieprawidłową liczbę cyfr dla wybranego kraju.',
            ]);
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
            'phones' => [],
            'marital_status' => '1',
            'about' => 'Kilka słów o mnie.',
            'accepted_rules' => '1',
        ], $overrides);
    }
}
