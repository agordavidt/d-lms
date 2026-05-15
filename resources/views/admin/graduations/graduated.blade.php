@extends('layouts.app')
@section('title', 'Graduates')

@section('content')

<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.graduations.index') }}">Graduation Approvals</a></div>
        <h1>Graduates</h1>
    </div>
</div>

<div class="grad-page" style="padding-top:0;">

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.graduations.graduated') }}"
          style="display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap;">
        <select name="program_id" class="form-control" style="width:220px;" onchange="this.form.submit()">
            <option value="">All Programs</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="month" class="form-control" style="width:160px;" onchange="this.form.submit()">
            <option value="">All Months</option>
            @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endforeach
        </select>
        @if(request('program_id') || request('month'))
        <a href="{{ route('admin.graduations.graduated') }}" class="btn btn-ghost btn-sm">Clear</a>
        @endif
    </form>

    @if($graduates->isEmpty())
    <div class="card card-body" style="text-align:center;padding:48px;color:var(--muted);">
        No graduates found for the selected filters.
    </div>
    @else

    <table class="grant-table">
        <thead>
            <tr>
                <th>Graduate</th>
                <th>Program</th>
                <th>Final Grade</th>
                <th>Certificate</th>
                <th>Approved</th>
                <th>Approved By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($graduates as $enrollment)
            <tr>
                <td>
                    <div style="font-weight:600;">{{ $enrollment->user->full_name }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $enrollment->user->email }}</div>
                </td>
                <td>
                    <div>{{ $enrollment->program->name }}</div>
                    <div style="font-size:12px;color:var(--muted);">{{ $enrollment->cohort->name ?? '' }}</div>
                </td>
                <td>
                    @if($enrollment->final_grade_avg)
                    <span class="score-badge {{ $enrollment->final_grade_avg >= 80 ? 'high' : 'medium' }}">
                        {{ number_format($enrollment->final_grade_avg, 1) }}%
                    </span>
                    @else
                    <span style="font-size:12px;color:var(--muted);">—</span>
                    @endif
                </td>
                <td>
                    @if($enrollment->certificate_key)
                    <a href="{{ route('certificate.verify', $enrollment->certificate_key) }}"
                       target="_blank"
                       style="font-size:12px;color:var(--blue);font-weight:600;text-decoration:none;">
                        {{ $enrollment->certificate_key }}
                    </a>
                    @else
                    <span style="font-size:12px;color:var(--muted);">—</span>
                    @endif
                </td>
                <td style="font-size:13px;color:var(--muted);">
                    {{ $enrollment->graduation_approved_at?->format('M d, Y') ?? '—' }}
                </td>
                <td style="font-size:13px;color:var(--muted);">
                    {{ $enrollment->approver?->full_name ?? 'System' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:16px;">
        {{ $graduates->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection