<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Explore Programs | G-Luper</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" rel="stylesheet">

    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .program-card { transition: box-shadow 0.18s, transform 0.18s; }
        .program-card:hover { box-shadow: 0 16px 40px rgba(0,0,0,0.09); transform: translateY(-3px); }
    </style>
</head>
<body class="bg-[#f7f9fc] text-slate-900 antialiased">

{{-- ── Navigation ─────────────────────────────────────────────────────── --}}
<nav class="sticky top-0 z-50 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-5 h-[60px] flex items-center gap-4">

        <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0 group">
            <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[7px] flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                <span class="text-white font-bold text-xs">G</span>
            </div>
            <span class="text-[16px] font-bold tracking-tight hidden sm:block">G-Luper</span>
        </a>

        <div class="h-[60px] flex items-center">
            <span class="px-3 h-full flex items-center text-sm font-bold text-blue-600 border-b-2 border-blue-600">
                Explore
            </span>
            @auth
            <a href="{{ route('learner.my-learning') }}"
               class="px-3 h-full flex items-center text-sm font-medium text-slate-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all">
                My Learning
            </a>
            @endauth
        </div>

        <div class="flex-1 max-w-xs mx-auto hidden lg:block">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full px-4 py-2">
                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" id="search-input" placeholder="Search programs…"
                    class="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full">
            </div>
        </div>

        <div class="ml-auto flex items-center gap-3">
            @auth
            <a href="{{ route('learner.my-learning') }}"
               class="text-sm font-semibold text-slate-700 hover:text-blue-600 transition hidden sm:block">
                My Learning
            </a>
            @else
            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition">
                Sign in
            </a>
            <a href="{{ route('register') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors">
                Join Free
            </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ── Hero ────────────────────────────────────────────────────────────── --}}
<section class="bg-white border-b border-slate-100 py-12">
    <div class="max-w-7xl mx-auto px-5 text-center">
        <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-3">
            Explore Programs
        </h1>
        <p class="text-slate-500 text-lg max-w-xl mx-auto">
            Live cohort-based learning. Join a batch, attend hands-on sessions, and build real skills with a community.
        </p>

        {{-- Stats strip --}}
        <div class="flex flex-wrap justify-center gap-8 mt-8 text-center">
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $programs->count() }}+</p>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Active Programs</p>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">4,000+</p>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Learners</p>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">2×</p>
                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Weekly Live Sessions</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Program grid ────────────────────────────────────────────────────── --}}
<main class="max-w-7xl mx-auto px-5 py-12">

    @if($programs->isEmpty())
    <div class="text-center py-20">
        <div class="text-5xl mb-4">🎓</div>
        <h3 class="text-lg font-bold text-slate-700 mb-2">No programs available right now</h3>
        <p class="text-slate-400 text-sm">New cohorts are added regularly. Check back soon.</p>
    </div>
    @else

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="programs-grid">
        @foreach($programs as $program)
        @php
            $isEnrolled  = $enrolledProgramIds->contains($program->id);
            $nextCohort  = $program->cohorts->first();
            $discountedP = $program->discounted_price;
            $hasDiscount = $program->discount_percentage > 0;
        @endphp

        <div class="program-card bg-white border border-slate-100 rounded-2xl overflow-hidden"
             data-title="{{ strtolower($program->name) }}">

            {{-- Program image or gradient placeholder --}}
            <div class="h-40 bg-gradient-to-br from-blue-500 to-indigo-700 relative overflow-hidden">
                @if($program->image)
                <img src="{{ $program->image_url }}" alt="{{ $program->name }}"
                     class="absolute inset-0 w-full h-full object-cover opacity-80">
                @endif
                @if($isEnrolled)
                <div class="absolute top-3 right-3 bg-green-500 text-white text-[11px] font-black px-2.5 py-1 rounded-full">
                    Enrolled
                </div>
                @endif
                @if($nextCohort)
                <div class="absolute bottom-3 left-3 bg-black/40 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-1 rounded-full">
                    Starts {{ \Carbon\Carbon::parse($nextCohort->start_date)->format('M j, Y') }}
                </div>
                @endif
            </div>

            <div class="p-5">
                {{-- Title + duration --}}
                <h3 class="text-base font-bold text-slate-900 leading-snug mb-1">{{ $program->name }}</h3>
                <p class="text-xs text-slate-400 mb-3">
                    {{ $program->duration }} weeks · Live sessions via Google Meet
                </p>

                {{-- Description --}}
                @if($program->description)
                <p class="text-sm text-slate-600 line-clamp-2 mb-4">{{ $program->description }}</p>
                @endif

                {{-- Price --}}
                <div class="flex items-end gap-2 mb-5">
                    <span class="text-xl font-black text-slate-900">
                        ₦{{ number_format($discountedP, 0) }}
                    </span>
                    @if($hasDiscount)
                    <span class="text-sm text-slate-400 line-through">
                        ₦{{ number_format($program->price, 0) }}
                    </span>
                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                        {{ $program->discount_percentage }}% off
                    </span>
                    @endif
                </div>

                {{-- Installment note --}}
                <p class="text-xs text-slate-400 mb-4">
                    Or ₦{{ number_format($program->installment_amount, 0) }} now + ₦{{ number_format($program->installment_amount, 0) }} later
                </p>

                {{-- CTA --}}
                @if($isEnrolled)
                <a href="{{ route('learner.my-learning') }}"
                   class="flex items-center justify-center gap-2 w-full bg-green-50 text-green-700 font-bold text-sm py-2.5 rounded-xl hover:bg-green-100 transition-colors">
                    Go to My Learning
                </a>
                @else
                <button
                    onclick="handleEnroll({{ $program->id }}, '{{ $program->name }}')"
                    data-program-id="{{ $program->id }}"
                    class="enroll-btn flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2.5 rounded-xl transition-colors">
                    Enroll Now
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @endif
</main>

{{-- ── Payment Plan Modal ───────────────────────────────────────────────── --}}
<div id="enroll-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">

        <div class="px-7 pt-7 pb-2">
            <h3 class="text-xl font-black text-slate-900" id="modal-program-name">Enroll in Program</h3>
            <p class="text-slate-500 text-sm mt-1">Choose your payment plan to continue.</p>
        </div>

        <div class="px-7 py-5 space-y-3">
            {{-- One-time --}}
            <label class="plan-option flex items-start gap-4 border-2 border-slate-200 rounded-2xl p-4 cursor-pointer hover:border-blue-400 transition-colors peer/a has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="payment_plan" value="one-time" class="sr-only" id="plan-onetime">
                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex-shrink-0 mt-0.5 flex items-center justify-center plan-radio">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600 hidden plan-dot"></div>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">Full Payment</p>
                    <p class="text-xs text-slate-500 mt-0.5">Pay once and save
                        <span class="text-green-600 font-bold" id="modal-discount"></span>.
                    </p>
                </div>
            </label>

            {{-- Installment --}}
            <label class="plan-option flex items-start gap-4 border-2 border-slate-200 rounded-2xl p-4 cursor-pointer hover:border-blue-400 transition-colors has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="payment_plan" value="installment" class="sr-only" id="plan-installment">
                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex-shrink-0 mt-0.5 flex items-center justify-center plan-radio">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600 hidden plan-dot"></div>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">50/50 Installment</p>
                    <p class="text-xs text-slate-500 mt-0.5">Pay half now, half later. No extra charge.</p>
                </div>
            </label>
        </div>

        <div class="px-7 pb-7 flex gap-3">
            <button onclick="closeModal()"
                class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm py-3 rounded-xl transition-colors">
                Cancel
            </button>
            <form id="enroll-form" action="{{ route('payment.initiate') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="program_id" id="form-program-id">
                <input type="hidden" name="cohort_id" id="form-cohort-id">
                <input type="hidden" name="payment_plan" id="form-payment-plan">
                <button type="button" onclick="submitEnroll()"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-3 rounded-xl transition-colors">
                    Proceed to Payment
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ── Auth Prompt Modal (guests) ───────────────────────────────────────── --}}
<div id="auth-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h3 class="text-xl font-black text-slate-900 mb-2">Sign in to enroll</h3>
        <p class="text-slate-500 text-sm mb-6">Create a free account or sign in to continue with your enrollment.</p>
        <div class="flex flex-col gap-3">
            <a href="{{ route('register') }}"
               class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-sm transition-colors">
                Create Free Account
            </a>
            <a href="{{ route('login') }}"
               class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl text-sm transition-colors">
                Sign In
            </a>
            <button onclick="document.getElementById('auth-modal').classList.add('hidden'); document.getElementById('auth-modal').classList.remove('flex');"
                class="text-slate-400 text-sm hover:text-slate-600 transition-colors">
                Maybe later
            </button>
        </div>
    </div>
</div>

<footer class="border-t border-slate-200 py-8 bg-white mt-10">
    <div class="max-w-7xl mx-auto px-5 flex flex-col sm:flex-row items-center justify-between gap-4">

        <div class="flex items-center gap-2 text-slate-500 text-sm">
            <div class="w-6 h-6 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[6px] flex items-center justify-center">
                <span class="text-white font-bold text-[10px]">G</span>
            </div>
            <span class="font-semibold text-slate-700">Luper</span>
            <span>&copy; {{ date('Y') }}</span>
        </div>

        <div class="flex items-center gap-6">
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">
                Privacy
            </a>
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">
                Terms
            </a>
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">
                Support
            </a>
        </div>

    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
var isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
var selectedProgramId = null;

// ── Enroll button handler ──────────────────────────────────────────────────
function handleEnroll(programId, programName) {
    if (!isAuthenticated) {
        document.getElementById('auth-modal').classList.remove('hidden');
        document.getElementById('auth-modal').classList.add('flex');
        return;
    }

    // Fetch cohort via AJAX first
    fetch('/learner/programs/' + programId + '/enroll', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ payment_plan: 'one-time' }) // temp plan just to get cohort_id
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            toastr.error(data.message || 'No available cohort for this program.');
            return;
        }

        // Store cohort_id + show modal
        document.getElementById('form-program-id').value = programId;
        document.getElementById('form-cohort-id').value  = data.cohort_id;
        document.getElementById('modal-program-name').textContent = programName;

        // Open modal
        var modal = document.getElementById('enroll-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Default to one-time
        document.getElementById('plan-onetime').checked = true;
        syncRadioStyles();
    })
    .catch(() => {
        toastr.error('Something went wrong. Please try again.');
    });
}

function closeModal() {
    var modal = document.getElementById('enroll-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modals on backdrop click
document.getElementById('enroll-modal').addEventListener('click', closeModal);
document.getElementById('auth-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
        this.classList.remove('flex');
    }
});

// ── Submit enrollment form ─────────────────────────────────────────────────
function submitEnroll() {
    var plan = document.querySelector('input[name="payment_plan"]:checked');
    if (!plan) {
        toastr.warning('Please select a payment plan.');
        return;
    }
    document.getElementById('form-payment-plan').value = plan.value;
    document.getElementById('enroll-form').submit();
}

// ── Radio styling ──────────────────────────────────────────────────────────
function syncRadioStyles() {
    document.querySelectorAll('.plan-option').forEach(function(label) {
        var radio  = label.querySelector('input[type="radio"]');
        var dot    = label.querySelector('.plan-dot');
        var circle = label.querySelector('.plan-radio');
        var active = radio.checked;
        dot.classList.toggle('hidden', !active);
        circle.classList.toggle('border-blue-600', active);
        circle.classList.toggle('border-slate-300', !active);
    });
}

document.querySelectorAll('input[name="payment_plan"]').forEach(function(radio) {
    radio.addEventListener('change', syncRadioStyles);
});

// ── Client-side search ─────────────────────────────────────────────────────
document.getElementById('search-input').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('#programs-grid [data-title]').forEach(function(card) {
        var match = !q || card.dataset.title.includes(q);
        card.style.display = match ? '' : 'none';
    });
});

// ── Toastr ─────────────────────────────────────────────────────────────────
@if(Session::has('message'))
(function() {
    var type = "{{ Session::get('alert-type', 'info') }}";
    var msg  = @json(Session::get('message'));
    toastr.options = { progressBar: true, closeButton: true, positionClass: 'toast-top-right' };
    if      (type === 'success') toastr.success(msg);
    else if (type === 'error')   toastr.error(msg);
    else if (type === 'warning') toastr.warning(msg);
    else                         toastr.info(msg);
})();
@endif
</script>
</body>
</html>