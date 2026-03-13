<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::with(['mentor'])
            ->withCount(['enrollments', 'modules']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $programs = $query->latest()->paginate(20);

        $counts = [
            'under_review' => Program::where('status', 'under_review')->count(),
            'active'       => Program::where('status', 'active')->count(),
            'draft'        => Program::where('status', 'draft')->count(),
            'inactive'     => Program::where('status', 'inactive')->count(),
        ];

        return view('admin.programs.index', compact('programs', 'counts'));
    }

    /** Full program preview for admin review */
    public function show(Program $program)
    {
        $program->load([
            'mentor',
            'modules.weeks.contents',
            'modules.weeks.assessment.questions',
        ]);

        $stats = [
            'weeks'       => $program->modules->sum(fn ($m) => $m->weeks->count()),
            'contents'    => $program->modules->sum(fn ($m) => $m->weeks->sum(fn ($w) => $w->contents->count())),
            'assessments' => $program->modules->sum(fn ($m) => $m->weeks->filter(fn ($w) => $w->assessment)->count()),
            'questions'   => $program->modules->sum(fn ($m) => $m->weeks->sum(fn ($w) =>
                                $w->assessment ? $w->assessment->questions->count() : 0)),
        ];

        return view('admin.programs.show', compact('program', 'stats'));
    }

    /** Publish (approve) a program — makes it visible to learners */
    public function publish(Request $request, Program $program)
    {
        $request->validate(['review_notes' => 'nullable|string|max:500']);

        $program->update([
            'status'       => 'active',
            'reviewed_at'  => now(),
            'reviewed_by'  => auth()->id(),
            'review_notes' => $request->review_notes,
        ]);

        // TODO: notify mentor
        return back()->with(['message' => "'{$program->name}' is now live for learners.", 'alert-type' => 'success']);
    }

    /** Reject — send back to draft with notes */
    public function reject(Request $request, Program $program)
    {
        $request->validate(['review_notes' => 'required|string|max:500']);

        $program->update([
            'status'       => 'draft',
            'reviewed_at'  => now(),
            'reviewed_by'  => auth()->id(),
            'review_notes' => $request->review_notes,
        ]);

        // TODO: notify mentor with review_notes
        return back()->with(['message' => 'Program returned to mentor with feedback.', 'alert-type' => 'success']);
    }

    /** Take a live program offline — existing learners keep access, no new enrollments */
    public function takeOffline(Request $request, Program $program)
    {
        $request->validate(['review_notes' => 'nullable|string|max:500']);

        $program->update([
            'status'       => 'inactive',
            'reviewed_at'  => now(),
            'reviewed_by'  => auth()->id(),
            'review_notes' => $request->review_notes,
        ]);

        return back()->with(['message' => "'{$program->name}' taken offline. Existing learners unaffected.", 'alert-type' => 'success']);
    }

    /** Restore an inactive program back to active */
    public function restore(Program $program)
    {
        $program->update([
            'status'      => 'active',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with(['message' => "'{$program->name}' restored and live again.", 'alert-type' => 'success']);
    }

    public function destroy(Program $program)
    {
        abort_if($program->enrollments()->exists(), 403,
            'Cannot delete a program with enrolled learners.');

        $program->delete();

        return redirect()->route('admin.programs.index')
            ->with(['message' => 'Program deleted.', 'alert-type' => 'success']);
    }
}