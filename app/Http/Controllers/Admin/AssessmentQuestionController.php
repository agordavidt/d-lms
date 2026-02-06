<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class AssessmentQuestionController extends Controller
{
    /**
     * Show questions for an assessment
     */
    public function index(Assessment $assessment)
    {
        $assessment->load(['moduleWeek.programModule.program', 'questions' => function($query) {
            $query->orderBy('order');
        }]);

        return view('admin.assessments.questions.index', compact('assessment'));
    }

    /**
     * Store a new question
     */
    public function store(Request $request, Assessment $assessment)
    {
        // First, decode JSON options if they were sent as JSON string
        if ($request->has('options') && is_string($request->options)) {
            $request->merge(['options' => json_decode($request->options, true)]);
        }
        
        // Decode correct_answer if it's JSON string (for multiple_select)
        if ($request->has('correct_answer') && is_string($request->correct_answer)) {
            $decoded = json_decode($request->correct_answer, true);
            if ($decoded !== null) {
                $request->merge(['correct_answer' => $decoded]);
            }
        }

        $request->validate([
            'question_type' => 'required|in:multiple_choice,true_false,multiple_select',
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'points' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
        ]);

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('question_image')) {
                $imagePath = $request->file('question_image')->store('assessments/questions', 'public');
            }

            // Get next order
            $nextOrder = $assessment->questions()->max('order') + 1;

            // Format options and correct answer based on question type
            $options = $this->formatOptions($request);
            $correctAnswer = $this->formatCorrectAnswer($request);

            $question = AssessmentQuestion::create([
                'assessment_id' => $assessment->id,
                'question_type' => $request->question_type,
                'question_text' => $request->question_text,
                'question_image' => $imagePath,
                'points' => $request->points,
                'order' => $nextOrder,
                'explanation' => $request->explanation,
                'options' => $options,
                'correct_answer' => $correctAnswer,
            ]);

            AuditLog::log('question_created', auth()->user(), [
                'description' => 'Added question to assessment: ' . $assessment->title,
                'model_type' => AssessmentQuestion::class,
                'model_id' => $question->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question added successfully!',
                'question' => $question
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a question
     */
    public function update(Request $request, Assessment $assessment, AssessmentQuestion $question)
    {
        $request->validate([
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'points' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
            'options' => 'required_unless:question_type,' . $question->question_type . ',true_false|array',
            'correct_answer' => 'required',
        ]);

        try {
            // Handle image upload
            if ($request->hasFile('question_image')) {
                // Delete old image
                if ($question->question_image) {
                    Storage::disk('public')->delete($question->question_image);
                }
                $imagePath = $request->file('question_image')->store('assessments/questions', 'public');
                $question->question_image = $imagePath;
            }

            // Format options and correct answer
            $options = $this->formatOptions($request, $question->question_type);
            $correctAnswer = $this->formatCorrectAnswer($request, $question->question_type);

            $question->update([
                'question_text' => $request->question_text,
                'points' => $request->points,
                'explanation' => $request->explanation,
                'options' => $options,
                'correct_answer' => $correctAnswer,
            ]);

            AuditLog::log('question_updated', auth()->user(), [
                'description' => 'Updated question in assessment: ' . $assessment->title,
                'model_type' => AssessmentQuestion::class,
                'model_id' => $question->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully!',
                'question' => $question
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a question
     */
    public function destroy(Assessment $assessment, AssessmentQuestion $question)
    {
        try {
            // Delete image if exists
            if ($question->question_image) {
                Storage::disk('public')->delete($question->question_image);
            }

            AuditLog::log('question_deleted', auth()->user(), [
                'description' => 'Deleted question from assessment: ' . $assessment->title,
                'model_type' => AssessmentQuestion::class,
                'model_id' => $question->id,
            ]);

            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder questions
     */
    public function reorder(Request $request, Assessment $assessment)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:assessment_questions,id',
            'questions.*.order' => 'required|integer',
        ]);

        try {
            foreach ($request->questions as $questionData) {
                AssessmentQuestion::where('id', $questionData['id'])
                    ->update(['order' => $questionData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Questions reordered successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder questions.'
            ], 500);
        }
    }

    /**
     * Format options based on question type
     */
    protected function formatOptions(Request $request, ?string $questionType = null): array
    {
        $type = $questionType ?? $request->question_type;

        switch ($type) {
            case 'true_false':
                return [
                    'true' => 'True',
                    'false' => 'False'
                ];

            case 'multiple_choice':
            case 'multiple_select':
                // If options already decoded from JSON, use it
                if ($request->has('options') && is_array($request->options)) {
                    return $request->options;
                }
                
                // Otherwise build from individual fields
                $options = [];
                foreach (['A', 'B', 'C', 'D'] as $key) {
                    $value = $request->input("options.{$key}");
                    if ($value) {
                        $options[$key] = $value;
                    }
                }
                return $options;

            default:
                return [];
        }
    }

    /**
     * Format correct answer based on question type
     */
    protected function formatCorrectAnswer(Request $request, ?string $questionType = null): array
    {
        $type = $questionType ?? $request->question_type;

        switch ($type) {
            case 'multiple_choice':
            case 'true_false':
                return ['answer' => $request->correct_answer];

            case 'multiple_select':
                // If already an array (decoded from JSON), use it
                if (is_array($request->correct_answer)) {
                    return ['answers' => $request->correct_answer];
                }
                
                // Otherwise it should be a single value
                $answers = is_array($request->correct_answer) 
                    ? $request->correct_answer 
                    : [$request->correct_answer];
                return ['answers' => $answers];

            default:
                return [];
        }
    }

    // import questions from csv / excel
    /**
     * Show import form
     */
    public function showImport(Assessment $assessment)
    {
        $assessment->load(['moduleWeek.programModule.program']);
        return view('admin.assessments.questions.import', compact('assessment'));
    }

    /**
     * Import questions from CSV/Excel
     */
    public function import(Request $request, Assessment $assessment)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            // Parse file based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $questions = $this->parseExcel($file);
            } else {
                $questions = $this->parseCsv($file);
            }

            if (empty($questions)) {
                return back()->with([
                    'message' => 'No valid questions found in file',
                    'alert-type' => 'warning'
                ]);
            }

            // Get starting order
            $startOrder = $assessment->questions()->max('order') + 1;
            $imported = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($questions as $index => $questionData) {
                try {
                    // Validate question data
                    $validated = $this->validateQuestionData($questionData, $index + 2); // +2 for header row
                    
                    if ($validated['error']) {
                        $errors[] = $validated['error'];
                        continue;
                    }

                    // Create question
                    AssessmentQuestion::create([
                        'assessment_id' => $assessment->id,
                        'question_type' => $validated['question_type'],
                        'question_text' => $validated['question_text'],
                        'points' => $validated['points'],
                        'order' => $startOrder + $imported,
                        'explanation' => $validated['explanation'],
                        'options' => $validated['options'],
                        'correct_answer' => $validated['correct_answer'],
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            // Log the import
            AuditLog::log('questions_imported', auth()->user(), [
                'description' => "Imported {$imported} questions to assessment: " . $assessment->title,
                'model_type' => Assessment::class,
                'model_id' => $assessment->id,
            ]);

            $message = "{$imported} question(s) imported successfully";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " error(s) occurred.";
            }

            return redirect()->route('admin.assessments.questions.index', $assessment->id)
                ->with([
                    'message' => $message,
                    'alert-type' => $imported > 0 ? 'success' : 'warning',
                    'import_errors' => $errors
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with([
                'message' => 'Import failed: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * Download CSV template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="question_import_template.csv"',
        ];

        $template = "question_text,question_type,option_a,option_b,option_c,option_d,correct_answer,points,explanation\n";
        $template .= "\"What is the primary purpose of a database?\",multiple_choice,\"To store files\",\"To store and manage data\",\"To run applications\",\"To browse the internet\",B,1,\"Databases are designed to efficiently store, retrieve, and manage data\"\n";
        $template .= "\"SQL stands for Structured Query Language\",true_false,\"\",\"\",\"\",\"\",true,1,\"SQL is indeed an acronym for Structured Query Language\"\n";
        $template .= "\"Which of the following are programming languages?\",multiple_select,\"Python\",\"HTML\",\"JavaScript\",\"CSS\",\"A,C\",2,\"Python and JavaScript are programming languages\"\n";

        return response($template, 200, $headers);
    }

    /**
     * Parse CSV file
     */
    protected function parseCsv($file): array
    {
        $questions = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header row
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 9 || empty(trim($row[0]))) {
                continue; // Skip empty or invalid rows
            }

            $questions[] = [
                'question_text' => trim($row[0]),
                'question_type' => trim($row[1]),
                'option_a' => trim($row[2] ?? ''),
                'option_b' => trim($row[3] ?? ''),
                'option_c' => trim($row[4] ?? ''),
                'option_d' => trim($row[5] ?? ''),
                'correct_answer' => trim($row[6]),
                'points' => trim($row[7] ?? '1'),
                'explanation' => trim($row[8] ?? ''),
            ];
        }
        
        fclose($handle);
        return $questions;
    }

    /**
     * Parse Excel file
     */
    protected function parseExcel($file): array
    {
        // Using PhpSpreadsheet
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $questions = [];
        
        // Skip header row
        array_shift($rows);
        
        foreach ($rows as $row) {
            if (empty(trim($row[0] ?? ''))) {
                continue; // Skip empty rows
            }

            $questions[] = [
                'question_text' => trim($row[0]),
                'question_type' => trim($row[1] ?? ''),
                'option_a' => trim($row[2] ?? ''),
                'option_b' => trim($row[3] ?? ''),
                'option_c' => trim($row[4] ?? ''),
                'option_d' => trim($row[5] ?? ''),
                'correct_answer' => trim($row[6] ?? ''),
                'points' => trim($row[7] ?? '1'),
                'explanation' => trim($row[8] ?? ''),
            ];
        }
        
        return $questions;
    }

    /**
     * Validate imported question data
     */
    protected function validateQuestionData(array $data, int $rowNumber): array
    {
        // Validate question type
        $validTypes = ['multiple_choice', 'true_false', 'multiple_select'];
        if (!in_array($data['question_type'], $validTypes)) {
            return [
                'error' => "Row {$rowNumber}: Invalid question type '{$data['question_type']}'. Must be: multiple_choice, true_false, or multiple_select"
            ];
        }

        // Validate question text
        if (empty($data['question_text'])) {
            return ['error' => "Row {$rowNumber}: Question text is required"];
        }

        // Validate points
        $points = (int) $data['points'];
        if ($points < 1 || $points > 10) {
            return ['error' => "Row {$rowNumber}: Points must be between 1 and 10"];
        }

        // Build options and correct answer based on type
        $options = [];
        $correctAnswer = [];

        switch ($data['question_type']) {
            case 'multiple_choice':
                // Build options
                foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $key => $field) {
                    if (!empty($data[$field])) {
                        $options[$key] = $data[$field];
                    }
                }

                if (count($options) < 2) {
                    return ['error' => "Row {$rowNumber}: Multiple choice requires at least 2 options"];
                }

                // Validate correct answer
                $correct = strtoupper(trim($data['correct_answer']));
                if (!isset($options[$correct])) {
                    return ['error' => "Row {$rowNumber}: Correct answer '{$correct}' not found in options"];
                }

                $correctAnswer = ['answer' => $correct];
                break;

            case 'true_false':
                $options = ['true' => 'True', 'false' => 'False'];
                
                $correct = strtolower(trim($data['correct_answer']));
                if (!in_array($correct, ['true', 'false'])) {
                    return ['error' => "Row {$rowNumber}: True/False answer must be 'true' or 'false'"];
                }

                $correctAnswer = ['answer' => $correct];
                break;

            case 'multiple_select':
                // Build options
                foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $key => $field) {
                    if (!empty($data[$field])) {
                        $options[$key] = $data[$field];
                    }
                }

                if (count($options) < 2) {
                    return ['error' => "Row {$rowNumber}: Multiple select requires at least 2 options"];
                }

                // Parse correct answers (comma-separated: A,C,D)
                $correctKeys = array_map('trim', explode(',', strtoupper($data['correct_answer'])));
                
                foreach ($correctKeys as $key) {
                    if (!isset($options[$key])) {
                        return ['error' => "Row {$rowNumber}: Correct answer '{$key}' not found in options"];
                    }
                }

                $correctAnswer = ['answers' => $correctKeys];
                break;
        }

        return [
            'error' => null,
            'question_type' => $data['question_type'],
            'question_text' => $data['question_text'],
            'points' => $points,
            'explanation' => $data['explanation'] ?? null,
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ];
    }
}