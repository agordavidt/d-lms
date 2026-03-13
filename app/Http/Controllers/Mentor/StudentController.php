<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Program;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $programIds = Program::where('mentor_id', auth()->id())->pluck('id');

        $enrollments = Enrollment::whereIn('program_id', $programIds)
            ->where('status', 'active')
            ->with(['user', 'program'])
            ->when($request->program_id, fn ($q) => $q->where('program_id', $request->program_id))
            ->when($request->search, fn ($q) => $q->whereHas('user', fn ($u) =>
                $u->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name',  'like', '%' . $request->search . '%')
                  ->orWhere('email',      'like', '%' . $request->search . '%')
            ))
            ->orderByDesc('created_at')
            ->paginate(20);

        $programs = Program::where('mentor_id', auth()->id())
            ->whereIn('status', ['active', 'inactive'])
            ->orderBy('name')
            ->get();

        return view('mentor.students.index', compact('enrollments', 'programs'));
    }

    public function show(Enrollment $enrollment)
    {
        $programIds = Program::where('mentor_id', auth()->id())->pluck('id');
        abort_if(!$programIds->contains($enrollment->program_id), 403);

        $enrollment->load([
            'user', 'program.modules.weeks',
            'weekProgress.moduleWeek',
            'assessmentAttempts.assessment',
        ]);

        $weekProgress = $enrollment->weekProgress->keyBy('module_week_id');

        return view('mentor.students.show', compact('enrollment', 'weekProgress'));
    }
}