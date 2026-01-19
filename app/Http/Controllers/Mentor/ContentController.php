<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ModuleWeek;
use App\Models\Program;
use App\Models\ProgramModule;
use App\Models\WeekContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    /**
     * List all content created by this mentor
     */
    public function index(Request $request)
    {
        $mentor = auth()->user();

        $query = WeekContent::with(['moduleWeek.programModule.program'])
            ->where('created_by', $mentor->id);

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

        return view('mentor.contents.index', compact('contents'));
    }

    /**
     * Create new content
     */
    public function create(Request $request)
    {
        // Get programs that this mentor is assigned to (through cohorts/sessions)
        $programIds = auth()->user()->mentorSessions()
            ->distinct()
            ->pluck('program_id');

        $programs = Program::whereIn('id', $programIds)->get();

        $modules = $request->program_id 
            ? ProgramModule::where('program_id', $request->program_id)->orderBy('order')->get()
            : collect();
        
        $weeks = $request->module_id
            ? ModuleWeek::where('program_module_id', $request->module_id)->orderBy('week_number')->get()
            : collect();

        $programId = $request->program_id;
        $moduleId = $request->module_id;
        $weekId = $request->week_id;

        return view('mentor.contents.create', compact('programs', 'modules', 'weeks', 'programId', 'moduleId', 'weekId'));
    }

    /**
     * Store new content
     */
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
                'description' => 'Mentor created content: ' . $content->title,
                'model_type' => WeekContent::class,
                'model_id' => $content->id,
            ]);

            return redirect()->route('mentor.contents.index')
                ->with(['message' => 'Content created successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to create content: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Edit content (only if created by this mentor)
     */
    public function edit(WeekContent $content)
    {
        // Ensure mentor created this content
        if ($content->created_by !== auth()->id()) {
            abort(403, 'You can only edit content you created.');
        }

        $program = $content->moduleWeek->programModule->program;
        $modules = ProgramModule::where('program_id', $program->id)->orderBy('order')->get();
        $weeks = ModuleWeek::where('program_module_id', $content->moduleWeek->program_module_id)
            ->orderBy('week_number')->get();

        return view('mentor.contents.edit', compact('content', 'modules', 'weeks'));
    }

    /**
     * Update content
     */
    public function update(Request $request, WeekContent $content)
    {
        // Ensure mentor created this content
        if ($content->created_by !== auth()->id()) {
            abort(403, 'You can only edit content you created.');
        }

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
                'description' => 'Mentor updated content: ' . $content->title,
                'model_type' => WeekContent::class,
                'model_id' => $content->id,
            ]);

            return redirect()->route('mentor.contents.index')
                ->with(['message' => 'Content updated successfully!', 'alert-type' => 'success']);

        } catch (\Exception $e) {
            return back()->withInput()
                ->with(['message' => 'Failed to update content: ' . $e->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Delete content
     */
    public function destroy(WeekContent $content)
    {
        // Ensure mentor created this content
        if ($content->created_by !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete content you created.'
            ], 403);
        }

        try {
            // Delete file if exists
            if ($content->file_path) {
                Storage::disk('public')->delete($content->file_path);
            }

            AuditLog::log('content_deleted', auth()->user(), [
                'description' => 'Mentor deleted content: ' . $content->title,
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
                'message' => 'Failed to delete content.'
            ], 500);
        }
    }
}