<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationFormRequest;
use App\Models\ApplicationForm;
use Illuminate\Http\RedirectResponse;

class ApplicationFormController extends Controller
{
    public function store(StoreApplicationFormRequest $request): RedirectResponse
    {
        ApplicationForm::create($request->safe()->only([
            'first_name',
            'last_name',
            'middle_name',
            'birth_date',
            'email',
            'country_code',
            'phone_numbers',
            'marital_status',
            'about',
            'accepted_rules',
        ]));

        return to_route('home')->with('success', true);
    }
}
