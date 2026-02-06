@extends('layouts.admin')

@section('title', 'Manage Questions')
@section('breadcrumb-parent', 'Assessments')
@section('breadcrumb-current', 'Questions')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-0">{{ $assessment->title }}</h4>
                    <small class="text-muted">Week {{ $assessment->moduleWeek->week_number }}: {{ $assessment->moduleWeek->title }}</small>
                </div>
                <div>
                    @if($assessment->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-warning">Inactive</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <!-- Assessment Summary -->
                <div class="alert alert-light border">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Questions:</strong> {{ $assessment->questions->count() }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Points:</strong> {{ $assessment->total_points }}
                        </div>
                        <div class="col-md-3">
                            <strong>Pass:</strong> {{ $assessment->pass_percentage }}%
                        </div>
                        <div class="col-md-3">
                            <strong>Attempts:</strong> {{ $assessment->max_attempts }}
                        </div>
                    </div>
                </div>

                <!-- Questions List -->               

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Questions</h5>
                    <div>
                        <a href="{{ route('admin.assessments.questions.import-form', $assessment->id) }}" 
                        class="btn btn-success btn-sm mr-2">
                            <i class="icon-upload"></i> Import from CSV/Excel
                        </a>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addQuestionModal">
                            <i class="icon-plus"></i> Add Question
                        </button>
                    </div>
                </div>

                @if($assessment->questions->count() > 0)
                    <div id="questionsList">
                        @foreach($assessment->questions as $question)
                        <div class="card mb-3" data-question-id="{{ $question->id }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-start mb-2">
                                            <span class="badge badge-secondary mr-2">Q{{ $loop->iteration }}</span>
                                            <span class="badge badge-info mr-2">{{ $question->points }} {{ Str::plural('point', $question->points) }}</span>
                                            <span class="badge badge-light">{{ ucwords(str_replace('_', ' ', $question->question_type)) }}</span>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <strong>{{ $question->question_text }}</strong>
                                        </div>

                                        @if($question->question_image)
                                        <div class="mb-2">
                                            <img src="{{ Storage::url($question->question_image) }}" 
                                                 alt="Question Image" 
                                                 style="max-width: 200px; max-height: 150px;"
                                                 class="img-thumbnail">
                                        </div>
                                        @endif

                                        <!-- Options Display -->
                                        <div class="ml-4">
                                            @if($question->question_type === 'multiple_choice')
                                                @foreach($question->options as $key => $option)
                                                    <div class="form-check">
                                                        @if($question->correct_answer['answer'] === $key)
                                                            <i class="icon-check text-success"></i>
                                                        @else
                                                            <i class="icon-close text-muted" style="opacity: 0.3;"></i>
                                                        @endif
                                                        <strong>{{ $key }}:</strong> {{ $option }}
                                                    </div>
                                                @endforeach
                                            @elseif($question->question_type === 'true_false')
                                                <div class="form-check">
                                                    @if($question->correct_answer['answer'] === 'true')
                                                        <i class="icon-check text-success"></i> <strong>True</strong>
                                                    @else
                                                        <i class="icon-close text-muted" style="opacity: 0.3;"></i> True
                                                    @endif
                                                </div>
                                                <div class="form-check">
                                                    @if($question->correct_answer['answer'] === 'false')
                                                        <i class="icon-check text-success"></i> <strong>False</strong>
                                                    @else
                                                        <i class="icon-close text-muted" style="opacity: 0.3;"></i> False
                                                    @endif
                                                </div>
                                            @elseif($question->question_type === 'multiple_select')
                                                @foreach($question->options as $key => $option)
                                                    <div class="form-check">
                                                        @if(in_array($key, $question->correct_answer['answers']))
                                                            <i class="icon-check text-success"></i>
                                                        @else
                                                            <i class="icon-close text-muted" style="opacity: 0.3;"></i>
                                                        @endif
                                                        <strong>{{ $key }}:</strong> {{ $option }}
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        @if($question->explanation)
                                        <div class="mt-2 p-2 bg-light rounded">
                                            <small class="text-muted"><strong>Explanation:</strong> {{ $question->explanation }}</small>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="ml-3">
                                        <button type="button" class="btn btn-sm btn-light mb-1" 
                                                onclick="editQuestion({{ $question->id }})" 
                                                title="Edit">
                                            <i class="icon-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteQuestion({{ $question->id }})" 
                                                title="Delete">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="icon-note" style="font-size: 48px; color: #ccc;"></i>
                        <h5 class="text-muted mt-3">No Questions Added Yet</h5>
                        <p class="text-muted mb-4">Start building your assessment by adding questions</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addQuestionModal">
                            <i class="icon-plus"></i> Add First Question
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Actions</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.weeks.show', $assessment->module_week_id) }}" 
                   class="btn btn-secondary btn-block mb-2">
                    Back to Week
                </a>

                <a href="{{ route('admin.assessments.edit', $assessment->id) }}" 
                   class="btn btn-primary btn-block mb-2">
                    Edit Settings
                </a>

                @if($assessment->questions->count() > 0)
                    <button type="button" 
                            class="btn btn-{{ $assessment->is_active ? 'warning' : 'success' }} btn-block mb-2"
                            onclick="toggleAssessmentStatus()">
                        {{ $assessment->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                @else
                    <button type="button" class="btn btn-success btn-block mb-2" disabled title="Add questions first">
                        Activate
                    </button>
                @endif

                <hr>

                <button type="button" class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#addQuestionModal">
                    Add Question
                </button>

                <a href="{{ route('admin.assessments.questions.import-form', $assessment->id) }}" 
                   class="btn btn-success btn-block">
                    Import from CSV/Excel
                </a>
            </div>
        </div>

        <!-- Status -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Status</h4>
            </div>
            <div class="card-body">
                @if($assessment->questions->count() === 0)
                    <div class="alert alert-warning mb-0">
                        <i class="icon-info"></i> Add at least 3 questions to activate assessment
                    </div>
                @elseif($assessment->questions->count() < 3)
                    <div class="alert alert-warning mb-0">
                        <i class="icon-info"></i> {{ 3 - $assessment->questions->count() }} more question(s) recommended
                    </div>
                @elseif(!$assessment->is_active)
                    <div class="alert alert-info mb-0">
                        <i class="icon-check"></i> Ready to activate!
                    </div>
                @else
                    <div class="alert alert-success mb-0">
                        <i class="icon-check"></i> Assessment is live
                    </div>
                @endif
            </div>
        </div>

        <!-- Tips -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Tips</h4>
            </div>
            <div class="card-body">
                <ul class="mb-0 small">
                    <li class="mb-2">Mix question types for variety</li>
                    <li class="mb-2">Add explanations to help learning</li>
                    <li class="mb-2">Use images to clarify questions</li>
                    <li class="mb-2">Point values reflect difficulty</li>
                    <li>Test your questions before activating</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="addQuestionForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Question</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Question Type -->
                    <div class="form-group">
                        <label>Question Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="question_type" id="questionType" required>
                            <option value="multiple_choice">Multiple Choice (Single Answer)</option>
                            <option value="true_false">True/False</option>
                            <option value="multiple_select">Multiple Select (Multiple Answers)</option>
                        </select>
                    </div>

                    <!-- Question Text -->
                    <div class="form-group">
                        <label>Question <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="question_text" rows="3" 
                                  placeholder="Enter your question here..." required></textarea>
                    </div>

                    <!-- Question Image (Optional) -->
                    <div class="form-group">
                        <label>Question Image (Optional)</label>
                        <input type="file" class="form-control-file" name="question_image" 
                               accept="image/jpeg,image/png,image/jpg,image/gif">
                        <small class="text-muted">Max 2MB (JPEG, PNG, GIF)</small>
                    </div>

                    <!-- Points -->
                    <div class="form-group">
                        <label>Points <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="points" value="1" min="1" max="10" required>
                    </div>

                    <!-- Options Container (Dynamic based on type) -->
                    <div id="optionsContainer">
                        <!-- Multiple Choice Options (Default) -->
                        <div id="multipleChoiceOptions">
                            <label>Answer Options <span class="text-danger">*</span></label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="correct_answer" value="A" id="optionA" required>
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options[A]" placeholder="Option A" required style="width: calc(100% - 30px);">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="correct_answer" value="B" id="optionB">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options[B]" placeholder="Option B" required style="width: calc(100% - 30px);">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="correct_answer" value="C" id="optionC">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options[C]" placeholder="Option C" required style="width: calc(100% - 30px);">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="correct_answer" value="D" id="optionD">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options[D]" placeholder="Option D" required style="width: calc(100% - 30px);">
                            </div>
                            <small class="text-muted">Select the correct answer by clicking the radio button</small>
                        </div>

                        <!-- True/False Options (Hidden by default) -->
                        <div id="trueFalseOptions" style="display: none;">
                            <label>Correct Answer <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer_tf" value="true" id="tfTrue">
                                <label class="form-check-label" for="tfTrue">True</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="correct_answer_tf" value="false" id="tfFalse">
                                <label class="form-check-label" for="tfFalse">False</label>
                            </div>
                        </div>

                        <!-- Multiple Select Options (Hidden by default) -->
                        <div id="multipleSelectOptions" style="display: none;">
                            <label>Answer Options <span class="text-danger">*</span></label>
                            <small class="text-muted d-block mb-2">Check all correct answers</small>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="correct_answer_ms[]" value="A" id="msOptionA">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options_ms[A]" placeholder="Option A" style="width: calc(100% - 30px);">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="correct_answer_ms[]" value="B" id="msOptionB">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options_ms[B]" placeholder="Option B" style="width: calc(100% - 30px);">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="correct_answer_ms[]" value="C" id="msOptionC">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options_ms[C]" placeholder="Option C" style="width: calc(100% - 30px);">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="correct_answer_ms[]" value="D" id="msOptionD">
                                <input type="text" class="form-control form-control-sm d-inline-block" 
                                       name="options_ms[D]" placeholder="Option D" style="width: calc(100% - 30px);">
                            </div>
                        </div>
                    </div>

                    <!-- Explanation -->
                    <div class="form-group mt-3">
                        <label>Explanation (Shown After Submission)</label>
                        <textarea class="form-control" name="explanation" rows="2" 
                                  placeholder="Explain why this is the correct answer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Question type switching
document.getElementById('questionType').addEventListener('change', function() {
    const type = this.value;
    
    document.getElementById('multipleChoiceOptions').style.display = 'none';
    document.getElementById('trueFalseOptions').style.display = 'none';
    document.getElementById('multipleSelectOptions').style.display = 'none';
    
    if (type === 'multiple_choice') {
        document.getElementById('multipleChoiceOptions').style.display = 'block';
    } else if (type === 'true_false') {
        document.getElementById('trueFalseOptions').style.display = 'block';
    } else if (type === 'multiple_select') {
        document.getElementById('multipleSelectOptions').style.display = 'block';
    }
});

// Add question form submission
document.getElementById('addQuestionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const questionType = formData.get('question_type');
    
    // Handle correct answer based on type
    if (questionType === 'true_false') {
        formData.set('correct_answer', formData.get('correct_answer_tf'));
        formData.delete('correct_answer_tf');
    } else if (questionType === 'multiple_select') {
        // Get options from ms fields
        const options = {};
        ['A', 'B', 'C', 'D'].forEach(key => {
            const val = formData.get(`options_ms[${key}]`);
            if (val) options[key] = val;
        });
        
        // Clear old fields
        formData.delete('options_ms[A]');
        formData.delete('options_ms[B]');
        formData.delete('options_ms[C]');
        formData.delete('options_ms[D]');
        
        // Set as JSON
        formData.set('options', JSON.stringify(options));
        
        // Correct answers as array
        const correctAnswers = formData.getAll('correct_answer_ms[]');
        formData.delete('correct_answer_ms[]');
        formData.set('correct_answer', JSON.stringify(correctAnswers));
    } else {
        // Multiple choice - convert options to JSON
        const options = {};
        ['A', 'B', 'C', 'D'].forEach(key => {
            const val = formData.get(`options[${key}]`);
            if (val) options[key] = val;
        });
        
        formData.delete('options[A]');
        formData.delete('options[B]');
        formData.delete('options[C]');
        formData.delete('options[D]');
        
        formData.set('options', JSON.stringify(options));
    }
    
    fetch('{{ route("admin.assessments.questions.store", $assessment->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(data.message || 'Failed to add question');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('An error occurred. Please try again.');
    });
});

// Delete question
function deleteQuestion(questionId) {
    if (!confirm('Are you sure you want to delete this question?')) {
        return;
    }
    
    fetch(`{{ route("admin.assessments.questions.destroy", ["assessment" => $assessment->id, "question" => "__ID__"]) }}`.replace('__ID__', questionId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
    });
}

// Toggle assessment status
function toggleAssessmentStatus() {
    fetch('{{ route("admin.assessments.toggle-active", $assessment->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(data.message);
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
    });
}

// Edit question (placeholder - will implement in next phase)
function editQuestion(questionId) {
    toastr.info('Edit functionality coming soon. For now, delete and re-add the question.');
}
</script>
@endpush