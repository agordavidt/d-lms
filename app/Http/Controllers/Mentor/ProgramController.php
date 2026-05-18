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

    public function index(Request $request)
    {
        $query = $this->mentorPrograms()
            ->withCount(['enrollments', 'modules'])
            ->latest();

        // ── Status filter — drives the tab navigation ──
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $programs = $query->paginate(12);

        return view('mentor.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('mentor.programs.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        
        if (array_key_exists('discount_percentage', $validatedData) && is_null($validatedData['discount_percentage'])) {
            unset($validatedData['discount_percentage']);
        }

        // Handle file uploads safely
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('program-covers', 'public');
            $validatedData['cover_image'] = $path;
        }

        // Build remaining required program fields
        $validatedData['mentor_id'] = auth()->id();
        $validatedData['slug'] = Str::slug($validatedData['name']);
        $validatedData['status'] = 'draft';

        $program = Program::create($validatedData);

        return redirect()->route('mentor.programs.show', $program->id)
            ->with('success', 'Program draft created successfully.');
    }

    public function show(Program $program)
    {
        $this->authorise($program);

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

        if (in_array($program->status, ['under_review', 'active'])) {
            return back()->with([
                'message'    => 'This program is ' . $program->status . '. Contact admin to make it editable.',
                'alert-type' => 'warning',
            ]);
        }

        return view('mentor.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        
        if (array_key_exists('discount_percentage', $validatedData) && is_null($validatedData['discount_percentage'])) {
            $validatedData['discount_percentage'] = 0;
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('program-covers', 'public');
            $validatedData['cover_image'] = $path;
        }

        $validatedData['slug'] = Str::slug($validatedData['name']);

        $program->update($validatedData);

        return redirect()->route('mentor.programs.show', $program->id)
            ->with('success', 'Program updated successfully.');
    }

    public function submitForReview(Program $program)
    {
        $this->authorise($program);

        if ($program->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft programs can be submitted for review.',
            ], 422);
        }

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



