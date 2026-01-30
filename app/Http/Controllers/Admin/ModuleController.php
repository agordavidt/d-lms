<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Program;
use App\Models\ProgramModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgramModule::with('program');

        // Filter by program
        if ($request->program_id) {
            $query->where('program_id', $request->program_id);
        }

        // Search
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $modules = $query->orderBy('order')->paginate(20);
        $programs = Program::active()->get();

        return view('admin.modules.index', compact('modules', 'programs'));
    }

    public function create(Request $request)
    {
        $programs = Program::active()->get();
        $programId = $request->program_id;

        return view('admin.modules.create', compact('programs', 'programId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_weeks' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
            'learning_objectives' => 'nullable|array',
        ]);

        try {
            // Get next order number
            $nextOrder = ProgramModule::where('program_id', $request->program_id)
                ->max('order') + 1;

            $module = ProgramModule::create([
                'program_id' => $request->program_id,
                'title' => $request->title,
                'description' => $request->description,
                'duration_weeks' => $request->duration_weeks,
                'order' => $nextOrder,
                'status' => $request->status,
                'learning_objectives' => $request->learning_objectives 
                    ? array_filter($request->learning_objectives) 
                    : null,
            ]);

            AuditLog::log('module_created', auth()->user(), [
                'description' => 'Created module: ' . $module->title,
                'model_type' => ProgramModule::class,
                'model_id' => $module->id,
            ]);

            return redirect()->route('admin.modules.index', ['program_id' => $module->program_id])
                ->with(['message' => 'Module created successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create module: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function edit(ProgramModule $module)
    {
        $programs = Program::active()->get();
        
        return view('admin.modules.edit_partial', compact('module', 'programs'));
    }
  

    public function update(Request $request, ProgramModule $module)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_weeks' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,archived',
            'learning_objectives' => 'nullable|array',
        ]);

        try {
            $module->update([
                'program_id' => $request->program_id,
                'title' => $request->title,
                'description' => $request->description,
                'duration_weeks' => $request->duration_weeks,
                'status' => $request->status,
                'learning_objectives' => $request->learning_objectives 
                    ? array_filter($request->learning_objectives) 
                    : null,
            ]);

            AuditLog::log('module_updated', auth()->user(), [
                'description' => 'Updated module: ' . $module->title,
                'model_type' => ProgramModule::class,
                'model_id' => $module->id,
            ]);

            return redirect()->route('admin.modules.index', ['program_id' => $module->program_id])
                ->with(['message' => 'Module updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update module: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(ProgramModule $module)
    {
        try {
            // Check if module has weeks
            if ($module->weeks()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete module with existing weeks. Delete weeks first.'
                ], 400);
            }

            AuditLog::log('module_deleted', auth()->user(), [
                'description' => 'Deleted module: ' . $module->title,
                'model_type' => ProgramModule::class,
                'model_id' => $module->id,
            ]);

            $module->delete();

            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module.'
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:program_modules,id',
            'modules.*.order' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->modules as $moduleData) {
                ProgramModule::where('id', $moduleData['id'])
                    ->update(['order' => $moduleData['order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Modules reordered successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder modules.'
            ], 500);
        }
    }
}