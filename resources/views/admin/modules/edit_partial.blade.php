<div class="form-group">
    <label>Program <span class="text-danger">*</span></label>
    <select class="form-control" name="program_id" required>
        <option value="">Select Program</option>
        @foreach($programs as $program)
            <option value="{{ $program->id }}" {{ $module->program_id == $program->id ? 'selected' : '' }}>
                {{ $program->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Module Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" name="title" 
           value="{{ $module->title }}" required>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea class="form-control" name="description" rows="3">{{ $module->description }}</textarea>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Duration (Weeks) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" name="duration_weeks" 
               min="1" value="{{ $module->duration_weeks }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Status <span class="text-danger">*</span></label>
        <select class="form-control" name="status" required>
            <option value="draft" {{ $module->status == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ $module->status == 'published' ? 'selected' : '' }}>Published</option>
            <option value="archived" {{ $module->status == 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
    </div>
</div>

<div class="form-group">
    <label>Learning Objectives</label>
    <small class="text-muted d-block mb-2">What will learners achieve in this module?</small>
    <div id="objectives-container-edit">
        @if($module->learning_objectives && count($module->learning_objectives) > 0)
            @foreach($module->learning_objectives as $objective)
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="learning_objectives[]" 
                           value="{{ $objective }}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger" onclick="removeObjectiveEdit(this)">Remove</button>
                    </div>
                </div>
            @endforeach
        @else
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="learning_objectives[]" 
                       placeholder="Enter learning objective">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger" onclick="removeObjectiveEdit(this)">Remove</button>
                </div>
            </div>
        @endif
    </div>
    <button type="button" class="btn btn-sm btn-secondary" onclick="addObjectiveEdit()">
        Add Another Objective
    </button>
</div>

<script>
function addObjectiveEdit() {
    const container = document.getElementById('objectives-container-edit');
    const newObjective = document.createElement('div');
    newObjective.className = 'input-group mb-2';
    newObjective.innerHTML = `
        <input type="text" class="form-control" name="learning_objectives[]" 
               placeholder="Enter learning objective">
        <div class="input-group-append">
            <button type="button" class="btn btn-danger" onclick="removeObjectiveEdit(this)">Remove</button>
        </div>
    `;
    container.appendChild(newObjective);
}

function removeObjectiveEdit(btn) {
    const container = document.getElementById('objectives-container-edit');
    if (container.children.length > 1) {
        btn.closest('.input-group').remove();
    }
}
</script>