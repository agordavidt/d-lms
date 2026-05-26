@extends('layouts.learner')
@section('title', 'My Certifications')

@section('content')
<div style="max-width:900px;margin:0 auto;padding:2.5rem 2rem 5rem;font-family:'DM Sans',sans-serif;">

    <div style="margin-bottom:2rem;">
        <h1 style="font-size:1.45rem;font-weight:800;color:#0f172a;margin:0 0 .25rem;">Certifications</h1>
        <p style="font-size:.875rem;color:#64748b;margin:0;">Programs you have successfully completed.</p>
    </div>

    @if($certifications->isEmpty())
    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 2rem;text-align:center;">       
        <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin-bottom:.4rem;">No certifications yet</h3>
        <p style="font-size:.875rem;color:#64748b;max-width:280px;line-height:1.6;margin-bottom:1.5rem;">
            Complete a program and get your certificate approved to see it here.
        </p>
        <a href="{{ route('learner.my-learning') }}"
           style="background:#2563eb;color:#fff;font-size:.875rem;font-weight:700;padding:10px 22px;border-radius:10px;text-decoration:none;">
            Go to My Learning
        </a>
    </div>
    @else

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem;">
        @foreach($certifications as $enrollment)
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;transition:box-shadow .15s;"
             onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,.08)'"
             onmouseout="this.style.boxShadow='none'">     
           

            <div style="padding:1.5rem;">
                {{-- Program info --}}
                <div style="display:flex;align-items:flex-start;gap:.75rem;margin-bottom:1.25rem;">
                    <div style="width:40px;height:40px;border-radius:10px;background:#fffbeb;display:flex;align-items:center;justify-content:center;font-size:1.25rem;flex-shrink:0;">🎓</div>
                    <div style="min-width:0;">
                        <div style="font-size:.9rem;font-weight:700;color:#0f172a;line-height:1.35;margin-bottom:.2rem;">
                            {{ $enrollment->program->name }}
                        </div>
                        <div style="font-size:.75rem;color:#94a3b8;">
                            {{ $enrollment->cohort->name ?? '' }}
                        </div>
                    </div>
                </div>

                {{-- Meta --}}
                <div style="display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.25rem;font-size:.8rem;color:#64748b;">
                    @if($enrollment->final_exam_score !== null)
                    <div style="display:flex;justify-content:space-between;">
                        <span>Final exam score</span>
                        <span style="font-weight:700;color:#0f172a;">{{ number_format($enrollment->final_exam_score,0) }}%</span>
                    </div>
                    @endif
                    @if($enrollment->graduation_approved_at)
                    <div style="display:flex;justify-content:space-between;">
                        <span>Issued</span>
                        <span style="font-weight:600;color:#0f172a;">{{ $enrollment->graduation_approved_at->format('M j, Y') }}</span>
                    </div>
                    @endif
                    <div style="display:flex;justify-content:space-between;">
                        <span>Certificate no.</span>
                        <span style="font-family:monospace;font-size:.72rem;color:#0f172a;font-weight:600;">{{ $enrollment->certificate_key }}</span>
                    </div>
                </div>

                {{-- Status badge --}}
                <div style="margin-bottom:1rem;">
                    <span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#166534;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:99px;">
                        <svg width="10" height="10" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Graduated
                    </span>
                </div>

                {{-- Actions --}}
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    <a href="{{ route('learner.certificate.download', $enrollment->certificate_key) }}"
                       style="display:flex;align-items:center;justify-content:center;gap:8px;background:#0f172a;color:#fff;font-size:.82rem;font-weight:700;padding:10px;border-radius:10px;text-decoration:none;transition:background .15s;"
                       onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'">                      
                        Download Certificate
                    </a>
                    <a href="{{ route('certificate.verify', $enrollment->certificate_key) }}"
                       target="_blank"
                       style="display:flex;align-items:center;justify-content:center;gap:6px;background:transparent;color:#64748b;font-size:.78rem;font-weight:600;padding:8px;border-radius:10px;text-decoration:none;border:1.5px solid #e2e8f0;transition:border-color .15s;"
                       onmouseover="this.style.borderColor='#94a3b8'" onmouseout="this.style.borderColor='#e2e8f0'">
                        Verify Certificate ↗
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endif
</div>
@endsection