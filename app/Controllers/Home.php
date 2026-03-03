<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('LandingPage');
    }

    public function auth(): string
    {
        return view('AuthPage');
    }
}
