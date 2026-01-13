<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account | G-Luper Learning</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    
    <div class="max-w-7xl w-full flex bg-white rounded-[3rem] shadow-2xl shadow-blue-900/10 overflow-hidden min-h-[90vh]">
        
        <!-- Left Side - Brand -->
        <div class="hidden lg:flex lg:w-5/12 bg-gradient-to-br from-blue-700 to-indigo-900 p-16 flex-col justify-between relative overflow-hidden">
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-xl">
                        <span class="text-blue-700 font-black text-2xl">G</span>
                    </div>
                    <span class="text-2xl font-bold text-white tracking-tight">Luper</span>
                </a>
            </div>

            <div class="relative z-10">
                <h2 class="text-5xl font-bold text-white leading-tight mb-6">
                    Start your <br> <span class="text-blue-200">learning journey.</span>
                </h2>
                <p class="text-blue-100/70 text-lg max-w-sm">
                    Access premium resources and join a community of forward-thinking professionals.
                </p>
            </div>

            <div class="relative z-10 text-blue-200/50 text-sm font-medium tracking-widest uppercase">
                © {{ date('Y') }} G-Luper Global
            </div>
        </div>

        <!-- Right Side - Registration Form -->
        <div class="w-full lg:w-7/12 p-8 sm:p-12 md:p-16 flex flex-col justify-center bg-white">
            
            <div class="max-w-xl mx-auto w-full">
                <header class="mb-10 text-center lg:text-left">
                    <h1 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Create Account</h1>
                    <p class="text-slate-500 font-medium">Join us today! It only takes a minute to get started.</p>
                </header>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Full Name</label>
                        <input class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none @error('name') border-red-500 @enderror"
                            type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required autofocus />
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Email Address</label>
                        <input class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none @error('email') border-red-500 @enderror"
                            type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Password</label>
                            <div class="relative">
                                <input class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none @error('password') border-red-500 @enderror"
                                    type="password" name="password" id="password" placeholder="••••••••" required />
                                <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.367-3.73m3.76-2.43A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.974 9.974 0 01-4.293 5.063M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" /></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-2">Confirm</label>
                            <div class="relative">
                                <input class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none"
                                    type="password" name="password_confirmation" id="confirmPassword" placeholder="••••••••" required />
                                <button type="button" id="togglePassword2" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg id="eyeOpen2" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg id="eyeClosed2" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.367-3.73m3.76-2.43A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.974 9.974 0 01-4.293 5.063M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
                        <p class="text-xs text-slate-600">
                            Password must contain at least 8 characters with uppercase, lowercase, numbers, and symbols.
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-5 rounded-2xl font-bold text-lg shadow-xl shadow-blue-500/20 hover:scale-[1.01] active:scale-95 transition-all mt-4">
                        Create My Account
                    </button>
                </form>

                <div class="mt-10 text-center">
                    <p class="text-slate-500 font-medium">
                        Already have an account? 
                        <a class="text-blue-600 font-bold hover:underline ml-1" href="{{ route('login') }}">Sign In</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // Password Toggles
        function setupToggle(btnId, inputId, openId, closedId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            const open = document.getElementById(openId);
            const closed = document.getElementById(closedId);

            btn.addEventListener("click", () => {
                const isVisible = input.type === "text";
                input.type = isVisible ? "password" : "text";
                open.classList.toggle("hidden", isVisible);
                closed.classList.toggle("hidden", !isVisible);
            });
        }

        setupToggle("togglePassword", "password", "eyeOpen", "eyeClosed");
        setupToggle("togglePassword2", "confirmPassword", "eyeOpen2", "eyeClosed2");

        // Toastr Notifications
        toastr.options = {
            "progressBar": true,
            "positionClass": "toast-top-right",
            "closeButton": true,
            "timeOut": "5000"
        };

        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            switch (type) {
                case 'info': toastr.info("{{ Session::get('message') }}"); break;
                case 'success': toastr.success("{{ Session::get('message') }}"); break;
                case 'warning': toastr.warning("{{ Session::get('message') }}"); break;
                case 'error': toastr.error("{{ Session::get('message') }}"); break;
            }
        @endif
    </script>
</body>
</html>