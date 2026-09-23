<?php

namespace App\Models;

use Database\Factories\ApplicationFormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationForm extends Model
{
    /** @use HasFactory<ApplicationFormFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'first_name', 'last_name', 'middle_name', 'birth_date', 'email',
        'country_code', 'phone_numbers', 'marital_status', 'about', 'accepted_rules',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'phone_numbers' => 'array',
            'accepted_rules' => 'boolean',
        ];
    }
}
