<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MentorManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'mentor');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name',  'like', "%{$s}%")
                ->orWhere('email',      'like', "%{$s}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $mentors = $query->withCount([
            'programs',
            'programs as active_programs_count' => fn ($q) => $q->where('status', 'active'),
        ])
        ->orderByDesc('created_at')
        ->paginate(20)
        ->withQueryString();

        return view('admin.mentors.index', compact('mentors'));
    }

    /**
     * Tabbed mentor detail: Overview / Programs / Learners
     */
    public function show($id)
    {
        $mentor = User::where('role', 'mentor')->findOrFail($id);

        // Programs
        $programs = Program::where('mentor_id', $mentor->id)
            ->withCount('enrollments')
            ->orderByDesc('created_at')
            ->get();

        // Learners across all mentor programs
        $programIds = $programs->pluck('id');

        $enrollments = Enrollment::whereIn('program_id', $programIds)
            ->where('status', 'active')
            ->with(['user', 'program'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'programs'      => $programs->count(),
            'active'        => $programs->where('status', 'active')->count(),
            'under_review'  => $programs->where('status', 'under_review')->count(),
            'drafts'        => $programs->where('status', 'draft')->count(),
            'learners'      => $enrollments->count(),
        ];

        return view('admin.mentors.show', compact('mentor', 'programs', 'enrollments', 'stats'));
    }

    public function create()
    {
        return view('admin.mentors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8|confirmed',
        ]);

        $data['role']     = 'mentor';
        $data['status']   = 'active';
        $data['password'] = Hash::make($data['password']);

        $mentor = User::create($data);

        AuditLog::log('mentor_created', auth()->user(), [
            'description' => 'Created mentor account: ' . $mentor->email,
            'model_type'  => User::class,
            'model_id'    => $mentor->id,
        ]);

        return redirect()->route('admin.mentors.show', $mentor->id)
            ->with(['message' => 'Mentor account created.', 'alert-type' => 'success']);
    }

    public function edit($id)
    {
        $mentor = User::where('role', 'mentor')->findOrFail($id);
        return view('admin.mentors.edit', compact('mentor'));
    }

    public function update(Request $request, $id)
    {
        $mentor = User::where('role', 'mentor')->findOrFail($id);

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email,' . $mentor->id,
            'phone'      => 'nullable|string|max:20',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $data['password'] = Hash::make($request->password);
        }

        $mentor->update($data);

        return redirect()->route('admin.mentors.show', $mentor->id)
            ->with(['message' => 'Mentor updated.', 'alert-type' => 'success']);
    }

    public function destroy($id)
    {
        $mentor = User::where('role', 'mentor')->findOrFail($id);

        if (Program::where('mentor_id', $mentor->id)->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a mentor with active programs.',
            ], 400);
        }

        AuditLog::log('mentor_deleted', auth()->user(), [
            'description' => 'Deleted mentor: ' . $mentor->email,
            'model_type'  => User::class,
            'model_id'    => $mentor->id,
        ]);

        $mentor->delete();

        return response()->json(['success' => true, 'message' => 'Mentor removed.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended']);

        User::where('role', 'mentor')->findOrFail($id)->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}