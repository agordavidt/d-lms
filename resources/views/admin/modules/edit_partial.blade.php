<div class="form-group">
    <label>Program <span class="text-danger">*</span></label>
    <select class="form-control @error('program_id') is-invalid @enderror" name="program_id" required>
        <option value="">Select Program</option>
        @foreach($programs as $program)
            <option value="{{ $program->id }}" 
                {{ (old('program_id', $module->program_id) == $program->id) ? 'selected' : '' }}>
                {{ $program->name }}
            </option>
        @endforeach
    </select>
    @error('program_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>Module Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" 
           name="title" placeholder="e.g., Module 1: Foundations of Data Analytics" 
           value="{{ old('title', $module->title) }}" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" 
              name="description" rows="3" 
              placeholder="Brief description of what this module covers">{{ old('description', $module->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Duration (Weeks) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('duration_weeks') is-invalid @enderror" 
               name="duration_weeks" min="1" 
               value="{{ old('duration_weeks', $module->duration_weeks) }}" required>
        @error('duration_weeks')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Status <span class="text-danger">*</span></label>
        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
            <option value="draft" {{ old('status', $module->status) == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ old('status', $module->status) == 'published' ? 'selected' : '' }}>Published</option>
            <option value="archived" {{ old('status', $module->status) == 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label>Learning Objectives</label>
    <small class="text-muted d-block mb-2">What will learners achieve in this module?</small>
    <div id="edit-objectives-container">
        @if(old('learning_objectives'))
            @foreach(old('learning_objectives') as $objective)
                @if($objective)
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="learning_objectives[]" 
                           placeholder="e.g., Understand fundamental data concepts"
                           value="{{ $objective }}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger" onclick="removeEditObjective(this)">Remove</button>
                    </div>
                </div>
                @endif
            @endforeach
        @elseif($module->learning_objectives && count($module->learning_objectives) > 0)
            @foreach($module->learning_objectives as $objective)
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="learning_objectives[]" 
                           placeholder="e.g., Understand fundamental data concepts"
                           value="{{ $objective }}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger" onclick="removeEditObjective(this)">Remove</button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="learning_objectives[]" 
                       placeholder="e.g., Understand fundamental data concepts">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeEditObjective(this)">Remove</button>
                </div>
            </div>
        @endif
    </div>
    <button type="button" class="btn btn-sm btn-secondary" onclick="addEditObjective()">
        Add Another Objective
    </button>
</div>

<script>
function addEditObjective() {
    const container = document.getElementById('edit-objectives-container');
    const newObjective = document.createElement('div');
    newObjective.className = 'input-group mb-2';
    newObjective.innerHTML = `
        <input type="text" class="form-control" name="learning_objectives[]" 
               placeholder="Enter learning objective">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="removeEditObjective(this)">Remove</button>
        </div>
    `;
    container.appendChild(newObjective);
}

function removeEditObjective(btn) {
    const container = document.getElementById('edit-objectives-container');
    if (container.children.length > 1) {
        btn.closest('.input-group').remove();
    }
}
</script>