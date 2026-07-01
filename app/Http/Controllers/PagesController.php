<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PagesController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('homepage', [
            'saasPlans' => Plan::saasPlans()->ordered()->get(),
            'extraRouterPlan' => Plan::extraRouterPlan()->first(),
        ]);
    }

    public function pricing(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('pricing');
    }

    public function features(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('features');
    }

    public function aboutUs(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('about-us');
    }

    public function contactUs(Request $request): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('contact-us');
    }
}
