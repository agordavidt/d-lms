<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::withCount('cohorts', 'enrollments')
            ->latest()
            ->paginate(10);

        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:programs',
            'description' => 'required|string',
            'overview' => 'nullable|string',
            'duration' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,draft',
            'max_students' => 'nullable|integer|min:1',
        ]);

        try {
            $program = Program::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'overview' => $request->overview,
                'duration' => $request->duration,
                'price' => $request->price,
                'discount_percentage' => $request->discount_percentage ?? 10,
                'status' => $request->status,
                'max_students' => $request->max_students,
                'features' => $request->features ? array_filter($request->features) : null,
                'requirements' => $request->requirements ? array_filter($request->requirements) : null,
            ]);

            AuditLog::log('program_created', auth()->user(), [
                'description' => 'Admin created new program',
                'model_type' => Program::class,
                'model_id' => $program->id,
                'new_values' => $program->only(['name', 'price', 'status'])
            ]);

            return redirect()->route('admin.programs.index')
                ->with(['message' => 'Program created successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create program: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function edit(Program $program)
    {
        return view('admin.programs.edit', compact('program'));
    }

    public function update(Request $request, Program $program)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:programs,name,' . $program->id,
            'description' => 'required|string',
            'overview' => 'nullable|string',
            'duration' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,draft',
            'max_students' => 'nullable|integer|min:1',
        ]);

        try {
            $oldValues = $program->only(['name', 'price', 'status']);

            $program->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'overview' => $request->overview,
                'duration' => $request->duration,
                'price' => $request->price,
                'discount_percentage' => $request->discount_percentage ?? 10,
                'status' => $request->status,
                'max_students' => $request->max_students,
                'features' => $request->features ? array_filter($request->features) : null,
                'requirements' => $request->requirements ? array_filter($request->requirements) : null,
            ]);

            AuditLog::log('program_updated', auth()->user(), [
                'description' => 'Admin updated program',
                'model_type' => Program::class,
                'model_id' => $program->id,
                'old_values' => $oldValues,
                'new_values' => $program->only(['name', 'price', 'status'])
            ]);

            return redirect()->route('admin.programs.index')
                ->with(['message' => 'Program updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update program: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(Program $program)
    {
        try {
            // Check if program has active enrollments
            if ($program->enrollments()->whereIn('status', ['active', 'pending'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete program with active enrollments.'
                ], 400);
            }

            AuditLog::log('program_deleted', auth()->user(), [
                'description' => 'Admin deleted program',
                'model_type' => Program::class,
                'model_id' => $program->id,
                'old_values' => $program->only(['name', 'price', 'status'])
            ]);

            $program->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete program.'
            ], 500);
        }
    }
}