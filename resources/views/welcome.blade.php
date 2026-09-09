<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'USC Vehicle Ops') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('storage/branding/e77cede7-066d-4a48-9f26-9ae7c312f839.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts / Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .float-slow { 
            animation: float 6s ease-in-out infinite; 
            transform: translateZ(0);
        }
        .float-delay { 
            animation: float 7s ease-in-out 2s infinite; 
            transform: translateZ(0);
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-16px); }
            100% { transform: translateY(0px); }
        }
        
        /* Light Mesh Pattern */
        .mesh-bg-light {
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 20% 30%, rgba(29, 163, 56, 0.08) 0px, transparent 50%),
                radial-gradient(at 80% 20%, rgba(26, 148, 51, 0.05) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(104, 193, 122, 0.08) 0px, transparent 50%),
                radial-gradient(at 10% 90%, rgba(12, 163, 12, 0.05) 0px, transparent 50%);
        }
    </style>
</head>
<body class="antialiased bg-slate-50 text-[#475467] selection:bg-usc-500 selection:text-white flex flex-col min-h-screen">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/10 backdrop-blur-sm border-b border-usc-100 shadow-sm transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Icon/Logo -->
                <div class="flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-lg sm:rounded-xl bg-usc-50 text-usc-500 border border-usc-100">
                    <svg viewBox="0 -15.43 122.88 122.88" version="1.1" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 sm:w-6 sm:h-6"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.17,34.23c-10.98-5.58-9.72-11.8,1.31-11.15l2.47,4.63l5.09-15.83C21.04,5.65,24.37,0,30.9,0H96 c6.53,0,10.29,5.54,11.87,11.87l3.82,15.35l2.2-4.14c11.34-0.66,12.35,5.93,0.35,11.62l1.95,2.99c7.89,8.11,7.15,22.45,5.92,42.48 v8.14c0,2.04-1.67,3.71-3.71,3.71h-15.83c-2.04,0-3.71-1.67-3.71-3.71v-4.54H24.04v4.54c0,2.04-1.67,3.71-3.71,3.71H4.5 c-2.04,0-3.71-1.67-3.71-3.71V78.2c0-0.2,0.02-0.39,0.04-0.58C-0.37,62.25-2.06,42.15,10.17,34.23L10.17,34.23z M30.38,58.7 l-14.06-1.77c-3.32-0.37-4.21,1.03-3.08,3.89l1.52,3.69c0.49,0.95,1.14,1.64,1.9,2.12c0.89,0.55,1.96,0.82,3.15,0.87l12.54,0.1 c3.03-0.01,4.34-1.22-3.39-4C34.96,60.99,33.18,59.35,30.38,58.7L30.38,58.7z M54.38,52.79h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0 c0,0.85-0.7,1.55-1.55,1.55h-14.4c-0.85,0-1.55-0.7-1.55-1.55l0,0C52.82,53.49,53.52,52.79,54.38,52.79L54.38,52.79z M89.96,73.15 h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0c0,0.85-0.7,1.55-1.55,1.55h-14.4c-0.85,0-1.55-0.7-1.55-1.55l0,0 C88.41,73.85,89.1,73.15,89.96,73.15L89.96,73.15z M92.5,58.7l14.06-1.77c3.32-0.37,4.21,1.03,3.08,3.89l-1.52,3.69 c-0.49,0.95-1.14,1.64-1.9,2.12c-0.89,0.55-1.96,0.82-3.15,0.87l-12.54,0.1c-3.03-0.01-4.34-1.22-3.39-4 C87.92,60.99,89.7,59.35,92.5,58.7L92.5,58.7z M18.41,73.15h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0c0,0.85-0.7,1.55-1.55,1.55h-14.4 c-0.85,0-1.55-0.7-1.55-1.55l0,0C16.86,73.85,17.56,73.15,18.41,73.15L18.41,73.15z M19.23,31.2h86.82l-3.83-15.92 c-1.05-4.85-4.07-9.05-9.05-9.05H33.06c-4.97,0-7.52,4.31-9.05,9.05L19.23,31.2v0.75V31.2L19.23,31.2z"></path></svg>
                </div>
                <span class="text-lg sm:text-xl font-bold tracking-tight text-[#02081C] font-heading hidden sm:block">USC Vehicle Ops</span>
            </div>
            
            <div class="flex items-center gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-usc-50 hover:text-usc-700">Dashboard</a>
                        
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-600">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-usc-50 hover:text-usc-700">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition duration-200 hover:-translate-y-0.5 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow pt-16 sm:pt-20">
        <section class="mesh-bg-light relative overflow-hidden flex items-center min-h-[90vh]">
            <div class="absolute inset-0 bg-grid-slate-900/[0.02] bg-[size:30px_30px]"></div>
            
            <div class="mx-auto max-w-7xl px-6 lg:px-8 relative z-10 w-full py-12 lg:py-20 grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left: Text Content -->
                <div class="text-left relative z-20">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white text-usc-500 text-xs font-bold uppercase tracking-widest mb-8 border border-usc-200 shadow-sm">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-usc-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-usc-500"></span>
                        </span>
                        Sistem Informasi Manajemen
                    </div>
                    
                    <h1 class="text-5xl lg:text-[4.5rem] font-bold font-heading tracking-tight text-[#02081C] mb-6 leading-[1.1]">
                        Pusat Kendali <br/>
                        <span class="text-usc-500">Mobil Operasional</span>
                    </h1>
                    
                    <p class="max-w-xl text-lg text-[#475467] mb-10 leading-relaxed">
                        USC Vehicle Ops adalah platform digital terintegrasi untuk pengelolaan jadwal peminjaman, pemantauan utilisasi, serta rekapitulasi biaya pemeliharaan mobil secara transparan.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto justify-center group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition-all duration-300 hover:-translate-y-1 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                                Menuju Dashboard Utama
                                <span class="transition-transform duration-300 group-hover:translate-x-1">
                                    →
                                </span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full sm:w-auto justify-center group inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-usc-500 to-emerald-500 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-usc-500/20 transition-all duration-300 hover:-translate-y-1 hover:from-usc-600 hover:to-emerald-600 hover:shadow-xl hover:shadow-usc-500/25 active:translate-y-0">
                                Masuk ke Portal
                                <span class="transition-transform duration-300 group-hover:translate-x-1">
                                    →
                                </span>
                            </a>
                            <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 text-sm uppercase tracking-wider font-bold text-[#02081C] bg-white border border-slate-200 shadow-sm rounded-xl hover:bg-slate-50 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                Jelajahi Fitur
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Right: Aesthetic UI Mockup -->
                <div class="relative z-10 hidden lg:block perspective-1000">
                    
                    <!-- Glow behind mockup -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-usc-300/30 blur-[100px] rounded-full"></div>
                    
                    <!-- Main Dashboard Window Mockup -->
                    <div class="float-slow relative bg-white border border-slate-100 shadow-2xl rounded-2xl p-6 rotate-[-2deg] hover:rotate-0 transition-transform duration-700">
                        
                        <!-- Top Bar of Mockup -->
                        <div class="flex items-center gap-2 mb-6 border-b border-slate-100 pb-4">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <!-- Stat Card -->
                            <div class="col-span-1 bg-usc-50 border border-usc-100 rounded-xl p-4">
                                <p class="text-usc-700 text-xs mb-1 font-semibold">Mobil Aktif</p>
                                <p class="text-[#02081C] text-2xl font-bold">24 <span class="text-xs font-medium text-usc-500">+3 Unit</span></p>
                            </div>
                            <div class="col-span-2 bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-end justify-between">
                                <div class="w-full flex items-end gap-2 h-10">
                                    <div class="w-1/6 bg-usc-200 h-[40%] rounded-t-md"></div>
                                    <div class="w-1/6 bg-usc-200 h-[60%] rounded-t-md"></div>
                                    <div class="w-1/6 bg-usc-200 h-[30%] rounded-t-md"></div>
                                    <div class="w-1/6 bg-usc-200 h-[80%] rounded-t-md"></div>
                                    <div class="w-1/6 bg-usc-400 h-[100%] rounded-t-md relative shadow-[0_-5px_15px_rgba(29,163,56,0.2)]">
                                        <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-usc-600"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity List -->
                        <div class="space-y-3">
                            <div class="flex items-center gap-4 bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                                <div class="w-10 h-10 rounded-lg bg-usc-50 flex items-center justify-center text-usc-500">
                                    <svg viewBox="0 -15.43 122.88 122.88" version="1.1" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"><path fill-rule="evenodd" clip-rule="evenodd" d="M10.17,34.23c-10.98-5.58-9.72-11.8,1.31-11.15l2.47,4.63l5.09-15.83C21.04,5.65,24.37,0,30.9,0H96 c6.53,0,10.29,5.54,11.87,11.87l3.82,15.35l2.2-4.14c11.34-0.66,12.35,5.93,0.35,11.62l1.95,2.99c7.89,8.11,7.15,22.45,5.92,42.48 v8.14c0,2.04-1.67,3.71-3.71,3.71h-15.83c-2.04,0-3.71-1.67-3.71-3.71v-4.54H24.04v4.54c0,2.04-1.67,3.71-3.71,3.71H4.5 c-2.04,0-3.71-1.67-3.71-3.71V78.2c0-0.2,0.02-0.39,0.04-0.58C-0.37,62.25-2.06,42.15,10.17,34.23L10.17,34.23z M30.38,58.7 l-14.06-1.77c-3.32-0.37-4.21,1.03-3.08,3.89l1.52,3.69c0.49,0.95,1.14,1.64,1.9,2.12c0.89,0.55,1.96,0.82,3.15,0.87l12.54,0.1 c3.03-0.01,4.34-1.22-3.39-4C34.96,60.99,33.18,59.35,30.38,58.7L30.38,58.7z M54.38,52.79h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0 c0,0.85-0.7,1.55-1.55,1.55h-14.4c-0.85,0-1.55-0.7-1.55-1.55l0,0C52.82,53.49,53.52,52.79,54.38,52.79L54.38,52.79z M89.96,73.15 h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0c0,0.85-0.7,1.55-1.55,1.55h-14.4c-0.85,0-1.55-0.7-1.55-1.55l0,0 C88.41,73.85,89.1,73.15,89.96,73.15L89.96,73.15z M92.5,58.7l14.06-1.77c3.32-0.37,4.21,1.03,3.08,3.89l-1.52,3.69 c-0.49,0.95-1.14,1.64-1.9,2.12c-0.89,0.55-1.96,0.82-3.15,0.87l-12.54,0.1c-3.03-0.01-4.34-1.22-3.39-4 C87.92,60.99,89.7,59.35,92.5,58.7L92.5,58.7z M18.41,73.15h14.4c0.85,0,1.55,0.7,1.55,1.55l0,0c0,0.85-0.7,1.55-1.55,1.55h-14.4 c-0.85,0-1.55-0.7-1.55-1.55l0,0C16.86,73.85,17.56,73.15,18.41,73.15L18.41,73.15z M19.23,31.2h86.82l-3.83-15.92 c-1.05-4.85-4.07-9.05-9.05-9.05H33.06c-4.97,0-7.52,4.31-9.05,9.05L19.23,31.2v0.75V31.2L19.23,31.2z"></path></svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-[#02081C] font-semibold">Innova Venturer (B 1234 CD)</p>
                                    <p class="text-xs text-[#475467] flex items-center gap-1.5 mt-0.5">
                                        Bapak Budi Diajukan sebagai Driver
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-usc-500"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                    </p>
                                </div>
                                <div class="text-xs text-usc-700 font-semibold bg-usc-100 px-2.5 py-1 rounded-md">Aktif</div>
                            </div>

                            <div class="flex items-center gap-4 bg-white border border-slate-100 rounded-xl p-3 shadow-sm">
                                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053 5.084 1.695-1.695-5.084-3.052 2.493-2.829-2.828-3.053 2.493 1.695 5.084-5.084 1.695 2.493-3.053zM9.75 9.75l-4.5 4.5" /></svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-[#02081C] font-semibold">Avanza (D 456 EF)</p>
                                    <p class="text-xs text-[#475467] flex items-center gap-1.5 mt-0.5">
                                        Jadwal Servis Berkala (Auto2000)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-amber-500"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.054-2.066.05A5.378 5.378 0 0110.5 12c0-1.28-.445-2.457-1.18-3.376.68.106 1.378.113 2.066.05.7-.063 1.393-.197 2.067-.384V9a2.5 2.5 0 005 0v-.76c.674-.187 1.367-.321 2.067-.384.68-.063 1.386-.054 2.066.05a5.378 5.378 0 01-2.226 3.89c.735.919 1.18 2.096 1.18 3.376 0 1.28-.445 2.457-1.18 3.376a5.378 5.378 0 012.226 3.89c-.68.106-1.386.113-2.066.05-.7-.063-1.393-.197-2.067-.384v-.76a2.5 2.5 0 00-5 0v.76c-.674-.187-1.367-.321-2.067-.384zM15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </p>
                                </div>
                                <div class="text-xs text-amber-700 font-semibold bg-amber-100 px-2.5 py-1 rounded-md">Pending</div>
                            </div>
                        </div>

                        <!-- Floating Element (Notification) -->
                        <div class="absolute -right-8 -top-6 float-delay bg-white border border-slate-100 p-3 rounded-2xl shadow-xl flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-usc-50 flex items-center justify-center text-usc-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </div>
                            <div>
                                <p class="text-xs text-[#475467]">Klaim BBM</p>
                                <p class="text-sm text-[#02081C] font-bold">Telah Disetujui</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-white relative z-20 border-t border-slate-100">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mb-16 text-center max-w-3xl mx-auto">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-usc-500 mb-3">Fitur Platform</p>
                    <h2 class="text-3xl font-bold font-heading text-[#02081C] sm:text-4xl mb-4">Lebih dari Sekadar Pencatatan</h2>
                    <p class="text-lg text-[#475467]">USC Vehicle Ops merevolusi bagaimana mengelola mobilitas dengan baik dan terintegrasi. Mulai dari request peminjaman mobil hingga rekonsiliasi pengeluaran yang semuanya terintegrasi dengan baik.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Feature 1 -->
                    <div class="group relative bg-slate-50 rounded-2xl p-8 transition duration-300 hover:bg-white hover:shadow-[0_20px_40px_-15px_rgba(16,185,129,0.15)] border border-transparent hover:border-emerald-100">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-50 text-emerald-600 mb-6 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-emerald-200 transition-all duration-300 border border-emerald-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Reservasi & Distribusi</h3>
                        <p class="text-slate-600 leading-relaxed text-sm">
                            Atur permohonan peminjaman unit dengan flow approval digital yang terstruktur.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group relative bg-slate-50 rounded-2xl p-8 transition duration-300 hover:bg-white hover:shadow-[0_20px_40px_-15px_rgba(59,130,246,0.15)] border border-transparent hover:border-blue-100">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 text-blue-600 mb-6 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-blue-200 transition-all duration-300 border border-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.492-3.053 5.084 1.695-1.695-5.084-3.052 2.493-2.829-2.828-3.053 2.493 1.695 5.084-5.084 1.695 2.493-3.053zM9.75 9.75l-4.5 4.5" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Pemeliharaan Mesin</h3>
                        <p class="text-slate-600 leading-relaxed text-sm">
                            Sistem mencatat riwayat perbaikan dan mengatur jadwal maintenance mobil.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group relative bg-slate-50 rounded-2xl p-8 transition duration-300 hover:bg-white hover:shadow-[0_20px_40px_-15px_rgba(245,158,11,0.15)] border border-transparent hover:border-amber-100">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 text-amber-600 mb-6 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-amber-200 transition-all duration-300 border border-amber-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Reimbursement Dana</h3>
                        <p class="text-slate-600 leading-relaxed text-sm">
                            Rekap pengeluaran BBM dan Tol menjadi lebih mudah dengan data pencatatan digital.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group relative bg-slate-50 rounded-2xl p-8 transition duration-300 hover:bg-white hover:shadow-[0_20px_40px_-15px_rgba(168,85,247,0.15)] border border-transparent hover:border-purple-100">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-purple-100 to-purple-50 text-purple-600 mb-6 group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-purple-200 transition-all duration-300 border border-purple-100">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-7 h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">Laporan & Utilisasi</h3>
                        <p class="text-slate-600 leading-relaxed text-sm">
                            Insight komprehensif berupa biaya operasional dan durasi penggunaan mobil.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-50 border-t border-slate-200 mt-auto">
        <div class="mx-auto max-w-7xl px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center justify-center px-5 py-2.5">
                <span class="text-sm font-bold">
                    &copy; {{ date('Y') }} USC Vehicle Ops
                </span>
            </div>
            
            <div class="flex items-center justify-center px-5 py-2.5">
                <p class="text-sm font-bold">
                    PT. United Steel Center Indonesia
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
