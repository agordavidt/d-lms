<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Program;
use App\Models\ModuleWeek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    // ── Store / Update ────────────────────────────────────────────────────────

    /**
     * Create or update an assessment for a week.
     *
     * Weekly assessments: no pass_percentage stored — always 100% in scoring service.
     * Final exam: pass_percentage stored (default 75, min 75).
     * Only one final exam allowed per program.
     */
    public function store(Request $request, Program $program, ModuleWeek $week)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'time_limit_minutes'  => 'nullable|integer|min:1|max:300',
            'randomize_questions' => 'boolean',
            'is_final'            => 'boolean',
            'pass_percentage'     => 'nullable|integer|min:75|max:100',
        ]);

        if (! empty($data['is_final'])) {
            $conflict = Assessment::whereHas('moduleWeek.programModule',
                fn ($q) => $q->where('program_id', $program->id)
            )->where('is_final', true)
             ->where('module_week_id', '!=', $week->id)
             ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'This program already has a final examination. Only one is allowed.',
                ], 422);
            }
        }

        $assessment = Assessment::updateOrCreate(
            ['module_week_id' => $week->id],
            [
                'title'               => $data['title'],
                'time_limit_minutes'  => $data['time_limit_minutes'] ?? null,
                'randomize_questions' => $data['randomize_questions'] ?? false,
                'is_final'            => $data['is_final'] ?? false,
                // pass_percentage only meaningful for final exam
                'pass_percentage'     => ! empty($data['is_final'])
                                            ? ($data['pass_percentage'] ?? Assessment::FINAL_PASS_PERCENTAGE)
                                            : Assessment::FINAL_PASS_PERCENTAGE,
                'created_by'          => auth()->id(),
            ]
        );

        $week->update(['has_assessment' => true]);

        return response()->json(['success' => true, 'assessment' => $assessment]);
    }

    public function update(Request $request, Program $program, Assessment $assessment)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'time_limit_minutes'  => 'nullable|integer|min:1|max:300',
            'randomize_questions' => 'boolean',
            'is_final'            => 'boolean',
            'pass_percentage'     => 'nullable|integer|min:75|max:100',
        ]);

        if (! empty($data['is_final'])) {
            $conflict = Assessment::whereHas('moduleWeek.programModule',
                fn ($q) => $q->where('program_id', $program->id)
            )->where('is_final', true)
             ->where('id', '!=', $assessment->id)
             ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'This program already has a final examination.',
                ], 422);
            }
        }

        $assessment->update([
            'title'               => $data['title'],
            'time_limit_minutes'  => $data['time_limit_minutes'] ?? null,
            'randomize_questions' => $data['randomize_questions'] ?? false,
            'is_final'            => $data['is_final'] ?? false,
            'pass_percentage'     => ! empty($data['is_final'])
                                        ? ($data['pass_percentage'] ?? Assessment::FINAL_PASS_PERCENTAGE)
                                        : Assessment::FINAL_PASS_PERCENTAGE,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Program $program, Assessment $assessment)
    {
        $this->authorise($program);

        $assessment->moduleWeek->update(['has_assessment' => false]);
        $assessment->delete();

        return response()->json(['success' => true]);
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function questions(Program $program, Assessment $assessment)
    {
        $this->authorise($program);
        $assessment->load('questions');
        $week = $assessment->moduleWeek;

        return view('mentor.assessments.questions', compact('program', 'assessment', 'week'));
    }

    public function storeQuestion(Request $request, Program $program, Assessment $assessment)
    {
        $this->authorise($program);

        $data = $request->validate([
            'question_type'    => 'required|in:multiple_choice,true_false,multiple_select',
            'question_text'    => 'required|string',
            'options'          => 'required|array|min:2',
            'options.*'        => 'required|string',
            'correct_answer'   => 'required|array|min:1',
            'correct_answer.*' => 'required|string',
            'explanation'      => 'nullable|string',
            'points'           => 'integer|min:1',
        ]);

        $question = $assessment->questions()->create(array_merge(
            $data,
            ['order' => $assessment->questions()->max('order') + 1]
        ));

        return response()->json(['success' => true, 'question' => $question]);
    }

    public function updateQuestion(Request $request, Program $program, Assessment $assessment, AssessmentQuestion $question)
    {
        $this->authorise($program);

        $data = $request->validate([
            'question_text'    => 'required|string',
            'options'          => 'required|array|min:2',
            'options.*'        => 'required|string',
            'correct_answer'   => 'required|array|min:1',
            'correct_answer.*' => 'required|string',
            'explanation'      => 'nullable|string',
            'points'           => 'integer|min:1',
        ]);

        $question->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyQuestion(Program $program, AssessmentQuestion $question)
    {
        $this->authorise($program);
        $question->delete();

        return response()->json(['success' => true]);
    }

    // ── CSV Import ────────────────────────────────────────────────────────────

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="questions_template.csv"',
        ];

        $rows = [
            ['question_type', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'points', 'explanation'],
            ['multiple_choice', 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Hyper Transfer Markup Logic', 'Home Tool Markup Language', 'option_a', '1', 'HTML stands for Hyper Text Markup Language.'],
            ['true_false', 'CSS is used to style web pages.', 'True', 'False', '', '', 'option_a', '1', 'CSS handles styling.'],
            ['multiple_select', 'Which are JavaScript frameworks?', 'React', 'Laravel', 'Vue', 'Django', 'option_a|option_c', '1', 'React and Vue are JS frameworks.'],
        ];

        return response()->stream(function () use ($rows) {
            $f = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($f, $row);
            fclose($f);
        }, 200, $headers);
    }

    public function importQuestions(Request $request, Program $program, Assessment $assessment)
    {
        $this->authorise($program);
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:2048']);

        $handle   = fopen($request->file('csv_file')->getRealPath(), 'r');
        $imported = 0;
        $errors   = [];
        $rowNum   = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if ($rowNum === 1) continue; // skip header

                if (count($row) < 7) {
                    $errors[] = "Row {$rowNum}: too few columns.";
                    continue;
                }

                [$type, $text, $optA, $optB, $optC, $optD, $rawCorrect, $points, $explanation]
                    = array_pad(array_map('trim', $row), 9, '');

                $optionMap = array_filter([
                    'option_a' => $optA ?: null,
                    'option_b' => $optB ?: null,
                    'option_c' => $optC ?: null,
                    'option_d' => $optD ?: null,
                ]);
                $options = array_values($optionMap);

                if (count($options) < 2) {
                    $errors[] = "Row {$rowNum}: need at least 2 options.";
                    continue;
                }

                $correctAnswer = [];
                foreach (array_map('trim', explode('|', $rawCorrect)) as $part) {
                    if (array_key_exists($part, $optionMap))    $correctAnswer[] = $optionMap[$part];
                    elseif (in_array($part, $options))           $correctAnswer[] = $part;
                    else                                          $errors[] = "Row {$rowNum}: '{$part}' not in options.";
                }

                if (empty($correctAnswer)) continue;

                if (! in_array($type, ['multiple_choice', 'true_false', 'multiple_select'])) {
                    $errors[] = "Row {$rowNum}: unknown type '{$type}'.";
                    continue;
                }

                $assessment->questions()->create([
                    'question_type'  => $type,
                    'question_text'  => $text,
                    'options'        => $options,
                    'correct_answer' => $correctAnswer,
                    'points'         => max(1, (int) $points),
                    'explanation'    => $explanation ?: null,
                    'order'          => $assessment->questions()->max('order') + 1,
                ]);
                $imported++;
            }

            fclose($handle);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Import failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }

        $msg = "{$imported} question(s) imported.";
        if ($errors) $msg .= ' ' . count($errors) . ' skipped: ' . implode(' | ', array_slice($errors, 0, 3));

        return back()->with(['message' => $msg, 'alert-type' => $errors ? 'warning' : 'success']);
    }

    // ── Guard ─────────────────────────────────────────────────────────────────

    private function authorise(Program $program): void
    {
        abort_if($program->mentor_id !== auth()->id(), 403);
    }
}