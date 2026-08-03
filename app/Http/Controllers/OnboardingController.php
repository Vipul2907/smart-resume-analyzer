<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->user()->onboarding_completed_at) {
            return redirect()->route('dashboard');
        }

        return view('onboarding');
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'target_role' => ['required', 'string', 'max:255'],
            'experience_level' => ['required', 'string', 'max:50'],
        ]);

        $request->user()->update($attributes + ['onboarding_completed_at' => now()]);

        return redirect()->route('dashboard');
    }
}
