<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ModuleWeek;
use App\Models\Program;
use App\Models\ProgramModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeekController extends Controller
{
    public function index(Request $request)
    {
        $query = ModuleWeek::with(['programModule.program']);

        // Filter by program
        if ($request->program_id) {
            $query->whereHas('programModule', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        // Filter by module
        if ($request->module_id) {
            $query->where('program_module_id', $request->module_id);
        }

        // Search
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $weeks = $query->orderBy('week_number')->paginate(20);
        $programs = Program::active()->get();
        $modules = $request->program_id 
            ? ProgramModule::where('program_id', $request->program_id)->get()
            : collect();

        return view('admin.weeks.index', compact('weeks', 'programs', 'modules'));
    }

    public function create(Request $request)
    {
        $programs = Program::active()->get();
        $modules = $request->program_id 
            ? ProgramModule::where('program_id', $request->program_id)->orderBy('order')->get()
            : collect();
        
        $programId = $request->program_id;
        $moduleId = $request->module_id;

        return view('admin.weeks.create', compact('programs', 'modules', 'programId', 'moduleId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_module_id' => 'required|exists:program_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'week_number' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
            'has_assessment' => 'boolean',
            'assessment_pass_percentage' => 'nullable|integer|min:0|max:100',
            'learning_outcomes' => 'nullable|array',
        ]);

        try {
            // Get next order number within module
            $nextOrder = ModuleWeek::where('program_module_id', $request->program_module_id)
                ->max('order') + 1;

            $week = ModuleWeek::create([
                'program_module_id' => $request->program_module_id,
                'title' => $request->title,
                'description' => $request->description,
                'week_number' => $request->week_number,
                'order' => $nextOrder,
                'status' => $request->status,
                'has_assessment' => $request->has_assessment ?? false,
                'assessment_pass_percentage' => $request->assessment_pass_percentage ?? 70,
                'learning_outcomes' => $request->learning_outcomes 
                    ? array_filter($request->learning_outcomes) 
                    : null,
            ]);

            AuditLog::log('week_created', auth()->user(), [
                'description' => 'Created week: ' . $week->title,
                'model_type' => ModuleWeek::class,
                'model_id' => $week->id,
            ]);

            return redirect()->route('admin.weeks.index', ['module_id' => $week->program_module_id])
                ->with(['message' => 'Week created successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create week: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function show(ModuleWeek $week)
    {
        $week->load(['programModule.program', 'contents' => function($query) {
            $query->orderBy('order');
        }]);

        return view('admin.weeks.show', compact('week'));
    }

    public function edit(ModuleWeek $week)
    {
        $programs = Program::active()->get();
        $modules = ProgramModule::where('program_id', $week->programModule->program_id)
            ->orderBy('order')
            ->get();

        return view('admin.weeks.edit', compact('week', 'programs', 'modules'));
    }

    public function update(Request $request, ModuleWeek $week)
    {
        $request->validate([
            'program_module_id' => 'required|exists:program_modules,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'week_number' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
            'has_assessment' => 'boolean',
            'assessment_pass_percentage' => 'nullable|integer|min:0|max:100',
            'learning_outcomes' => 'nullable|array',
        ]);

        try {
            $week->update([
                'program_module_id' => $request->program_module_id,
                'title' => $request->title,
                'description' => $request->description,
                'week_number' => $request->week_number,
                'status' => $request->status,
                'has_assessment' => $request->has_assessment ?? false,
                'assessment_pass_percentage' => $request->assessment_pass_percentage ?? 70,
                'learning_outcomes' => $request->learning_outcomes 
                    ? array_filter($request->learning_outcomes) 
                    : null,
            ]);

            AuditLog::log('week_updated', auth()->user(), [
                'description' => 'Updated week: ' . $week->title,
                'model_type' => ModuleWeek::class,
                'model_id' => $week->id,
            ]);

            return redirect()->route('admin.weeks.index', ['module_id' => $week->program_module_id])
                ->with(['message' => 'Week updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update week: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(ModuleWeek $week)
    {
        try {
            // Check if week has contents
            if ($week->contents()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete week with existing content. Delete content first.'
                ], 400);
            }

            AuditLog::log('week_deleted', auth()->user(), [
                'description' => 'Deleted week: ' . $week->title,
                'model_type' => ModuleWeek::class,
                'model_id' => $week->id,
            ]);

            $week->delete();

            return response()->json([
                'success' => true,
                'message' => 'Week deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete week.'
            ], 500);
        }
    }

    public function getModulesByProgram(Request $request)
    {
        $modules = ProgramModule::where('program_id', $request->program_id)
            ->orderBy('order')
            ->get(['id', 'title']);

        return response()->json($modules);
    }
}