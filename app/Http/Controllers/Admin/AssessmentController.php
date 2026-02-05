<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AuditLog;
use App\Models\ModuleWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssessmentController extends Controller
{
    /**
     * Show form to create assessment for a week
     */
    public function create(Request $request)
    {
        $request->validate([
            'week_id' => 'required|exists:module_weeks,id'
        ]);

        $week = ModuleWeek::with(['programModule.program'])->findOrFail($request->week_id);

        // Check if assessment already exists
        if ($week->assessment) {
            return redirect()->route('admin.assessments.edit', $week->assessment->id)
                ->with(['message' => 'Assessment already exists for this week.', 'alert-type' => 'info']);
        }

        return view('admin.assessments.create', compact('week'));
    }

    /**
     * Store assessment and redirect to question management
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_week_id' => 'required|exists:module_weeks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'required|integer|min:1|max:10',
            'pass_percentage' => 'required|integer|min:1|max:100',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_correct_answers' => 'boolean',
        ]);

        try {
            $assessment = Assessment::create([
                'module_week_id' => $request->module_week_id,
                'title' => $request->title,
                'description' => $request->description,
                'time_limit_minutes' => $request->time_limit_minutes,
                'max_attempts' => $request->max_attempts,
                'pass_percentage' => $request->pass_percentage,
                'randomize_questions' => $request->randomize_questions ?? false,
                'randomize_options' => $request->randomize_options ?? false,
                'show_correct_answers' => $request->show_correct_answers ?? true,
                'is_active' => false, // Start as inactive until questions added
                'created_by' => auth()->id(),
            ]);

            AuditLog::log('assessment_created', auth()->user(), [
                'description' => 'Created assessment: ' . $assessment->title,
                'model_type' => Assessment::class,
                'model_id' => $assessment->id,
            ]);

            return redirect()->route('admin.assessments.questions.index', $assessment->id)
                ->with(['message' => 'Assessment created! Now add questions.', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create assessment: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Show assessment with questions
     */
    public function show(Assessment $assessment)
    {
        $assessment->load(['moduleWeek.programModule.program', 'questions', 'attempts']);

        return view('admin.assessments.show', compact('assessment'));
    }

    /**
     * Edit assessment settings
     */
    public function edit(Assessment $assessment)
    {
        $assessment->load(['moduleWeek.programModule.program']);

        return view('admin.assessments.edit', compact('assessment'));
    }

    /**
     * Update assessment settings
     */
    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time_limit_minutes' => 'nullable|integer|min:1',
            'max_attempts' => 'required|integer|min:1|max:10',
            'pass_percentage' => 'required|integer|min:1|max:100',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'show_correct_answers' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            $assessment->update([
                'title' => $request->title,
                'description' => $request->description,
                'time_limit_minutes' => $request->time_limit_minutes,
                'max_attempts' => $request->max_attempts,
                'pass_percentage' => $request->pass_percentage,
                'randomize_questions' => $request->randomize_questions ?? false,
                'randomize_options' => $request->randomize_options ?? false,
                'show_correct_answers' => $request->show_correct_answers ?? true,
                'is_active' => $request->is_active ?? false,
            ]);

            AuditLog::log('assessment_updated', auth()->user(), [
                'description' => 'Updated assessment: ' . $assessment->title,
                'model_type' => Assessment::class,
                'model_id' => $assessment->id,
            ]);

            return redirect()->route('admin.assessments.show', $assessment->id)
                ->with(['message' => 'Assessment updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update assessment: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Delete assessment
     */
    public function destroy(Assessment $assessment)
    {
        try {
            // Check if there are attempts
            $attemptsCount = $assessment->attempts()->count();

            if ($attemptsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete assessment with {$attemptsCount} learner attempts. Deactivate it instead."
                ], 400);
            }

            AuditLog::log('assessment_deleted', auth()->user(), [
                'description' => 'Deleted assessment: ' . $assessment->title,
                'model_type' => Assessment::class,
                'model_id' => $assessment->id,
            ]);

            $assessment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assessment deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle assessment active status
     */
    public function toggleActive(Assessment $assessment)
    {
        try {
            // Check if assessment has questions
            if (!$assessment->is_active && $assessment->questions()->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate assessment without questions.'
                ], 400);
            }

            $assessment->update([
                'is_active' => !$assessment->is_active
            ]);

            $status = $assessment->is_active ? 'activated' : 'deactivated';

            AuditLog::log('assessment_toggled', auth()->user(), [
                'description' => "Assessment {$status}: {$assessment->title}",
                'model_type' => Assessment::class,
                'model_id' => $assessment->id,
            ]);

            return response()->json([
                'success' => true,
                'is_active' => $assessment->is_active,
                'message' => "Assessment {$status} successfully!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle assessment status.'
            ], 500);
        }
    }
}