<x-layouts.centered title="Account under review — AlmaConnect">
    <div class="text-center">
        <div class="ac-icon mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50">
            <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>

        <h1 class="mt-6 text-xl font-semibold text-gray-900">Your account is under review</h1>

        <p class="mt-3 text-sm leading-6 text-gray-600">
            Thanks for joining. The alumni cell is verifying your details. You&rsquo;ll get an
            email once your account is approved &mdash; usually within 1&ndash;2 business days.
        </p>

        <div class="mt-8 space-y-3">
            <a href="mailto:{{ config('almaconnect.contact_email') }}"
               class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Contact the alumni cell
            </a>

            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" form="logout-form"
                        class="w-full rounded-lg px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:text-gray-900">
                    Log out
                </button>
            </form>
        </div>
    </div>
</x-layouts.centered>
