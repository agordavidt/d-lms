<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramModule;
use App\Models\ModuleWeek;
use App\Models\WeekContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Handles all curriculum structure management for a mentor's program:
 * modules, weeks, and content items — all via JSON responses
 * for the inline curriculum builder UI.
 */
class CurriculumController extends Controller
{
    // ── Modules ───────────────────────────────────────────────────────────────

    public function storeModule(Request $request, Program $program)
    {
        $this->authorise($program);

        $data = $request->validate(['title' => 'required|string|max:150']);

        $order  = $program->modules()->max('order') + 1;
        $module = $program->modules()->create([
            'title'          => $data['title'],
            'order'          => $order,
            'duration_weeks' => 0,  // recalculated as weeks are added
        ]);

        return response()->json(['success' => true, 'module' => $module]);
    }

    public function updateModule(Request $request, Program $program, ProgramModule $module)
    {
        $this->authorise($program);
        $this->authoriseModule($program, $module);

        $data = $request->validate(['title' => 'required|string|max:150']);
        $module->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyModule(Program $program, ProgramModule $module)
    {
        $this->authorise($program);
        $this->authoriseModule($program, $module);

        $module->delete();

        // Reorder remaining modules
        $program->modules()->orderBy('order')->get()
            ->each(fn ($m, $i) => $m->update(['order' => $i + 1]));

        return response()->json(['success' => true]);
    }

    public function reorderModules(Request $request, Program $program)
    {
        $this->authorise($program);

        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $position => $moduleId) {
            $program->modules()->where('id', $moduleId)
                ->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    // ── Weeks ─────────────────────────────────────────────────────────────────

    public function storeWeek(Request $request, Program $program, ProgramModule $module)
    {
        $this->authorise($program);
        $this->authoriseModule($program, $module);

        $data = $request->validate([
            'title'                      => 'required|string|max:150',
            'has_assessment'             => 'boolean',
            'assessment_pass_percentage' => 'nullable|integer|min:1|max:100',
        ]);

        // Global week number = total weeks in program so far + 1
        $weekNumber = ModuleWeek::whereHas('programModule', fn ($q) =>
            $q->where('program_id', $program->id)
        )->count() + 1;

        $order = $module->weeks()->max('order') + 1;

        $week = $module->weeks()->create([
            'title'                      => $data['title'],
            'week_number'                => $weekNumber,
            'order'                      => $order,
            'has_assessment'             => $data['has_assessment'] ?? false,
            'assessment_pass_percentage' => $data['assessment_pass_percentage'] ?? 70,
        ]);

        // Update module duration_weeks
        $module->update(['duration_weeks' => $module->weeks()->count()]);

        return response()->json(['success' => true, 'week' => $week]);
    }

    public function updateWeek(Request $request, Program $program, ModuleWeek $week)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'                      => 'required|string|max:150',
            'has_assessment'             => 'boolean',
            'assessment_pass_percentage' => 'nullable|integer|min:1|max:100',
        ]);

        $week->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyWeek(Program $program, ModuleWeek $week)
    {
        $this->authorise($program);

        $module = $week->programModule;
        $week->delete();

        // Recalculate module duration
        $module->update(['duration_weeks' => $module->weeks()->count()]);

        // Renumber all weeks in the program sequentially
        ModuleWeek::whereHas('programModule', fn ($q) =>
            $q->where('program_id', $program->id)
        )
        ->join('program_modules', 'module_weeks.program_module_id', '=', 'program_modules.id')
        ->orderBy('program_modules.order')
        ->orderBy('module_weeks.order')
        ->select('module_weeks.*')
        ->get()
        ->each(fn ($w, $i) => $w->update(['week_number' => $i + 1]));

        return response()->json(['success' => true]);
    }

    // ── Content ───────────────────────────────────────────────────────────────

    public function storeContent(Request $request, Program $program, ModuleWeek $week)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'                    => 'required|string|max:200',
            'content_type'             => 'required|in:video,pdf,link,article',
            'video_url'                => 'required_if:content_type,video|nullable|url',
            'video_duration_minutes'   => 'nullable|integer|min:1',
            'file'                     => 'required_if:content_type,pdf|nullable|file|mimes:pdf|max:20480',
            'external_url'             => 'required_if:content_type,link|nullable|url',
            'text_content'             => 'required_if:content_type,article|nullable|string',
            'is_required'              => 'boolean',
            'is_downloadable'          => 'boolean',
        ]);

        $order = $week->contents()->max('order') + 1;

        $content = [
            'title'            => $data['title'],
            'content_type'     => $data['content_type'],
            'order'            => $order,
            'created_by'       => auth()->id(),
            'is_required'      => $data['is_required'] ?? true,
            'is_downloadable'  => $data['is_downloadable'] ?? false,
        ];

        match ($data['content_type']) {
            'video'   => $content += [
                            'video_url'               => $data['video_url'],
                            'video_duration_minutes'  => $data['video_duration_minutes'] ?? null,
                         ],
            'pdf'     => $content += [
                            'file_path'       => $request->file('file')->store('content-pdfs', 'public'),
                            'is_downloadable' => $data['is_downloadable'] ?? true,
                         ],
            'link'    => $content += ['external_url' => $data['external_url']],
            'article' => $content += ['text_content'  => $data['text_content']],
        };

        $item = $week->contents()->create($content);

        return response()->json(['success' => true, 'content' => $item]);
    }

    public function updateContent(Request $request, Program $program, WeekContent $content)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'                   => 'required|string|max:200',
            'video_url'               => 'nullable|url',
            'video_duration_minutes'  => 'nullable|integer|min:1',
            'external_url'            => 'nullable|url',
            'text_content'            => 'nullable|string',
            'is_required'             => 'boolean',
            'is_downloadable'         => 'boolean',
        ]);

        // Handle PDF replacement
        if ($request->hasFile('file')) {
            $request->validate(['file' => 'file|mimes:pdf|max:20480']);
            if ($content->file_path) Storage::disk('public')->delete($content->file_path);
            $data['file_path'] = $request->file('file')->store('content-pdfs', 'public');
        }

        $content->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyContent(Program $program, WeekContent $content)
    {
        $this->authorise($program);

        if ($content->file_path) Storage::disk('public')->delete($content->file_path);
        $content->delete();

        // Reorder remaining content in the week
        WeekContent::where('module_week_id', $content->module_week_id)
            ->orderBy('order')->get()
            ->each(fn ($c, $i) => $c->update(['order' => $i + 1]));

        return response()->json(['success' => true]);
    }

    public function reorderContents(Request $request, Program $program, ModuleWeek $week)
    {
        $this->authorise($program);
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $position => $contentId) {
            $week->contents()->where('id', $contentId)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    // ── Guards ────────────────────────────────────────────────────────────────

    private function authorise(Program $program): void
    {
        abort_if($program->mentor_id !== auth()->id(), 403);
    }

    private function authoriseModule(Program $program, ProgramModule $module): void
    {
        abort_if($module->program_id !== $program->id, 403);
    }
}