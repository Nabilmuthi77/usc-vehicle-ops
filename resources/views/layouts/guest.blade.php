@props(['title' => null])
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' — '.config('app.name') : config('app.name', 'USC Vehicle Ops') }}</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('storage/branding/e77cede7-066d-4a48-9f26-9ae7c312f839.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>
    <body class="min-h-screen font-sans text-slate-800"
        style="
            background:
                radial-gradient(
                    ellipse at 100% 60%,
                    rgb(29 163 56 / 18%) 0%,
                    rgb(232 246 235) 48%,
                    #f8faf9 80%
                );
        ">

        <div class="flex min-h-screen items-center justify-center px-4 py-8">

            <main class="w-full max-w-[410px]">

                {{-- Brand --}}
                <div class="mb-6 text-center">

                    <a href="/" class="inline-flex flex-col items-center">

                        {{-- Logo --}}
                        <div
                            class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-white shadow-[4px_7px_16px_rgba(29,163,56,0.22)]">
                            <svg viewBox="0 -15.43 122.88 122.88" version="1.1" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-usc-500"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.17,34.23c-10.98-5.58-9.72-11.8,1.31-11.15l2.47,4.63l5.09-15.83C21.04,5.65,24.37,0,30.9,0H96 c6.53,0,10.29,5.54,11.87,11.87l3.82,15.35l2.2-4.14c11.34-0.66,12.35,5.93,0.35,11.62l1.95,2.99c7.89,8.11,7.15,22.45,5.92,42.48 v8.14c0,2.04-1.67,3.71-3.71,3.71h-15.83c-2.04,0-3.71-1.67-3.71-3.71v-4.54H24.04v4.54c0,2.04-1.67,3.71-3.71,3.71H4.5 c-2.04,0-3.71-1.67-3.71-3.71V78.2c0-0.2,0.02-0.39,0.04-0.58C-0.37,62.25-2.06,42.15,10.17,34.23L10.17,34.23z M30.38,58.7 l-14.06-1.77c-3.32-0.37-4.21,1.03-3.08,3.89l1.52,3.69c0.49,0.95,1.14,1.64,1.9,2.12c0.89,0.55,1.96,0.82,3.15,0.87l12.54,0.1 c3.03-0.01,4.34-1.22-3.39-4C34.96,60.99,33.18,59.35,30.38,58.7L30.38,58.7z M54.38,52.79h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0 c0,0.85-0.7,1.55-1.55,1.55h-14.4c-0.85,0-1.55-0.7-1.55-1.55l0,0C52.82,53.49,53.52,52.79,54.38,52.79L54.38,52.79z M89.96,73.15 h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0c0,0.85-0.7,1.55-1.55,1.55h-14.4c-0.85,0-1.55-0.7-1.55-1.55l0,0 C88.41,73.85,89.1,73.15,89.96,73.15L89.96,73.15z M92.5,58.7l14.06-1.77c3.32-0.37,4.21,1.03,3.08,3.89l-1.52,3.69 c-0.49,0.95-1.14,1.64-1.9,2.12c-0.89,0.55-1.96,0.82-3.15,0.87l-12.54,0.1c-3.03-0.01-4.34-1.22-3.39-4 C87.92,60.99,89.7,59.35,92.5,58.7L92.5,58.7z M18.41,73.15h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0c0,0.85-0.7,1.55-1.55,1.55h-14.4 c-0.85,0-1.55-0.7-1.55-1.55l0,0C16.86,73.85,17.56,73.15,18.41,73.15L18.41,73.15z M19.23,31.2h86.82l-3.83-15.92 c-1.05-4.85-4.07-9.05-9.05-9.05H33.06c-4.97,0-7.52,4.31-9.05,9.05L19.23,31.2v0.75V31.2L19.23,31.2z"></path></svg>
                        </div>


                        {{-- Site Name --}}
                        <h1 class="font-heading text-2xl font-bold text-[#02081C]">
                            USC Vehicle Ops
                        </h1>

                    </a>

                </div>


                {{-- Authentication Card --}}
                <div
                    class="rounded-2xl border border-usc-100 bg-white p-6 shadow-[0_18px_45px_rgba(16,24,40,0.08),0_4px_12px_rgba(29,163,56,0.05)]">
                    
                    {{-- Alert --}}
                    @include('components.alert')

                    {{ $slot }}
                </div>


                {{-- Footer --}}
                <p class="mt-4 text-center text-[11px] text-slate-400">
                    &copy; {{ date('Y') }} USC Vehicle Ops. All rights reserved.
                </p>

            </main>

        </div>
    </body>
</html>

