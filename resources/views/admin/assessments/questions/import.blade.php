@extends('layouts.admin')

@section('title', 'Import Questions')
@section('breadcrumb-parent', 'Assessments')
@section('breadcrumb-current', 'Import Questions')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Import Questions from CSV/Excel</h4>
            </div>
            <div class="card-body">
                <!-- Context -->
                <div class="alert alert-info mb-4">
                    <strong><i class="icon-info"></i> Importing questions for:</strong><br>
                    <div class="mt-2">
                        <strong>Assessment:</strong> {{ $assessment->title }}<br>
                        <strong>Week:</strong> Week {{ $assessment->moduleWeek->week_number }} - {{ $assessment->moduleWeek->title }}
                    </div>
                </div>

                <!-- Import Errors -->
                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning">
                        <strong><i class="icon-alert-triangle"></i> Import Errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Instructions -->
                <div class="mb-4">
                    <h5>How to Import Questions</h5>
                    <ol>
                        <li class="mb-2">Download the CSV template using the button below</li>
                        <li class="mb-2">Fill in your questions following the template format</li>
                        <li class="mb-2">Save the file as CSV or Excel (.xlsx)</li>
                        <li class="mb-2">Upload the file using the form below</li>
                    </ol>
                </div>

                <!-- Download Template -->
                <div class="mb-4 p-3 bg-light border rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Download Template</strong>
                            <p class="mb-0 text-muted small">Use this template as a guide for formatting your questions</p>
                        </div>
                        <a href="{{ route('admin.assessments.questions.template') }}" 
                           class="btn btn-secondary">
                            <i class="icon-download"></i> Download CSV Template
                        </a>
                    </div>
                </div>

                <!-- Upload Form -->
                <form action="{{ route('admin.assessments.questions.import', $assessment->id) }}" 
                      method="POST" 
                      enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Select File <span class="text-danger">*</span></label>
                        <input type="file" 
                               class="form-control-file @error('file') is-invalid @enderror" 
                               name="file" 
                               accept=".csv,.xlsx,.xls"
                               required>
                        <small class="text-muted">Accepted formats: CSV, Excel (.xlsx, .xls) - Max 2MB</small>
                        @error('file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        <i class="icon-info"></i> <strong>Note:</strong> 
                        Questions will be added to existing questions. The import will not replace current questions.
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-upload"></i> Import Questions
                        </button>
                        <a href="{{ route('admin.assessments.questions.index', $assessment->id) }}" 
                           class="btn btn-secondary">
                            <i class="icon-close"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- File Format Guide -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">File Format Guide</h4>
            </div>
            <div class="card-body">
                <h6 class="mb-3">CSV Columns (Required Order)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Column</th>
                                <th>Description</th>
                                <th>Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>question_text</code></td>
                                <td>The question text (required)</td>
                                <td>"What is SQL?"</td>
                            </tr>
                            <tr>
                                <td><code>question_type</code></td>
                                <td>Type: multiple_choice, true_false, or multiple_select</td>
                                <td>multiple_choice</td>
                            </tr>
                            <tr>
                                <td><code>option_a</code></td>
                                <td>Option A text (leave empty for true/false)</td>
                                <td>"A database"</td>
                            </tr>
                            <tr>
                                <td><code>option_b</code></td>
                                <td>Option B text</td>
                                <td>"A language"</td>
                            </tr>
                            <tr>
                                <td><code>option_c</code></td>
                                <td>Option C text</td>
                                <td>"A server"</td>
                            </tr>
                            <tr>
                                <td><code>option_d</code></td>
                                <td>Option D text</td>
                                <td>"A framework"</td>
                            </tr>
                            <tr>
                                <td><code>correct_answer</code></td>
                                <td>
                                    Multiple choice: A, B, C, or D<br>
                                    True/False: true or false<br>
                                    Multiple select: A,C,D (comma-separated)
                                </td>
                                <td>B</td>
                            </tr>
                            <tr>
                                <td><code>points</code></td>
                                <td>Points for this question (1-10)</td>
                                <td>1</td>
                            </tr>
                            <tr>
                                <td><code>explanation</code></td>
                                <td>Explanation shown after submission (optional)</td>
                                <td>"SQL is a query language"</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Tips -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Tips</h4>
            </div>
            <div class="card-body">
                <ul class="mb-0 small">
                    <li class="mb-2"><strong>CSV Format:</strong> Use commas to separate columns</li>
                    <li class="mb-2"><strong>Quotes:</strong> Wrap text in quotes if it contains commas</li>
                    <li class="mb-2"><strong>Empty Fields:</strong> Leave option fields empty for True/False questions</li>
                    <li class="mb-2"><strong>Multiple Select:</strong> Use comma-separated letters (A,C,D) for correct answers</li>
                    <li class="mb-2"><strong>Points:</strong> Must be between 1-10</li>
                    <li class="mb-2"><strong>Validation:</strong> Invalid rows will be skipped with error messages</li>
                    <li><strong>Encoding:</strong> Use UTF-8 encoding for special characters</li>
                </ul>
            </div>
        </div>

        <!-- Example Questions -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Example Rows</h4>
            </div>
            <div class="card-body">
                <p class="small mb-2"><strong>Multiple Choice:</strong></p>
                <pre class="bg-light p-2 small">"What is SQL?",multiple_choice,"Database","Language","Server","Framework",B,1,"SQL is a query language"</pre>

                <p class="small mb-2 mt-3"><strong>True/False:</strong></p>
                <pre class="bg-light p-2 small">"Python is compiled",true_false,"","","","",false,1,"Python is interpreted"</pre>

                <p class="small mb-2 mt-3"><strong>Multiple Select:</strong></p>
                <pre class="bg-light p-2 small">"Select programming languages",multiple_select,"Python","HTML","JavaScript","CSS","A,C",2,"Python and JS are languages"</pre>
            </div>
        </div>

        <!-- Assessment Info -->
        <div class="card mt-3">
            <div class="card-header">
                <h4 class="card-title mb-0">Current Assessment</h4>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td><strong>Questions:</strong></td>
                        <td>{{ $assessment->questions->count() }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Points:</strong></td>
                        <td>{{ $assessment->total_points }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            @if($assessment->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-warning">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
pre {
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}
</style>
@endpush