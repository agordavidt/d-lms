@extends('layouts.learner')

@section('title', 'My Certifications')

@section('content')
<div class="max-w-7xl mx-auto px-5 py-8">

    <div class="mb-7">
        <h1 class="text-2xl font-bold text-slate-900">Certifications</h1>
        <p class="text-sm text-slate-500 mt-1">Programs you've successfully completed.</p>
    </div>

    @if($certifications->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mb-5 text-3xl">🏅</div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">No certifications yet</h3>
        <p class="text-slate-500 text-sm mb-6 max-w-xs">
            Complete a program and get your certificate approved to see it here.
        </p>
        <a href="{{ route('learner.my-learning') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-6 py-3 rounded-xl transition-colors">
            Go to My Learning
        </a>
    </div>
    @else

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($certifications as $enrollment)
        <div class="bg-white border border-slate-100 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">
            {{-- Gold accent band --}}
            <div class="h-2 bg-gradient-to-r from-amber-400 to-yellow-500"></div>

            <div class="p-6">
                <div class="flex items-start gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-xl flex-shrink-0">
                        🎓
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-snug">
                            {{ $enrollment->program->name }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $enrollment->cohort->name ?? 'Cohort' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 mb-5">
                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-[11px] font-bold px-2.5 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Graduated
                    </span>
                </div>

                <a href="{{ route('learner.certificate.download', $enrollment->certificate_key) }}"
                   class="flex items-center justify-center gap-2 w-full bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Certificate
                </a>
            </div>
        </div>
        @endforeach
    </div>

    @endif
</div>
@endsection