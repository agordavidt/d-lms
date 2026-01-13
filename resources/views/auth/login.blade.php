<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | G-Luper Learning</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">
    
    <div class="max-w-7xl w-full flex bg-white rounded-[3rem] shadow-2xl shadow-blue-900/10 overflow-hidden min-h-[85vh]">
        
        <!-- Left Side - Brand -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-700 to-indigo-900 p-16 flex-col justify-between relative overflow-hidden">
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
                    Unlock your <br> <span class="text-blue-200">digital potential.</span>
                </h2>
                <p class="text-blue-100/70 text-lg max-w-sm">
                    Join thousands of professionals mastering the skills of the future economy.
                </p>
            </div>

            <div class="relative z-10 text-blue-200/50 text-sm font-medium tracking-widest uppercase">
                © {{ date('Y') }} G-Luper Global
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 p-8 sm:p-16 md:p-24 flex flex-col justify-center relative">
            
            <div class="lg:hidden mb-12 flex justify-center">
                 <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg">
                    <span class="text-white font-black text-2xl">G</span>
                </div>
            </div>

            <div class="max-w-md mx-auto w-full">
                <header class="mb-10">
                    <h1 class="text-4xl font-black text-slate-900 mb-3 tracking-tight">Welcome back</h1>
                    <p class="text-slate-500 font-medium">Please enter your credentials to access your portal.</p>
                </header>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 uppercase tracking-widest mb-2" for="email">Email Address</label>
                        <input class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none @error('email') border-red-500 @enderror"
                            type="email" name="email" id="email" value="{{ old('email') }}" placeholder="name@company.com" required autofocus />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-bold text-slate-700 uppercase tracking-widest" for="password">Password</label>
                            <a class="text-xs font-bold text-blue-600 hover:text-indigo-700 transition" href="{{ route('password.request') }}">Forgot?</a>
                        </div>
                        
                        <div class="relative group">
                            <input class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-600 transition-all outline-none @error('password') border-red-500 @enderror"
                                type="password" id="password" name="password" placeholder="••••••••" required />
                            
                            <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600 transition-colors">
                                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="hidden w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.367-3.73m3.76-2.43A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.974 9.974 0 01-4.293 5.063M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-slate-600">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-5 rounded-2xl font-bold text-lg shadow-xl shadow-blue-500/20 hover:scale-[1.01] active:scale-95 transition-all">
                        Sign In to Account
                    </button>
                </form>

                <div class="mt-12 text-center">
                    <p class="text-slate-500 font-medium">
                        New to G-Luper? 
                        <a class="text-blue-600 font-bold hover:underline ml-1" href="{{ route('register') }}">Create an account</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // Password Toggle Logic
        const togglePassword = document.querySelector("#togglePassword");
        const passwordField = document.querySelector("#password");
        const eyeOpen = document.querySelector("#eyeOpen");
        const eyeClosed = document.querySelector("#eyeClosed");

        togglePassword.addEventListener("click", function () {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeOpen.classList.add("hidden");
                eyeClosed.classList.remove("hidden");
            } else {
                passwordField.type = "password";
                eyeOpen.classList.remove("hidden");
                eyeClosed.classList.add("hidden");
            }
        });

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