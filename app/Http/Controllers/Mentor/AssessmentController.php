<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ModuleWeek;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /** Create or update the assessment for a week (one per week) */
    public function store(Request $request, Program $program, ModuleWeek $week)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'                => 'required|string|max:200',
            'pass_percentage'      => 'required|integer|min:1|max:100',
            'time_limit_minutes'   => 'nullable|integer|min:1|max:180',
            'randomize_questions'  => 'boolean',
        ]);

        $assessment = Assessment::updateOrCreate(
            ['module_week_id' => $week->id],
            array_merge($data, ['created_by' => auth()->id()])
        );

        // Keep week in sync
        $week->update([
            'has_assessment'             => true,
            'assessment_pass_percentage' => $data['pass_percentage'],
        ]);

        return response()->json(['success' => true, 'assessment' => $assessment]);
    }

    public function update(Request $request, Program $program, Assessment $assessment)
    {
        $this->authorise($program);

        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'pass_percentage'     => 'required|integer|min:1|max:100',
            'time_limit_minutes'  => 'nullable|integer|min:1|max:180',
            'randomize_questions' => 'boolean',
        ]);

        $assessment->update($data);
        $assessment->moduleWeek->update(['assessment_pass_percentage' => $data['pass_percentage']]);

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

    /** Show question management page for an assessment */
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
            'question_type' => 'required|in:multiple_choice,true_false,multiple_select',
            'question_text' => 'required|string',
            'options'       => 'required|array|min:2',
            'options.*'     => 'required|string',
            'correct_answer'=> 'required|array|min:1',
            'correct_answer.*' => 'required|string',
            'explanation'   => 'nullable|string',
            'points'        => 'integer|min:1',
        ]);

        $order    = $assessment->questions()->max('order') + 1;
        $question = $assessment->questions()->create(array_merge($data, ['order' => $order]));

        return response()->json(['success' => true, 'question' => $question]);
    }

    public function updateQuestion(Request $request, Program $program, AssessmentQuestion $question)
    {
        $this->authorise($program);

        $data = $request->validate([
            'question_text'  => 'required|string',
            'options'        => 'required|array|min:2',
            'options.*'      => 'required|string',
            'correct_answer' => 'required|array|min:1',
            'correct_answer.*' => 'required|string',
            'explanation'    => 'nullable|string',
            'points'         => 'integer|min:1',
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

    /**
     * Download blank CSV template.
     * Columns: question_type, question_text, option_a, option_b, option_c, option_d,
     *          correct_answer, points, explanation
     *
     * correct_answer values: option_a | option_b | option_c | option_d
     * For multiple_select: pipe-separated → option_a|option_c
     * For true_false: option_a = True, option_b = False
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="questions_template.csv"',
        ];

        $rows = [
            ['question_type', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'points', 'explanation'],
            ['multiple_choice', 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Hyper Transfer Markup Logic', 'Home Tool Markup Language', 'option_a', '1', 'HTML stands for Hyper Text Markup Language.'],
            ['true_false', 'CSS is used to style web pages.', 'True', 'False', '', '', 'option_a', '1', 'Correct — CSS (Cascading Style Sheets) handles styling.'],
            ['multiple_select', 'Which are JavaScript frameworks? (select all)', 'React', 'Laravel', 'Vue', 'Django', 'option_a|option_c', '1', 'React and Vue are JavaScript frameworks. Laravel and Django are server-side.'],
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $row) fputcsv($file, $row);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import questions from uploaded CSV.
     *
     * Expects columns (in order):
     *   question_type, question_text, option_a, option_b, option_c, option_d,
     *   correct_answer, points, explanation
     *
     * correct_answer must reference the OPTION VALUE (e.g. "True", "React")
     * OR use option_X references ("option_a", "option_b" etc.) — both accepted.
     */
    public function importQuestions(Request $request, Program $program, Assessment $assessment)
    {
        $this->authorise($program);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file    = $request->file('csv_file');
        $handle  = fopen($file->getRealPath(), 'r');
        $header  = null;
        $imported = 0;
        $errors  = [];
        $rowNum  = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                if ($rowNum === 1) {
                    $header = array_map('trim', $row);
                    continue; // skip header row
                }

                if (count($row) < 7) {
                    $errors[] = "Row {$rowNum}: too few columns (expected 9).";
                    continue;
                }

                [$type, $text, $optA, $optB, $optC, $optD, $rawCorrect, $points, $explanation]
                    = array_pad(array_map('trim', $row), 9, '');

                // Build options array (skip blank options)
                $optionMap = array_filter([
                    'option_a' => $optA ?: null,
                    'option_b' => $optB ?: null,
                    'option_c' => $optC ?: null,
                    'option_d' => $optD ?: null,
                ]);
                $options = array_values($optionMap);

                if (count($options) < 2) {
                    $errors[] = "Row {$rowNum}: at least 2 options required.";
                    continue;
                }

                // Resolve correct_answer — support "option_a|option_b" OR actual values
                $rawParts = array_map('trim', explode('|', $rawCorrect));
                $correctAnswer = [];
                foreach ($rawParts as $part) {
                    if (array_key_exists($part, $optionMap)) {
                        $correctAnswer[] = $optionMap[$part];  // resolve option_x → value
                    } elseif (in_array($part, $options)) {
                        $correctAnswer[] = $part;               // direct value
                    } else {
                        $errors[] = "Row {$rowNum}: correct_answer '{$part}' not found in options.";
                    }
                }

                if (empty($correctAnswer)) continue;

                $validTypes = ['multiple_choice', 'true_false', 'multiple_select'];
                if (!in_array($type, $validTypes)) {
                    $errors[] = "Row {$rowNum}: unknown question_type '{$type}'.";
                    continue;
                }

                $order = $assessment->questions()->max('order') + 1;

                $assessment->questions()->create([
                    'question_type'  => $type,
                    'question_text'  => $text,
                    'options'        => $options,
                    'correct_answer' => $correctAnswer,
                    'points'         => max(1, (int) $points),
                    'explanation'    => $explanation ?: null,
                    'order'          => $order,
                ]);

                $imported++;
            }

            fclose($handle);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with(['message' => 'Import failed: ' . $e->getMessage(), 'alert-type' => 'error']);
        }

        $msg = "{$imported} question(s) imported successfully.";
        if ($errors) {
            $msg .= ' ' . count($errors) . ' row(s) skipped: ' . implode(' | ', array_slice($errors, 0, 3));
        }

        return back()->with(['message' => $msg, 'alert-type' => $errors ? 'warning' : 'success']);
    }

    // ── Guard ─────────────────────────────────────────────────────────────────

    private function authorise(Program $program): void
    {
        abort_if($program->mentor_id !== auth()->id(), 403);
    }
}