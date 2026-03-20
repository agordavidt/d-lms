@extends('layouts.admin')
@section('title', 'Graduates')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.graduations.index') }}">Graduations</a></div>
        <h1>Graduates</h1>
    </div>
</div>

<div class="container section">

    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
        <select name="program_id" class="form-control" style="max-width: 240px;">
            <option value="">All Programs</option>
            @foreach($programs as $p)
            <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request()->hasAny(['program_id','month']))
        <a href="{{ route('admin.graduations.graduated') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Graduate</th>
                    <th>Program</th>
                    <th>Grade</th>
                    <th>Certificate Key</th>
                    <th>Approved</th>
                </tr>
            </thead>
            <tbody>
                @forelse($graduates as $enrollment)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $enrollment->user->first_name }} {{ $enrollment->user->last_name }}</div>
                        <div class="text-muted text-small">{{ $enrollment->user->email }}</div>
                    </td>
                    <td class="text-small">{{ $enrollment->program->name }}</td>
                    <td style="font-weight: 600; font-size: 0.875rem;">
                        {{ $enrollment->final_grade_avg ? number_format($enrollment->final_grade_avg, 1) . '%' : '—' }}
                    </td>
                    <td>
                        @if($enrollment->certificate_key)
                        <code style="font-size: 0.78rem; background: var(--bg); padding: 0.2rem 0.4rem; border-radius: 4px;">{{ $enrollment->certificate_key }}</code>
                        @else
                        <span class="text-muted text-small">—</span>
                        @endif
                    </td>
                    <td class="text-muted text-small">
                        {{ $enrollment->graduation_approved_at?->format('M j, Y') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align: center; color: var(--muted); padding: 2.5rem;">No graduates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem;">{{ $graduates->links() }}</div>

</div>
@endsection