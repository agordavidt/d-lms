@extends('layouts.admin')

@section('title', 'Taking Assessment')

@push('styles')
<style>
    body {
        background: #f8f9fa;
    }

    .assessment-taking {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .progress-header {
        background: #fff;
        padding: 20px 32px;
        border-radius: 8px 8px 0 0;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .progress-info {
        font-size: 14px;
        color: #666;
    }

    .timer {
        font-size: 16px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .timer.warning {
        color: #f57c00;
    }

    .question-container {
        background: #fff;
        padding: 48px;
        border-radius: 0 0 8px 8px;
        min-height: 400px;
    }

    .question-number {
        font-size: 14px;
        color: #7571f9;
        font-weight: 600;
        margin-bottom: 16px;
    }

    .question-text {
        font-size: 20px;
        font-weight: 500;
        color: #1a1a1a;
        line-height: 1.6;
        margin-bottom: 32px;
    }

    .question-image {
        max-width: 100%;
        height: auto;
        margin-bottom: 32px;
        border-radius: 6px;
    }

    .options-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .option-item {
        margin-bottom: 16px;
    }

    .option-label {
        display: flex;
        align-items: flex-start;
        padding: 20px;
        background: #f8f9fa;
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .option-label:hover {
        background: #f0f0ff;
        border-color: #7571f9;
    }

    .option-input {
        margin-right: 16px;
        margin-top: 4px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .option-text {
        flex: 1;
        font-size: 16px;
        color: #333;
        line-height: 1.5;
    }

    input[type="radio"]:checked + .option-text,
    input[type="checkbox"]:checked + .option-text {
        font-weight: 500;
        color: #7571f9;
    }

    .option-label:has(input:checked) {
        background: #f0f0ff;
        border-color: #7571f9;
    }

    .navigation {
        margin-top: 48px;
        padding-top: 32px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .btn {
        padding: 14px 32px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: #7571f9;
        color: white;
    }

    .btn-primary:hover {
        background: #5f5bd1;
    }

    .btn-secondary {
        background: #fff;
        color: #666;
        border: 2px solid #e0e0e0;
    }

    .btn-secondary:hover {
        border-color: #7571f9;
        color: #7571f9;
    }

    .btn-success {
        background: #4caf50;
        color: white;
    }

    .btn-success:hover {
        background: #43a047;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .submit-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .submit-modal.active {
        display: flex;
    }

    .modal-content {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        text-align: center;
    }

    .modal-title {
        font-size: 22px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
    }

    .modal-text {
        font-size: 16px;
        color: #666;
        margin-bottom: 32px;
        line-height: 1.6;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
    }

    .unanswered-info {
        background: #fff3cd;
        padding: 16px;
        border-radius: 6px;
        margin-bottom: 24px;
        font-size: 14px;
        color: #856404;
    }
</style>
@endpush

@section('content')
<div class="assessment-taking">
    <!-- Progress Header -->
    <div class="progress-header">
        <div class="progress-info">
            Question <span id="currentQuestionNum">1</span> of {{ $questions->count() }}
        </div>
        @if($assessment->time_limit_minutes)
        <div class="timer" id="timer">
            Time Remaining: <span id="timeDisplay">{{ $assessment->time_limit_minutes }}:00</span>
        </div>
        @endif
    </div>

    <!-- Question Container -->
    <div class="question-container">
        <form id="assessmentForm">
            @foreach($questions as $index => $question)
            <div class="question-slide" data-question="{{ $index }}" style="{{ $index === 0 ? '' : 'display: none;' }}">
                <div class="question-number">Question {{ $index + 1 }}</div>
                
                <div class="question-text">{{ $question->question_text }}</div>

                @if($question->question_image)
                <img src="{{ Storage::url($question->question_image) }}" alt="Question image" class="question-image">
                @endif

                <ul class="options-list">
                    @if($question->question_type === 'multiple_choice' || $question->question_type === 'true_false')
                        @foreach($question->getOptionsForDisplay() as $key => $option)
                        <li class="option-item">
                            <label class="option-label">
                                <input type="radio" 
                                       name="answers[{{ $question->id }}]" 
                                       value="{{ $key }}"
                                       class="option-input">
                                <span class="option-text">{{ $option }}</span>
                            </label>
                        </li>
                        @endforeach
                    @elseif($question->question_type === 'multiple_select')
                        @foreach($question->options as $key => $option)
                        <li class="option-item">
                            <label class="option-label">
                                <input type="checkbox" 
                                       name="answers[{{ $question->id }}][]" 
                                       value="{{ $key }}"
                                       class="option-input">
                                <span class="option-text">{{ $option }}</span>
                            </label>
                        </li>
                        @endforeach
                    @endif
                </ul>

                <!-- Navigation -->
                <div class="navigation">
                    <div>
                        @if($index > 0)
                        <button type="button" class="btn btn-secondary" onclick="previousQuestion()">
                            Previous Question
                        </button>
                        @endif
                    </div>

                    <div>
                        @if($index < $questions->count() - 1)
                        <button type="button" class="btn btn-primary" onclick="nextQuestion()">
                            Next Question
                        </button>
                        @else
                        <button type="button" class="btn btn-success" onclick="showSubmitModal()">
                            Submit Assessment
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </form>
    </div>
</div>

<!-- Submit Confirmation Modal -->
<div class="submit-modal" id="submitModal">
    <div class="modal-content">
        <div class="modal-title">Submit Assessment?</div>
        <div class="modal-text" id="modalText">
            You have answered <span id="answeredCount">0</span> out of {{ $questions->count() }} questions.
        </div>
        <div id="unansweredWarning" class="unanswered-info" style="display: none;">
            You have <span id="unansweredCount">0</span> unanswered questions. Unanswered questions will be marked as incorrect.
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeSubmitModal()">
                Review Answers
            </button>
            <button type="button" class="btn btn-success" onclick="submitAssessment()">
                Submit Now
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentQuestion = 0;
const totalQuestions = {{ $questions->count() }};
const startTime = new Date('{{ $attempt->started_at }}');

@if($assessment->time_limit_minutes)
const timeLimitMinutes = {{ $assessment->time_limit_minutes }};
const endTime = new Date(startTime.getTime() + timeLimitMinutes * 60000);
@endif

// Navigation
function nextQuestion() {
    if (currentQuestion < totalQuestions - 1) {
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'none';
        currentQuestion++;
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'block';
        document.getElementById('currentQuestionNum').textContent = currentQuestion + 1;
        window.scrollTo(0, 0);
    }
}

function previousQuestion() {
    if (currentQuestion > 0) {
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'none';
        currentQuestion--;
        document.querySelector(`[data-question="${currentQuestion}"]`).style.display = 'block';
        document.getElementById('currentQuestionNum').textContent = currentQuestion + 1;
        window.scrollTo(0, 0);
    }
}

// Timer
@if($assessment->time_limit_minutes)
function updateTimer() {
    const now = new Date();
    const remaining = Math.max(0, endTime - now);
    
    const minutes = Math.floor(remaining / 60000);
    const seconds = Math.floor((remaining % 60000) / 1000);
    
    const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    document.getElementById('timeDisplay').textContent = display;
    
    if (minutes < 5) {
        document.getElementById('timer').classList.add('warning');
    }
    
    if (remaining === 0) {
        submitAssessment();
    }
}

setInterval(updateTimer, 1000);
updateTimer();
@endif

// Submit Modal
function showSubmitModal() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);
    
    let answeredCount = 0;
    const questionIds = @json($questions->pluck('id'));
    
    questionIds.forEach(id => {
        const answer = formData.getAll(`answers[${id}][]`).length > 0 || formData.get(`answers[${id}]`);
        if (answer) answeredCount++;
    });
    
    document.getElementById('answeredCount').textContent = answeredCount;
    
    const unanswered = totalQuestions - answeredCount;
    if (unanswered > 0) {
        document.getElementById('unansweredCount').textContent = unanswered;
        document.getElementById('unansweredWarning').style.display = 'block';
    } else {
        document.getElementById('unansweredWarning').style.display = 'none';
    }
    
    document.getElementById('submitModal').classList.add('active');
}

function closeSubmitModal() {
    document.getElementById('submitModal').classList.remove('active');
}

// Submit Assessment
function submitAssessment() {
    const form = document.getElementById('assessmentForm');
    const formData = new FormData(form);
    
    const answers = {};
    const questionIds = @json($questions->pluck('id'));
    
    questionIds.forEach(id => {
        const multiSelect = formData.getAll(`answers[${id}][]`);
        if (multiSelect.length > 0) {
            answers[id] = multiSelect;
        } else {
            answers[id] = formData.get(`answers[${id}]`);
        }
    });
    
    fetch('{{ route("learner.attempts.submit", $attempt->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ answers })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            toastr.error(data.message || 'Failed to submit assessment');
            closeSubmitModal();
        }
    })
    .catch(error => {
        toastr.error('An error occurred. Please try again.');
        closeSubmitModal();
    });
}

// Prevent accidental page leave
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = '';
});
</script>
@endpush