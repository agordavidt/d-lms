<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Simulation | G-Luper</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex items-center justify-center p-6">
    
    <div class="max-w-md w-full">
        <!-- Payment Card -->
        <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-200/50 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white text-center">
                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-2">Payment Simulation</h2>
                <p class="text-indigo-100 text-sm">Test payment gateway (Development mode)</p>
            </div>

            <!-- Payment Details -->
            <div class="p-8 space-y-6">
                <div class="bg-slate-50 rounded-2xl p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Program</span>
                        <span class="text-slate-900 font-bold">{{ $payment->metadata['program_name'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Payment Plan</span>
                        <span class="text-slate-900 font-semibold">{{ ucfirst(str_replace('-', ' ', $payment->payment_plan)) }}</span>
                    </div>
                    @if($payment->payment_plan === 'installment')
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 font-medium">Installment</span>
                        <span class="text-indigo-600 font-bold">{{ $payment->installment_number }} of 2</span>
                    </div>
                    @endif
                    <div class="border-t border-slate-200 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-600 font-medium">Amount</span>
                            <span class="text-slate-900 font-medium">₦{{ number_format($payment->amount, 2) }}</span>
                        </div>
                        @if($payment->discount_amount > 0)
                        <div class="flex justify-between items-center text-green-600 mt-2">
                            <span class="font-medium">Discount ({{ $payment->program->discount_percentage }}%)</span>
                            <span class="font-semibold">-₦{{ number_format($payment->discount_amount, 2) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-4 border-2 border-indigo-200">
                        <div class="flex justify-between items-center">
                            <span class="text-indigo-900 font-bold text-lg">Total to Pay</span>
                            <span class="text-indigo-600 font-black text-2xl">₦{{ number_format($payment->final_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Transaction Info -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <div class="flex gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-amber-900 font-bold text-sm mb-1">Development Mode</p>
                            <p class="text-amber-700 text-xs">This is a simulated payment. Click the button below to complete your enrollment instantly.</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Actions -->
                <form action="{{ route('payment.callback') }}" method="GET" class="space-y-4">
                    <input type="hidden" name="reference" value="{{ $payment->reference }}">
                    <input type="hidden" name="status" value="successful">
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-4 rounded-2xl font-bold text-lg hover:shadow-xl hover:shadow-indigo-300 transition-all transform hover:scale-[1.02] active:scale-95">
                        Complete Payment (Simulate Success)
                    </button>
                    
                    <a href="{{ route('learner.dashboard') }}" class="block text-center text-slate-600 hover:text-slate-900 font-semibold transition">
                        Cancel Payment
                    </a>
                </form>

                <!-- Reference Info -->
                <div class="text-center pt-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400 font-medium uppercase tracking-wider mb-1">Transaction Reference</p>
                    <p class="text-slate-600 font-mono text-sm">{{ $payment->reference }}</p>
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center">
            <p class="text-xs text-slate-500">
                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                Secured by G-Luper Payment System
            </p>
        </div>
    </div>

</body>
</html>