<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $request->validate([
            'question_type' => 'required|in:multiple_choice,true_false,multiple_select',
            'question_text' => 'required|string',
            'question_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'points' => 'required|integer|min:1',
            'explanation' => 'nullable|string',
            'options' => 'required_unless:question_type,true_false|array',
            'correct_answer' => 'required',
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
                // Expected format: ['A' => 'text', 'B' => 'text', ...]
                return $request->options;

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
                // Expected format: array of keys ['A', 'C', 'D']
                $answers = is_array($request->correct_answer) 
                    ? $request->correct_answer 
                    : [$request->correct_answer];
                return ['answers' => $answers];

            default:
                return [];
        }
    }
}