<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationFormRequest;
use App\Models\ApplicationForm;
use Illuminate\Http\RedirectResponse;

class ApplicationFormController extends Controller
{
    public function store(StoreApplicationFormRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $application = $request->safe()->except(['phones']);
        $application['phone_numbers'] = $validated['phones'] ?? null;

        ApplicationForm::create($application);

        return to_route('home')->with('success', true);
    }
}
