<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Your Email | G-Luper</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet">
    <style>body { font-family: 'DM Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-6">

   
    {{-- Card --}}
    <div class="w-full max-w-md bg-white rounded-3xl border border-slate-200 shadow-xl p-9 text-center">

        {{-- Icon --}}
        <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-900 mb-2">Check your inbox</h1>
        <p class="text-slate-500 text-sm leading-relaxed mb-2">
            We sent a verification link to
        </p>
        <p class="text-blue-600 font-semibold text-sm mb-6">
            {{ auth()->user()?->email ?? 'your email address' }}
        </p>
        <p class="text-slate-400 text-xs leading-relaxed mb-7">
            Click the link in the email to activate your G-Luper account. The link expires in <strong class="text-slate-600">60 minutes</strong>.
        </p>

        {{-- Flash: resent --}}
        @if (session('status') === 'verification-link-sent')
        <div class="mb-5 p-3.5 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            A fresh verification link has been sent to your email.
        </div>
        @endif

        {{-- Resend button --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl transition-colors text-sm mb-4">
                Resend Verification Email
            </button>
        </form>

        <div class="space-y-3 pt-1">
            <a href="{{ route('home') }}"
                class="block text-sm text-slate-500 hover:text-blue-600 transition font-medium">
                ← Back to home
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="text-xs text-slate-400 hover:text-red-500 transition font-medium">
                    Not your account? Sign out
                </button>
            </form>
        </div>
    </div>

    {{-- Help text --}}
    <p class="text-center text-xs text-slate-400 mt-6 max-w-sm leading-relaxed">
        Didn't receive the email? Check your spam or junk folder.
        Still nothing? Contact us at
        <a href="mailto:support@g-luper.com" class="text-blue-500 hover:underline">support@g-luper.com</a>
    </p>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = { progressBar: true, positionClass: 'toast-top-right', closeButton: true, timeOut: 5000 };
        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            var msg  = "{{ Session::get('message') }}";
            if (type === 'success') toastr.success(msg);
            else if (type === 'error') toastr.error(msg);
            else toastr.info(msg);
        @endif
    </script>
</body>
</html>