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
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = WeekContent::with(['moduleWeek.programModule.program', 'creator']);

        // Filter by program
        if ($request->filled('program_id')) {
            $query->whereHas('moduleWeek.programModule', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        // Filter by module
        if ($request->filled('module_id')) {
            $query->whereHas('moduleWeek', function($q) use ($request) {
                $q->where('program_module_id', $request->module_id);
            });
        }

        // Filter by week
        if ($request->filled('week_id')) {
            $query->where('module_week_id', $request->week_id);
        }

        // Filter by content type
        if ($request->filled('content_type')) {
            $query->where('content_type', $request->content_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $contents = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        // Get filter options
        $programs = Program::active()->orderBy('name')->get();
        
        // Get modules for selected program
        $modules = collect();
        if ($request->filled('program_id')) {
            $modules = ProgramModule::where('program_id', $request->program_id)
                ->orderBy('order')
                ->get(['id', 'title']);
        }
        
        // Get weeks for selected module
        $weeks = collect();
        if ($request->filled('module_id')) {
            $weeks = ModuleWeek::where('program_module_id', $request->module_id)
                ->orderBy('week_number')
                ->get(['id', 'title', 'week_number']);
        }

        return view('admin.contents.index', compact('contents', 'programs', 'modules', 'weeks'));
    }

    /**
     * Show create form with week pre-selected
     */
    public function create(Request $request)
    {
        // Week ID is required - passed from week show page
        $request->validate([
            'week_id' => 'required|exists:module_weeks,id'
        ]);
        
        $week = ModuleWeek::with(['programModule.program'])->findOrFail($request->week_id);

        return view('admin.contents.create', compact('week'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'module_week_id' => 'required|exists:module_weeks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content_type' => 'required|in:video,pdf,link,text',
            'is_required' => 'boolean',
            'status' => 'required|in:draft,published',
            
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

            // Redirect to week show page to see the new content
            return redirect()->route('admin.weeks.show', $content->module_week_id)
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
            'status' => 'required|in:draft,published',
            
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

            return redirect()->route('admin.weeks.show', $content->module_week_id)
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
     * AJAX endpoint for filter dependencies
     */
    public function getModulesByProgram(Request $request)
    {
        $modules = ProgramModule::where('program_id', $request->program_id)
            ->orderBy('order')
            ->get(['id', 'title']);

        return response()->json($modules);
    }

    /**
     * AJAX endpoint for filter dependencies
     */
    public function getWeeksByModule(Request $request)
    {
        $weeks = ModuleWeek::where('program_module_id', $request->module_id)
            ->orderBy('week_number')
            ->get(['id', 'title', 'week_number']);

        return response()->json($weeks);
    }

    /**
     * Image upload handler for text editor
     */
    public function uploadImage(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'error' => 'No file uploaded'
                ], 400);
            }

            $file = $request->file('file');
            
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file upload'
                ], 400);
            }
            
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('content/images', $filename, 'public');
            
            if (!Storage::disk('public')->exists($path)) {
                throw new \Exception('File was not saved to storage');
            }
            
            $url = Storage::url($path);
            
            \Log::info('Image uploaded for content editor', [
                'user_id' => auth()->id(),
                'filename' => $filename,
                'size' => $file->getSize(),
                'path' => $path,
                'mime_type' => $file->getMimeType()
            ]);

            return response()->json([
                'success' => true,
                'location' => $url,
                'path' => $path,
                'filename' => $filename
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid file. Please upload a valid image (jpeg, png, jpg, gif, webp) under 2MB.',
                'details' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('Image upload failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload image. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}