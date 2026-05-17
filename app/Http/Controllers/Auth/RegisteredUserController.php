<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:alumni,student'],
            'batch' => ['required', 'integer', 'min:1980', 'max:'.(date('Y') + 5)],
            'branch' => ['required', 'string', 'max:100', 'in:CSE,ECE,ME,CE,EE,IT,Chemical,Civil,Other'],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'terms' => ['required', 'accepted'],
        ]);

        $status = $this->determineStatus($validated['role'], $validated['email']);

        $user = DB::transaction(function () use ($validated, $status) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => $status,
            ]);

            Profile::create([
                'user_id' => $user->id,
                'batch' => $validated['batch'],
                'branch' => $validated['branch'],
                'roll_no' => $validated['roll_no'] ?? null,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Students on an institute domain auto-approve; everyone else stays pending.
     * Admin is never assignable through public registration.
     */
    protected function determineStatus(string $role, string $email): string
    {
        if ($role !== 'student') {
            return 'pending';
        }

        $domain = Str::lower(Str::after($email, '@'));

        return in_array($domain, config('almaconnect.institute_domains'), true)
            ? 'approved'
            : 'pending';
    }
}
