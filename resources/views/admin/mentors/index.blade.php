@extends('layouts.admin')
@section('title', 'Mentors')

@section('content')
<div class="page-header">
    <div><h1>Mentors</h1></div>
    <a href="{{ route('admin.mentors.create') }}" class="btn btn-primary">Add Mentor</a>
</div>

<div class="container section">

    {{-- Filters --}}
    <form method="GET" style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap; align-items: flex-end;">
        <div>
            <input type="text" name="search" class="form-control" style="width: 240px;"
                   placeholder="Name or email…" value="{{ request('search') }}">
        </div>
        <div>
            <select name="status" class="form-control" style="width: 140px;">
                <option value="">Any Status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.mentors.index') }}" class="btn btn-ghost">Clear</a>
        @endif
    </form>

    <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 0.75rem;">
        {{ $mentors->total() }} mentor{{ $mentors->total() !== 1 ? 's' : '' }}
    </div>

    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>Mentor</th>
                    <th>Programs</th>
                    <th>Live</th>
                    <th>Account</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($mentors as $mentor)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $mentor->first_name }} {{ $mentor->last_name }}</div>
                        <div class="text-muted text-small">{{ $mentor->email }}</div>
                    </td>
                    <td style="font-size: 0.875rem; font-weight: 600;">{{ $mentor->programs_count }}</td>
                    <td style="font-size: 0.875rem; font-weight: 600; color: var(--success);">{{ $mentor->active_programs_count }}</td>
                    <td>
                        <span class="badge {{ match($mentor->status) {
                            'active'    => 'badge-green',
                            'suspended' => 'badge-red',
                            default     => 'badge-gray',
                        } }}">{{ ucfirst($mentor->status) }}</span>
                    </td>
                    <td class="text-muted text-small">{{ $mentor->created_at->format('M j, Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 0.35rem;">
                            <a href="{{ route('admin.mentors.show', $mentor->id) }}" class="btn btn-sm btn-ghost">View</a>
                            <a href="{{ route('admin.mentors.edit', $mentor->id) }}" class="btn btn-sm btn-ghost">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--muted); padding: 3rem;">
                        No mentors yet. <a href="{{ route('admin.mentors.create') }}" style="color: var(--blue);">Add one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div class="text-muted text-small">
            @if($mentors->total() > 0)
                Showing {{ $mentors->firstItem() }}–{{ $mentors->lastItem() }} of {{ $mentors->total() }}
            @endif
        </div>
        {{ $mentors->withQueryString()->links() }}
    </div>

</div>
@endsection