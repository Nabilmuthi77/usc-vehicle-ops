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
        @stack('styles')
        
        <!-- Viewer.js -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css" integrity="sha512-za6IYGZ71e1ZGIqL+vUeJbRxv4uE59/s7Jb76z/n1qge4j/L0sEaG5DqMylS79P2Q6R9Tq+w8ZfI/1U6D5D5tw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js" integrity="sha512-EC3CQ+2OkM+ZKsM1rxpn00+yE0QcjOvhqxZVqA8MfvVtIgB50y7NGK1VjR5x2r2O+q/zJ9K90v7F20B6rQ4F9A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </head>
    <body class="font-sans antialiased text-[15px]" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen bg-gray-50">

            {{-- Mobile sidebar overlay --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition-opacity ease-linear duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-linear duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden"
                style="display: none;"
            ></div>

            {{-- Sidebar (mobile: off-canvas, desktop: fixed) --}}
            <div
                x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="fixed inset-y-0 left-0 z-50 w-64 lg:hidden"
                style="display: none;"
            >
                @include('layouts.partials.sidebar')
            </div>

            <div class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:block lg:w-64">
                @include('layouts.partials.sidebar')
            </div>

            {{-- Main column --}}
            <div class="lg:pl-64 flex flex-col min-h-screen">
                @include('layouts.partials.topbar')

                @isset($header)
                    <header class="bg-white border-b border-gray-200">
                        <div class="px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                    <x-alert />
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
        <script>
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        </script>
    </body>
</html>

