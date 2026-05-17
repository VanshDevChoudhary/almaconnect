<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Settings/Index', [
            'app_name' => config('app.name'),
            'institute_domains' => implode(', ', config('almaconnect.institute_domains', [])),
            'mail_mailer' => config('mail.default'),
        ]);
    }
}
