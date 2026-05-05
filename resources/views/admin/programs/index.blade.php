@extends('layouts.admin')
@section('title', 'Programs')

@section('content')
<div class="page-header">
    <div><h1>Programs</h1></div>
</div>

<div class="container section">


    <div style="display: flex; gap: 0; border-bottom: 1px solid var(--border); margin-bottom: 1.5rem;">
        @php
            $currentStatus = request('status', '');   // '' means All
            $tabDefs = [
                ''             => ['label' => 'All',          'count' => $counts['all']],
                'under_review' => ['label' => 'Under Review', 'count' => $counts['under_review']],
                'active'       => ['label' => 'Live',         'count' => $counts['active']],
                'draft'        => ['label' => 'Draft',        'count' => $counts['draft']],
                'inactive'     => ['label' => 'Offline',      'count' => $counts['inactive']],
            ];
        @endphp

        @foreach($tabDefs as $val => $tab)
        @php
            $isActive = $currentStatus === $val;
            // Build URL: preserve the search term, set/unset status
            $params = array_filter([
                'search' => request('search'),
                'status' => $val !== '' ? $val : null,
            ], fn($v) => $v !== null && $v !== '');
            $tabUrl = route('admin.programs.index', $params);
        @endphp
        <a href="{{ $tabUrl }}"
           style="padding: 0.6rem 1rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; white-space: nowrap;
                  color: {{ $isActive ? 'var(--blue)' : 'var(--muted)' }};
                  border-bottom: 2px solid {{ $isActive ? 'var(--blue)' : 'transparent' }};
                  margin-bottom: -1px;">
            {{ $tab['label'] }}
            <span style="font-size: 0.75rem; margin-left: 0.2rem;
                         color: {{ $isActive ? 'var(--blue)' : 'var(--muted)' }};
                         {{ $val === 'under_review' && $tab['count'] > 0 ? 'font-weight: 700;' : '' }}">
                ({{ $tab['count'] }})
            </span>
        </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem;">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <input type="text" name="search" class="form-control" style="max-width: 300px;"
               placeholder="Search programs…" value="{{ request('search') }}">
        <button type="submit" class="btn btn-outline">Search</button>
        @if(request('search'))
        <a href="{{ route('admin.programs.index', array_filter(['status' => request('status')])) }}"
           class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Mentor</th>
                    <th>Submitted</th>
                    <th>Learners</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($programs as $program)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $program->name }}</div>
                        <div class="text-muted text-small">
                            {{ $program->duration }}
                            &middot; {{ $program->modules_count }} module{{ $program->modules_count !== 1 ? 's' : '' }}
                        </div>
                    </td>
                    <td class="text-small">
                        @if($program->mentor)
                            {{ $program->mentor->first_name }} {{ $program->mentor->last_name }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted text-small">
                        {{ $program->submitted_at ? $program->submitted_at->format('M j, Y') : '—' }}
                    </td>
                    <td class="text-small">{{ $program->enrollments_count }}</td>
                    <td>
                        <span class="badge {{ match($program->status) {
                            'active'       => 'badge-green',
                            'under_review' => 'badge-yellow',
                            'inactive'     => 'badge-gray',
                            default        => 'badge-gray',
                        } }}">
                            {{ match($program->status) {
                                'active'       => 'Live',
                                'under_review' => 'Under Review',
                                'inactive'     => 'Offline',
                                default        => 'Draft',
                            } }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-sm btn-outline">Review</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 2.5rem;">
                        No programs found{{ request('search') ? ' for "' . request('search') . '"' : '' }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem;">{{ $programs->links() }}</div>

</div>
@endsection