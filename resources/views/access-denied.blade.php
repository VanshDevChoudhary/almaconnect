<x-layouts.centered title="Access denied — AlmaConnect">
    <div class="text-center">
        <div class="ac-icon mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-50">
            <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>

        @if ($reason === 'banned')
            <h1 class="mt-6 text-xl font-semibold text-gray-900">Account suspended</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">
                This account has been suspended. Contact
                <a class="font-medium text-indigo-600 hover:text-indigo-700"
                   href="mailto:{{ config('almaconnect.contact_email') }}">the alumni cell</a>
                if you believe this was an error.
            </p>
        @else
            <h1 class="mt-6 text-xl font-semibold text-gray-900">Application not approved</h1>
            <p class="mt-3 text-sm leading-6 text-gray-600">
                Your account application was not approved. If you believe this was a mistake,
                please contact
                <a class="font-medium text-indigo-600 hover:text-indigo-700"
                   href="mailto:{{ config('almaconnect.contact_email') }}">the alumni cell</a>.
            </p>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:text-gray-900">
                Log out
            </button>
        </form>
    </div>
</x-layouts.centered>
