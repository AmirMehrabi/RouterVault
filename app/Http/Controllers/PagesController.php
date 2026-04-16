<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{
    public function index(Request $request)
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('landing');
    }

    public function pricing(Request $request)
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Show pricing page for guests
        return view('pricing');
    }

    public function features(Request $request)
    {
        // Redirect authenticated users to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Show features page for guests
        return view('features');
    }
}
