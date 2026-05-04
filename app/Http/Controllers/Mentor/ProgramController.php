<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    private function mentorPrograms()
    {
        return Program::where('mentor_id', auth()->id());
    }

    /** List all programs belonging to this mentor */
    public function index()
    {
        $programs = $this->mentorPrograms()
            ->withCount(['enrollments', 'modules'])
            ->latest()
            ->paginate(12);

        return view('mentor.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('mentor.programs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'description'         => 'required|string|max:1000',
            'duration'            => 'required|string|max:50',
            'price'               => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'min_passing_average' => 'nullable|numeric|min:0|max:100',
            'cover_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')
                ->store('program-covers', 'public');
        }

        $data['mentor_id'] = auth()->id();
        $data['status']    = 'draft';
        $data['slug']      = Str::slug($data['name']);

        // Ensure unique slug
        $baseSlug = $data['slug'];
        $i = 1;
        while (Program::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $baseSlug . '-' . $i++;
        }

        $program = Program::create($data);

        return redirect()
            ->route('mentor.programs.show', $program)
            ->with(['message' => 'Program created. Start building your curriculum.', 'alert-type' => 'success']);
    }

    /** Program detail / curriculum builder */
    public function show(Program $program)
    {
        $this->authorise($program);

        // ── CHANGE 1 ──────────────────────────────────────────────────────────
        // Load assessment.questions so the week rows can display question counts
        // without extra queries.
        $program->load(['modules.weeks.contents', 'modules.weeks.assessment.questions']);

        $stats = [
            'modules'     => $program->modules->count(),
            'weeks'       => $program->modules->sum(fn ($m) => $m->weeks->count()),
            'contents'    => $program->modules->sum(fn ($m) => $m->weeks->sum(fn ($w) => $w->contents->count())),
            'assessments' => $program->modules->sum(fn ($m) => $m->weeks->filter(fn ($w) => $w->assessment)->count()),
            'enrolled'    => $program->enrollments()->count(),
        ];

        return view('mentor.programs.show', compact('program', 'stats'));
    }

    public function edit(Program $program)
    {
        $this->authorise($program);

        // Prevent editing once under review or active — require admin to take offline first
        if (in_array($program->status, ['under_review', 'active'])) {
            return back()->with([
                'message' => 'This program is ' . $program->status . '. Contact admin to make it editable.',
                'alert-type' => 'warning',
            ]);
        }

        return view('mentor.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $this->authorise($program);

        abort_if(in_array($program->status, ['under_review', 'active']), 403,
            'Program cannot be edited while under review or active.');

        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'description'         => 'required|string|max:1000',
            'duration'            => 'required|string|max:50',
            'price'               => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'min_passing_average' => 'nullable|numeric|min:0|max:100',
            'cover_image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($program->cover_image) {
                Storage::disk('public')->delete($program->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')
                ->store('program-covers', 'public');
        }

        $program->update($data);

        // ── CHANGE 2 ──────────────────────────────────────────────────────────
        // Redirect to the program's show page instead of back() to the edit form,
        // so the mentor can see the updated details in context and continue work.
        return redirect()
            ->route('mentor.programs.show', $program)
            ->with(['message' => 'Program updated.', 'alert-type' => 'success']);
    }

    /** Submit program to admin for review */
    public function submitForReview(Program $program)
    {
        $this->authorise($program);

        if ($program->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft programs can be submitted for review.',
            ], 422);
        }

        // Basic readiness check — load if not already eager-loaded
        $program->loadMissing('modules.weeks.contents');

        $weekCount    = $program->modules->sum(fn ($m) => $m->weeks->count());
        $contentCount = $program->modules->sum(fn ($m) => $m->weeks->sum(fn ($w) => $w->contents->count()));

        if ($weekCount === 0 || $contentCount === 0) {
            return response()->json([
                'message' => 'Add at least one week with content before submitting for review.',
            ], 422);
        }

        $program->update([
            'status'       => 'under_review',
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Program $program)
    {
        $this->authorise($program);

        abort_if($program->enrollments()->exists(), 403,
            'Cannot delete a program that has enrolled learners.');

        if ($program->cover_image) {
            Storage::disk('public')->delete($program->cover_image);
        }

        $program->delete();

        return redirect()
            ->route('mentor.programs.index')
            ->with(['message' => 'Program deleted.', 'alert-type' => 'success']);
    }

    private function authorise(Program $program): void
    {
        abort_if($program->mentor_id !== auth()->id(), 403);
    }
}