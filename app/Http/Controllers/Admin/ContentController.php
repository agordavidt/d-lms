<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ModuleWeek;
use App\Models\Program;
use App\Models\ProgramModule;
use App\Models\WeekContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = WeekContent::with(['moduleWeek.programModule.program', 'creator']);

        // Filter by program
        if ($request->program_id) {
            $query->whereHas('moduleWeek.programModule', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        // Filter by module
        if ($request->module_id) {
            $query->whereHas('moduleWeek', function($q) use ($request) {
                $q->where('program_module_id', $request->module_id);
            });
        }

        // Filter by week
        if ($request->week_id) {
            $query->where('module_week_id', $request->week_id);
        }

        // Filter by content type
        if ($request->content_type) {
            $query->where('content_type', $request->content_type);
        }

        // Search
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $contents = $query->orderBy('created_at', 'desc')->paginate(20);
        $programs = Program::active()->get();

        return view('admin.contents.index', compact('contents', 'programs'));
    }

    public function create(Request $request)
    {
        $programs = Program::active()->get();
        $modules = $request->program_id 
            ? ProgramModule::where('program_id', $request->program_id)->orderBy('order')->get()
            : collect();
        $weeks = $request->module_id
            ? ModuleWeek::where('program_module_id', $request->module_id)->orderBy('week_number')->get()
            : collect();

        $programId = $request->program_id;
        $moduleId = $request->module_id;
        $weekId = $request->week_id;

        return view('admin.contents.create', compact('programs', 'modules', 'weeks', 'programId', 'moduleId', 'weekId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'module_week_id' => 'required|exists:module_weeks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:video,pdf,link,text',
            'is_required' => 'boolean',
            'status' => 'required|in:draft,published,archived',
            
            // Type-specific validations
            'video_url' => 'required_if:content_type,video|nullable|url',
            'video_duration_minutes' => 'nullable|integer|min:1',
            'file' => 'required_if:content_type,pdf|nullable|file|mimes:pdf|max:10240',
            'external_url' => 'required_if:content_type,link|nullable|url',
            'text_content' => 'required_if:content_type,text|nullable|string',
        ]);

        try {
            // Get next order number
            $nextOrder = WeekContent::where('module_week_id', $request->module_week_id)
                ->max('order') + 1;

            $data = [
                'module_week_id' => $request->module_week_id,
                'created_by' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'content_type' => $request->content_type,
                'order' => $nextOrder,
                'is_required' => $request->is_required ?? true,
                'status' => $request->status,
            ];

            // Handle content based on type
            switch ($request->content_type) {
                case 'video':
                    $data['video_url'] = $request->video_url;
                    $data['video_duration_minutes'] = $request->video_duration_minutes;
                    break;

                case 'pdf':
                    if ($request->hasFile('file')) {
                        $file = $request->file('file');
                        $path = $file->store('content/pdfs', 'public');
                        $data['file_path'] = $path;
                        $data['is_downloadable'] = true;
                        $data['metadata'] = [
                            'original_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                        ];
                    }
                    break;

                case 'link':
                    $data['external_url'] = $request->external_url;
                    break;

                case 'text':
                    $data['text_content'] = $request->text_content;
                    break;
            }

            $content = WeekContent::create($data);

            AuditLog::log('content_created', auth()->user(), [
                'description' => 'Created content: ' . $content->title,
                'model_type' => WeekContent::class,
                'model_id' => $content->id,
            ]);

            return redirect()->route('admin.contents.index', ['week_id' => $content->module_week_id])
                ->with(['message' => 'Content created successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create content: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function edit(WeekContent $content)
    {
        $programs = Program::active()->get();
        $program = $content->moduleWeek->programModule->program;
        $modules = ProgramModule::where('program_id', $program->id)->orderBy('order')->get();
        $weeks = ModuleWeek::where('program_module_id', $content->moduleWeek->program_module_id)
            ->orderBy('week_number')->get();

        return view('admin.contents.edit', compact('content', 'programs', 'modules', 'weeks'));
    }

    public function update(Request $request, WeekContent $content)
    {
        $request->validate([
            'module_week_id' => 'required|exists:module_weeks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'status' => 'required|in:draft,published,archived',
            
            // Type-specific validations
            'video_url' => 'required_if:content_type,video|nullable|url',
            'video_duration_minutes' => 'nullable|integer|min:1',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'external_url' => 'required_if:content_type,link|nullable|url',
            'text_content' => 'required_if:content_type,text|nullable|string',
        ]);

        try {
            $data = [
                'module_week_id' => $request->module_week_id,
                'title' => $request->title,
                'description' => $request->description,
                'is_required' => $request->is_required ?? true,
                'status' => $request->status,
            ];

            // Handle content based on type
            switch ($content->content_type) {
                case 'video':
                    $data['video_url'] = $request->video_url;
                    $data['video_duration_minutes'] = $request->video_duration_minutes;
                    break;

                case 'pdf':
                    if ($request->hasFile('file')) {
                        // Delete old file
                        if ($content->file_path) {
                            Storage::disk('public')->delete($content->file_path);
                        }
                        
                        $file = $request->file('file');
                        $path = $file->store('content/pdfs', 'public');
                        $data['file_path'] = $path;
                        $data['metadata'] = [
                            'original_name' => $file->getClientOriginalName(),
                            'file_size' => $file->getSize(),
                        ];
                    }
                    break;

                case 'link':
                    $data['external_url'] = $request->external_url;
                    break;

                case 'text':
                    $data['text_content'] = $request->text_content;
                    break;
            }

            $content->update($data);

            AuditLog::log('content_updated', auth()->user(), [
                'description' => 'Updated content: ' . $content->title,
                'model_type' => WeekContent::class,
                'model_id' => $content->id,
            ]);

            return redirect()->route('admin.contents.index', ['week_id' => $content->module_week_id])
                ->with(['message' => 'Content updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update content: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function destroy(WeekContent $content)
    {
        try {
            // Delete file if exists
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }

            AuditLog::log('content_deleted', auth()->user(), [
                'description' => 'Deleted content: ' . $content->title,
                'model_type' => WeekContent::class,
                'model_id' => $content->id,
            ]);

            $content->delete();

            return response()->json([
                'success' => true,
                'message' => 'Content deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete content: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'contents' => 'required|array',
            'contents.*.id' => 'required|exists:week_contents,id',
            'contents.*.order' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->contents as $contentData) {
                WeekContent::where('id', $contentData['id'])
                    ->update(['order' => $contentData['order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contents reordered successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder contents.'
            ], 500);
        }
    }

    /**
     * Get modules by program (for AJAX)
     */
    public function getModulesByProgram(Request $request)
    {
        $modules = ProgramModule::where('program_id', $request->program_id)
            ->orderBy('order')
            ->get(['id', 'title']);

        return response()->json($modules);
    }

    /**
     * Get weeks by module (for AJAX)
     */
    public function getWeeksByModule(Request $request)
    {
        $weeks = ModuleWeek::where('program_module_id', $request->module_id)
            ->orderBy('week_number')
            ->get(['id', 'title', 'week_number']);

        return response()->json($weeks);
    }
}