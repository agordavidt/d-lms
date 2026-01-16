<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Cohort;
use App\Models\Program;
use Illuminate\Http\Request;

class CohortController extends Controller
{
    public function index()
    {
        $cohorts = Cohort::with('program')
            ->latest('start_date')
            ->paginate(15);

        return view('admin.cohorts.index', compact('cohorts'));
    }

    public function create()
    {
        $programs = Program::active()->get();
        return view('admin.cohorts.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:cohorts',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
            'max_students' => 'required|integer|min:1',
            'whatsapp_link' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        try {
            $cohort = Cohort::create($request->all());

            AuditLog::log('cohort_created', auth()->user(), [
                'description' => 'Admin created new cohort',
                'model_type' => Cohort::class,
                'model_id' => $cohort->id,
            ]);

            return redirect()->route('admin.cohorts.index')
                ->with(['message' => 'Cohort created successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create cohort: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function edit(Cohort $cohort)
    {
        $programs = Program::active()->get();
        return view('admin.cohorts.edit', compact('cohort', 'programs'));
    }

    public function update(Request $request, Cohort $cohort)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:cohorts,code,' . $cohort->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,ongoing,completed,cancelled',
            'max_students' => 'required|integer|min:1',
            'whatsapp_link' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        try {
            $cohort->update($request->all());

            AuditLog::log('cohort_updated', auth()->user(), [
                'description' => 'Admin updated cohort',
                'model_type' => Cohort::class,
                'model_id' => $cohort->id,
            ]);

            return redirect()->route('admin.cohorts.index')
                ->with(['message' => 'Cohort updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update cohort: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(Cohort $cohort)
    {
        try {
            if ($cohort->enrollments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete cohort with existing enrollments.'
                ], 400);
            }

            AuditLog::log('cohort_deleted', auth()->user(), [
                'description' => 'Admin deleted cohort',
                'model_type' => Cohort::class,
                'model_id' => $cohort->id,
            ]);

            $cohort->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cohort deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cohort.'
            ], 500);
        }
    }
}