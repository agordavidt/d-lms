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

{{-- Navigation --}}
<nav class="sticky top-0 z-50 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-5 h-[60px] flex items-center gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0 group">
            <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[7px] flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                <span class="text-white font-bold text-xs">G</span>
            </div>
            <span class="text-[16px] font-bold tracking-tight hidden sm:block">Luper</span>
        </a>

        <div class="h-[60px] flex items-center">
            <span class="px-3 h-full flex items-center text-sm font-bold text-blue-600 border-b-2 border-blue-600">Explore</span>
            @auth
            <a href="{{ route('learner.my-learning') }}" class="px-3 h-full flex items-center text-sm font-medium text-slate-600 hover:text-blue-600 border-b-2 border-transparent hover:border-blue-500 transition-all">My Learning</a>
            @endauth
        </div>

        <div class="flex-1 max-w-xs mx-auto hidden lg:block">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-full px-4 py-2">
                <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="search-input" placeholder="Search programs…" class="bg-transparent text-sm text-slate-700 placeholder-slate-400 outline-none w-full">
            </div>
        </div>

        <div class="ml-auto flex items-center gap-3">
            @auth
            <a href="{{ route('learner.my-learning') }}" class="text-sm font-semibold text-slate-700 hover:text-blue-600 transition hidden sm:block">My Learning</a>
            @else
            <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition">Sign in</a>
            <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors">Join Free</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="bg-white border-b border-slate-100 py-12">
    <div class="max-w-7xl mx-auto px-5 text-center">
        <h1 class="text-4xl font-black text-slate-900 tracking-tight mb-3">Explore Programs</h1>
        <p class="text-slate-500 text-lg max-w-xl mx-auto">Live cohort-based learning. Attend hands-on sessions, and build real skills with a community.</p>
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

{{-- Programs grid --}}
<main class="max-w-7xl mx-auto px-5 py-12">

    @if($programs->isEmpty())
    <div class="text-center py-20">
        <div class="text-5xl mb-4">🎓</div>
        <h3 class="text-lg font-bold text-slate-700 mb-2">No programs available right now</h3>
        <p class="text-slate-400 text-sm">New programs are added regularly. Check back soon.</p>
    </div>
    @else

    @php
    $gradients = [
        'from-blue-500 to-indigo-600',
        'from-purple-500 to-pink-600',
        'from-indigo-500 to-cyan-600',
        'from-emerald-500 to-teal-600',
        'from-orange-500 to-amber-600',
        'from-pink-500 to-rose-600',
    ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="programs-grid">
        @foreach($programs as $i => $program)
        @php
            $isEnrolled  = $enrolledProgramIds->contains($program->id);
            $discountedP = $program->discounted_price;
            $hasDiscount = $program->discount_percentage > 0;
            $grad        = $gradients[$i % count($gradients)];
        @endphp

        <div class="program-card bg-white border border-slate-100 rounded-2xl overflow-hidden"
             data-title="{{ strtolower($program->name) }}">

            {{-- CHANGED: cover_image with gradient fallback, no cohort date --}}
            <div class="h-44 bg-gradient-to-br {{ $grad }} relative overflow-hidden">
                @if($program->cover_image)
                    <img src="{{ asset('storage/' . $program->cover_image) }}"
                         class="absolute inset-0 w-full h-full object-cover" alt="{{ $program->name }}">
                @else
                    <div class="absolute inset-0 flex items-end p-5">
                        <span class="text-white/25 font-black text-6xl tracking-tight leading-none select-none">
                            {{ strtoupper(substr($program->name, 0, 2)) }}
                        </span>
                    </div>
                @endif

                {{-- Duration badge --}}
                <div class="absolute top-3 left-3 bg-black/30 backdrop-blur-sm text-white text-[11px] font-semibold px-2.5 py-1 rounded-full">
                    {{ $program->duration }}
                </div>

                @if($isEnrolled)
                <div class="absolute top-3 right-3 bg-green-500 text-white text-[11px] font-black px-2.5 py-1 rounded-full">Enrolled</div>
                @endif
            </div>

            <div class="p-5">
                <h3 class="text-base font-bold text-slate-900 leading-snug mb-2">{{ $program->name }}</h3>

                @if($program->description)
                <p class="text-sm text-slate-600 line-clamp-2 mb-4">{{ $program->description }}</p>
                @endif

                <div class="flex items-end gap-2 mb-4">
                    <span class="text-xl font-black text-slate-900">₦{{ number_format($discountedP, 0) }}</span>
                    @if($hasDiscount)
                    <span class="text-sm text-slate-400 line-through">₦{{ number_format($program->price, 0) }}</span>
                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">{{ $program->discount_percentage }}% off</span>
                    @endif
                </div>

                <p class="text-xs text-slate-400 mb-4">Or ₦{{ number_format($program->installment_amount, 0) }} × 2 installments</p>

                @if($isEnrolled)
                <a href="{{ route('learner.my-learning') }}" class="flex items-center justify-center w-full bg-green-50 text-green-700 font-bold text-sm py-2.5 rounded-xl hover:bg-green-100 transition-colors">
                    Go to My Learning
                </a>
                @else
                <button onclick="handleEnroll({{ $program->id }}, '{{ addslashes($program->name) }}', {{ $program->discount_percentage }}, {{ $program->discounted_price }}, {{ $program->installment_amount }})"
                    class="flex items-center justify-center w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2.5 rounded-xl transition-colors">
                    Enroll Now
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</main>

{{-- ── Payment Plan Modal ── CHANGED: no cohort_id, simplified --}}
<div id="enroll-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md" onclick="event.stopPropagation()">
        <div class="px-7 pt-7 pb-2">
            <h3 class="text-xl font-black text-slate-900" id="modal-program-name">Enroll in Program</h3>
            <p class="text-slate-500 text-sm mt-1">Choose your payment plan to continue.</p>
        </div>

        <div class="px-7 py-5 space-y-3">
            <label class="plan-option flex items-start gap-4 border-2 border-slate-200 rounded-2xl p-4 cursor-pointer hover:border-blue-400 transition-colors has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="payment_plan" value="one-time" id="plan-onetime" class="sr-only">
                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex-shrink-0 mt-0.5 flex items-center justify-center plan-radio">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600 hidden plan-dot"></div>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">Full Payment</p>
                    <p class="text-xs text-slate-500 mt-0.5">Pay once — save <span id="modal-discount-text" class="text-green-600 font-bold"></span></p>
                    <p class="text-sm font-bold text-slate-800 mt-1" id="modal-full-price"></p>
                </div>
            </label>

            <label class="plan-option flex items-start gap-4 border-2 border-slate-200 rounded-2xl p-4 cursor-pointer hover:border-blue-400 transition-colors has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="payment_plan" value="installment" id="plan-installment" class="sr-only">
                <div class="w-5 h-5 rounded-full border-2 border-slate-300 flex-shrink-0 mt-0.5 flex items-center justify-center plan-radio">
                    <div class="w-2.5 h-2.5 rounded-full bg-blue-600 hidden plan-dot"></div>
                </div>
                <div>
                    <p class="font-bold text-slate-900 text-sm">50/50 Installment</p>
                    <p class="text-xs text-slate-500 mt-0.5">Pay half now, half later. No extra charge.</p>
                    <p class="text-sm font-bold text-slate-800 mt-1" id="modal-installment-price"></p>
                </div>
            </label>
        </div>

        <div class="px-7 pb-7 flex gap-3">
            <button onclick="closeEnrollModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm py-3 rounded-xl transition-colors">Cancel</button>
            {{-- CHANGED: removed cohort_id hidden input --}}
            <form id="enroll-form" action="{{ route('payment.initiate') }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="program_id" id="form-program-id">
                <input type="hidden" name="payment_plan" id="form-payment-plan">
                <button type="button" onclick="submitEnroll()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-3 rounded-xl transition-colors">
                    Proceed to Payment
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Auth Prompt Modal (guests) --}}
<div id="auth-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center" onclick="event.stopPropagation()">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h3 class="text-xl font-black text-slate-900 mb-2">Sign in to enroll</h3>
        <p class="text-slate-500 text-sm mb-6">Create a free account or sign in to continue.</p>
        <div class="flex flex-col gap-3">
            <a href="{{ route('register') }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-sm transition-colors">Create Free Account</a>
            <a href="{{ route('login') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl text-sm transition-colors">Sign In</a>
            <button onclick="document.getElementById('auth-modal').classList.add('hidden');document.getElementById('auth-modal').classList.remove('flex');" class="text-slate-400 text-sm hover:text-slate-600 transition-colors">Maybe later</button>
        </div>
    </div>
</div>

<footer class="border-t border-slate-200 py-8 bg-white mt-10">
    <div class="max-w-7xl mx-auto px-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-slate-500 text-sm">
            <div class="w-6 h-6 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-[6px] flex items-center justify-center"><span class="text-white font-bold text-[10px]">G</span></div>
            <span class="font-semibold text-slate-700">Luper</span>
            <span>&copy; {{ date('Y') }}</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">Privacy</a>
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">Terms</a>
            <a href="#" class="text-xs font-semibold text-slate-400 hover:text-blue-600 uppercase tracking-wider transition">Support</a>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
var isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
var CSRF = document.querySelector('meta[name="csrf-token"]').content;

toastr.options = { progressBar: true, closeButton: true, positionClass: 'toast-top-right' };

// ── CHANGED: handleEnroll no longer makes a pre-AJAX call to get cohort_id.
//    It validates enrollment eligibility first, then opens the modal directly.
//    If not authenticated, shows auth prompt instead.
async function handleEnroll(programId, programName, discountPct, discountedPrice, installmentAmount) {
    if (!isAuthenticated) {
        document.getElementById('auth-modal').classList.remove('hidden');
        document.getElementById('auth-modal').classList.add('flex');
        return;
    }

    // Quick eligibility check (already enrolled? program still active?)
    try {
        var res = await fetch('/learner/programs/' + programId + '/enroll', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({})
        });
        var data = await res.json();

        if (!data.success) {
            toastr.error(data.message || 'Unable to enroll at this time.');
            return;
        }
    } catch (e) {
        toastr.error('Something went wrong. Please try again.');
        return;
    }

    // Populate modal
    document.getElementById('form-program-id').value   = programId;
    document.getElementById('modal-program-name').textContent = programName;
    document.getElementById('modal-full-price').textContent   = '₦' + discountedPrice.toLocaleString();
    document.getElementById('modal-installment-price').textContent = '₦' + installmentAmount.toLocaleString() + ' today';
    document.getElementById('modal-discount-text').textContent = discountPct > 0 ? discountPct + '% discount' : '';

    // Default to one-time
    document.getElementById('plan-onetime').checked = true;
    syncRadioStyles();

    var modal = document.getElementById('enroll-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEnrollModal() {
    var modal = document.getElementById('enroll-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById('enroll-modal').addEventListener('click', closeEnrollModal);

function submitEnroll() {
    var plan = document.querySelector('input[name="payment_plan"]:checked');
    if (!plan) { toastr.warning('Please select a payment plan.'); return; }
    document.getElementById('form-payment-plan').value = plan.value;
    document.getElementById('enroll-form').submit();
}

function syncRadioStyles() {
    document.querySelectorAll('.plan-option').forEach(function(label) {
        var radio  = label.querySelector('input[type="radio"]');
        var dot    = label.querySelector('.plan-dot');
        var circle = label.querySelector('.plan-radio');
        dot.classList.toggle('hidden', !radio.checked);
        circle.classList.toggle('border-blue-600', radio.checked);
        circle.classList.toggle('border-slate-300', !radio.checked);
    });
}

document.querySelectorAll('input[name="payment_plan"]').forEach(function(r) {
    r.addEventListener('change', syncRadioStyles);
});

// Search
document.getElementById('search-input').addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    document.querySelectorAll('#programs-grid [data-title]').forEach(function(card) {
        card.style.display = (!q || card.dataset.title.includes(q)) ? '' : 'none';
    });
});

@if(Session::has('message'))
(function(){
    var type = "{{ Session::get('alert-type', 'info') }}";
    var msg  = @json(Session::get('message'));
    if(type==='success') toastr.success(msg);
    else if(type==='error') toastr.error(msg);
    else if(type==='warning') toastr.warning(msg);
    else toastr.info(msg);
})();
@endif
</script>
</body>
</html>