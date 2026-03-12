@extends('layouts.learner')

@section('title', 'Payment Simulation')

@section('content')
<div style="font-family:'DM Sans',sans-serif; min-height:100vh; background:#f8fafc; display:flex; align-items:center; justify-content:center; padding:40px 24px;">

    <div style="width:100%; max-width:520px;">

        {{-- Top nav --}}
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:28px;">
            <a href="{{ route('learner.dashboard') }}"
               style="display:flex; align-items:center; gap:6px; color:#64748b; font-size:14px; font-weight:600; text-decoration:none;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
                Cancel
            </a>
        </div>

        {{-- Card --}}
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">

            {{-- Header --}}
            <div style="padding:28px 32px; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                    <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#f59e0b; background:#fef3c7; padding:3px 10px; border-radius:100px;">
                        Development Mode
                    </span>
                </div>
                <h1 style="font-size:22px; font-weight:800; color:#0f172a; margin:12px 0 4px; letter-spacing:-0.02em;">Payment Simulation</h1>
                <p style="font-size:14px; color:#94a3b8; margin:0;">Test payment gateway — no real charges</p>
            </div>

            {{-- Details --}}
            <div style="padding:28px 32px; border-bottom:1px solid #f1f5f9;">

                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f8fafc;">
                    <span style="font-size:14px; color:#64748b;">Program</span>
                    <span style="font-size:14px; font-weight:700; color:#0f172a;">{{ $payment->metadata['program_name'] }}</span>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f8fafc;">
                    <span style="font-size:14px; color:#64748b;">Payment Plan</span>
                    <span style="font-size:14px; font-weight:600; color:#0f172a;">{{ ucfirst(str_replace('-', ' ', $payment->payment_plan)) }}</span>
                </div>

                @if($payment->payment_plan === 'installment')
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f8fafc;">
                    <span style="font-size:14px; color:#64748b;">Installment</span>
                    <span style="font-size:14px; font-weight:700; color:#6366f1;">{{ $payment->installment_number }} of 2</span>
                </div>
                @endif

                @if($payment->discount_amount > 0)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f8fafc;">
                    <span style="font-size:14px; color:#64748b;">Discount ({{ $payment->program->discount_percentage }}%)</span>
                    <span style="font-size:14px; font-weight:600; color:#16a34a;">−₦{{ number_format($payment->discount_amount, 2) }}</span>
                </div>
                @endif

                {{-- Total --}}
                <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 0 0;">
                    <span style="font-size:15px; font-weight:700; color:#0f172a;">Total to Pay</span>
                    <span style="font-size:22px; font-weight:800; color:#4f46e5;">₦{{ number_format($payment->final_amount, 2) }}</span>
                </div>

            </div>

            {{-- Actions --}}
            <div style="padding:28px 32px;">
                <form action="{{ route('payment.callback') }}" method="GET">
                    <input type="hidden" name="reference" value="{{ $payment->reference }}">
                    <input type="hidden" name="status" value="successful">

                    <button type="submit"
                            style="width:100%; background:#4f46e5; color:#fff; padding:15px; border-radius:10px; border:none; font-size:15px; font-weight:700; cursor:pointer; margin-bottom:12px; box-shadow:0 4px 14px rgba(79,70,229,.2);">
                        Complete Payment (Simulate Success)
                    </button>
                </form>

                <div style="padding-top:20px; border-top:1px solid #f1f5f9; text-align:center;">
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin:0 0 4px;">Transaction Reference</p>
                    <p style="font-size:13px; color:#475569; font-family:monospace; margin:0;">{{ $payment->reference }}</p>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection