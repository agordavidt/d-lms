
@extends('layouts.admin')
@section('title', $program->name)

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="{{ route('admin.programs.index') }}">Programs</a></div>
        <h1>{{ $program->name }}</h1>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="badge {{ match($program->status) {
            'active'       => 'badge-green',
            'under_review' => 'badge-yellow',
            'inactive'     => 'badge-gray',
            default        => 'badge-gray',
        } }}" style="padding: 0.35rem 0.9rem;">
            {{ match($program->status) { 'active' => 'Live', 'under_review' => 'Under Review', 'inactive' => 'Offline', default => 'Draft' } }}
        </span>

        @if($program->status === 'under_review')
            <button onclick="openModal('publish-modal')" class="btn btn-primary btn-sm">Publish</button>
            <button onclick="openModal('reject-modal')" class="btn btn-outline btn-sm" style="color: var(--error); border-color: #fca5a5;">Return to Mentor</button>
        @elseif($program->status === 'active')
            <button onclick="openModal('offline-modal')" class="btn btn-ghost btn-sm">Take Offline</button>
        @elseif($program->status === 'inactive')
            <form method="POST" action="{{ route('admin.programs.restore', $program) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">Restore to Live</button>
            </form>
        @endif

        @if(!$program->enrollments()->exists())
        <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" style="display:inline;"
              onsubmit="return confirm('Permanently delete this program?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--error);">Delete</button>
        </form>
        @endif
    </div>
</div>

<div class="container section">
<div style="display: grid; grid-template-columns: 1fr 300px; gap: 2rem; align-items: start;">

    {{-- Curriculum preview --}}
    <div>
        <h2 style="font-family: 'Source Serif 4', serif; font-size: 1.05rem; margin-bottom: 1rem;">Curriculum</h2>

        @forelse($program->modules as $module)
        <div class="card" style="margin-bottom: 1rem;">
            <div style="padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border); font-weight: 600; display: flex; justify-content: space-between;">
                <span>{{ $module->title }}</span>
                <span class="text-muted text-small">{{ $module->weeks->count() }} week{{ $module->weeks->count() !== 1 ? 's' : '' }}</span>
            </div>

            @foreach($module->weeks as $week)
            <div style="border-bottom: 1px solid var(--border);">
                <div style="padding: 0.75rem 1.25rem 0.75rem 2rem; background: var(--bg); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span class="text-muted text-small">Week {{ $week->week_number }} &nbsp;&middot;&nbsp;</span>
                        <span style="font-size: 0.875rem; font-weight: 500;">{{ $week->title }}</span>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <span class="text-muted text-small">{{ $week->contents->count() }} items</span>
                        @if($week->has_assessment)
                        <span class="badge badge-blue" style="font-size: 0.7rem;">Assessment · {{ $week->assessment?->questions?->count() ?? 0 }} Qs</span>
                        @endif
                    </div>
                </div>

                @foreach($week->contents as $content)
                <div style="padding: 0.5rem 1.25rem 0.5rem 3rem; border-bottom: 1px solid var(--border); display: flex; gap: 0.75rem; align-items: center; font-size: 0.875rem;">
                    <span style="font-size: 0.7rem; text-transform: uppercase; color: var(--muted); font-weight: 600; min-width: 44px;">{{ $content->content_type }}</span>
                    <span>{{ $content->title }}</span>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @empty
        <div class="card card-body" style="color: var(--muted); text-align: center;">No curriculum content yet.</div>
        @endforelse
    </div>

    {{-- Right: program info + actions --}}
    <div>
        {{-- Cover image --}}
        @if($program->cover_image)
        <div style="border-radius: 8px; overflow: hidden; margin-bottom: 1rem; border: 1px solid var(--border);">
            <img src="{{ asset('storage/' . $program->cover_image) }}" style="width: 100%; display: block;" alt="">
        </div>
        @endif

        <div class="card card-body" style="margin-bottom: 1rem;">
            <div style="font-weight: 600; margin-bottom: 0.75rem; font-family: 'Source Serif 4', serif;">Details</div>
            <div style="display: grid; gap: 0.6rem; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Mentor</span>
                    <span>{{ $program->mentor?->first_name }} {{ $program->mentor?->last_name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Duration</span>
                    <span>{{ $program->duration }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Price</span>
                    <span>₦{{ number_format($program->price, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Pass Average</span>
                    <span>{{ $program->min_passing_average }}%</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Submitted</span>
                    <span>{{ $program->submitted_at?->format('M j, Y') ?? '—' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span class="text-muted">Learners</span>
                    <span>{{ $stats['enrolled'] ?? $program->enrollments()->count() }}</span>
                </div>
            </div>
        </div>

        <div class="card card-body">
            <div style="font-weight: 600; margin-bottom: 0.5rem; font-family: 'Source Serif 4', serif;">Description</div>
            <p style="font-size: 0.875rem; color: var(--muted); line-height: 1.6;">{{ $program->description }}</p>
        </div>

        @if($program->review_notes)
        <div class="card card-body" style="margin-top: 1rem; background: #fffbeb; border-color: #fde68a;">
            <div style="font-size: 0.8rem; font-weight: 600; color: #92400e; margin-bottom: 0.4rem;">Previous Feedback</div>
            <p style="font-size: 0.875rem; color: #92400e;">{{ $program->review_notes }}</p>
        </div>
        @endif
    </div>

</div>
</div>

{{-- Publish modal --}}
<div class="modal-overlay" id="publish-modal">
    <div class="modal" style="max-width: 460px;">
        <button class="modal-close" onclick="closeModal('publish-modal')">&#215;</button>
        <h2>Publish Program</h2>
        <p style="color: var(--muted); font-size: 0.875rem; margin-bottom: 1.25rem;">
            This will make the program visible to learners on the Explore page.
        </p>
        <form method="POST" action="{{ route('admin.programs.publish', $program) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Note to Mentor (optional)</label>
                <textarea name="review_notes" class="form-control" rows="3" maxlength="500"
                          placeholder="Any feedback or guidance…"></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Publish</button>
                <button type="button" onclick="closeModal('publish-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Reject modal --}}
<div class="modal-overlay" id="reject-modal">
    <div class="modal" style="max-width: 460px;">
        <button class="modal-close" onclick="closeModal('reject-modal')">&#215;</button>
        <h2>Return to Mentor</h2>
        <p style="color: var(--muted); font-size: 0.875rem; margin-bottom: 1.25rem;">
            The program will return to Draft status. Explain what needs to be revised.
        </p>
        <form method="POST" action="{{ route('admin.programs.reject', $program) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Feedback <span style="color:var(--error)">*</span></label>
                <textarea name="review_notes" class="form-control" rows="4" required maxlength="500"
                          placeholder="Explain what changes are needed before this can be published…"></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="background: var(--error); border-color: var(--error);">Return to Mentor</button>
                <button type="button" onclick="closeModal('reject-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Take offline modal --}}
<div class="modal-overlay" id="offline-modal">
    <div class="modal" style="max-width: 460px;">
        <button class="modal-close" onclick="closeModal('offline-modal')">&#215;</button>
        <h2>Take Program Offline</h2>
        <p style="color: var(--muted); font-size: 0.875rem; margin-bottom: 1.25rem;">
            Existing learners keep their access. No new enrollments will be accepted.
        </p>
        <form method="POST" action="{{ route('admin.programs.take-offline', $program) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Reason (optional)</label>
                <textarea name="review_notes" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Take Offline</button>
                <button type="button" onclick="closeModal('offline-modal')" class="btn btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); }));
</script>
@endpush