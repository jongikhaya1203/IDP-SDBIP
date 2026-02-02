<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h4 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Add Priority Change
                        <small class="text-muted">- <?= htmlspecialchars($session['session_name']) ?></small>
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="/lekgotla/session/<?= $session['id'] ?>/store-change" id="changeForm">
                        <!-- Change Type Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Type of Change <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check card h-100">
                                        <div class="card-body">
                                            <input class="form-check-input" type="radio" name="change_type" id="typeNew" value="new" required>
                                            <label class="form-check-label d-block" for="typeNew">
                                                <i class="bi bi-plus-circle text-primary fs-3 d-block mb-2"></i>
                                                <strong>Add New Priority</strong>
                                                <small class="text-muted d-block">Introduce a new IDP priority from Imbizo commitments</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card h-100">
                                        <div class="card-body">
                                            <input class="form-check-input" type="radio" name="change_type" id="typeModify" value="modify">
                                            <label class="form-check-label d-block" for="typeModify">
                                                <i class="bi bi-pencil-square text-warning fs-3 d-block mb-2"></i>
                                                <strong>Modify Existing</strong>
                                                <small class="text-muted d-block">Adjust budget or scope of existing priority</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card h-100">
                                        <div class="card-body">
                                            <input class="form-check-input" type="radio" name="change_type" id="typeDiscard" value="discard">
                                            <label class="form-check-label d-block" for="typeDiscard">
                                                <i class="bi bi-x-circle text-danger fs-3 d-block mb-2"></i>
                                                <strong>Discard Priority</strong>
                                                <small class="text-muted d-block">Remove priority and free up budget</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Priority Fields -->
                        <div id="newPriorityFields" class="change-fields d-none">
                            <h5 class="border-bottom pb-2 mb-3 text-primary">
                                <i class="bi bi-plus-circle me-2"></i>New Priority Details
                            </h5>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Priority Name <span class="text-danger">*</span></label>
                                    <input type="text" name="new_priority_name" class="form-control"
                                           placeholder="e.g., Community Sports Facility Development">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Priority Level <span class="text-danger">*</span></label>
                                    <select name="new_priority_level" class="form-select">
                                        <option value="medium">Medium</option>
                                        <option value="critical">Critical</option>
                                        <option value="high">High</option>
                                        <option value="low">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="new_priority_description" class="form-control" rows="3"
                                          placeholder="Describe the priority and its expected outcomes..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="new_category_id" class="form-select">
                                        <option value="">-- Select Category --</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Responsible Directorate <span class="text-danger">*</span></label>
                                    <select name="new_directorate_id" class="form-select">
                                        <option value="">-- Select Directorate --</option>
                                        <?php foreach ($directorates as $dir): ?>
                                            <option value="<?= $dir['id'] ?>"><?= htmlspecialchars($dir['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Budget Allocation (R)</label>
                                    <input type="number" name="new_budget" class="form-control" min="0" step="1000"
                                           placeholder="e.g., 5000000">
                                </div>
                            </div>
                            <?php if (!empty($imbizoActions)): ?>
                            <div class="mb-3">
                                <label class="form-label">Link to Imbizo Commitment</label>
                                <select name="linked_imbizo_action_id" class="form-select">
                                    <option value="">-- Optional: Link to Imbizo Action --</option>
                                    <?php foreach ($imbizoActions as $action): ?>
                                        <option value="<?= $action['id'] ?>">
                                            <?= htmlspecialchars(substr($action['action_description'], 0, 80)) ?>...
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mayor's Commitment (from Imbizo)</label>
                                <textarea name="imbizo_commitment" class="form-control" rows="2"
                                          placeholder="Quote the specific commitment made by the Mayor..."></textarea>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Existing Priority Selection (for Modify/Discard) -->
                        <div id="existingPriorityFields" class="change-fields d-none">
                            <h5 class="border-bottom pb-2 mb-3 text-warning">
                                <i class="bi bi-list-check me-2"></i>Select Existing Priority
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">Select Priority <span class="text-danger">*</span></label>
                                <select name="priority_id" class="form-select" id="prioritySelect">
                                    <option value="">-- Select Priority --</option>
                                    <?php foreach ($priorities as $p): ?>
                                        <option value="<?= $p['id'] ?>"
                                                data-budget="<?= $p['budget_allocated'] ?>"
                                                data-name="<?= htmlspecialchars($p['priority_name']) ?>">
                                            <?= $p['priority_code'] ?> - <?= htmlspecialchars($p['priority_name']) ?>
                                            (R<?= number_format($p['budget_allocated'], 0) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Modify-specific fields -->
                            <div id="modifyFields" class="d-none">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Budget</label>
                                        <input type="text" id="currentBudget" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Budget (R)</label>
                                        <input type="number" name="new_budget" class="form-control" min="0" step="1000">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Budget Justification</label>
                                    <textarea name="budget_justification" class="form-control" rows="2"
                                              placeholder="Explain why the budget needs to be adjusted..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Common Fields -->
                        <div id="commonFields" class="change-fields d-none">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="bi bi-chat-left-text me-2"></i>Justification
                            </h5>
                            <div class="mb-3">
                                <label class="form-label">Reason for Change <span class="text-danger">*</span></label>
                                <textarea name="change_reason" class="form-control" rows="3" required
                                          placeholder="Explain why this change is being proposed..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Community Impact</label>
                                <textarea name="community_impact" class="form-control" rows="2"
                                          placeholder="Describe the expected impact on the community..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="/lekgotla/session/<?= $session['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <i class="bi bi-check-lg me-1"></i>Submit Change
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changeForm');
    const changeTypes = document.querySelectorAll('input[name="change_type"]');
    const newFields = document.getElementById('newPriorityFields');
    const existingFields = document.getElementById('existingPriorityFields');
    const modifyFields = document.getElementById('modifyFields');
    const commonFields = document.getElementById('commonFields');
    const submitBtn = document.getElementById('submitBtn');
    const prioritySelect = document.getElementById('prioritySelect');
    const currentBudget = document.getElementById('currentBudget');

    changeTypes.forEach(radio => {
        radio.addEventListener('change', function() {
            // Hide all
            newFields.classList.add('d-none');
            existingFields.classList.add('d-none');
            modifyFields.classList.add('d-none');
            commonFields.classList.add('d-none');

            // Show relevant fields
            if (this.value === 'new') {
                newFields.classList.remove('d-none');
            } else {
                existingFields.classList.remove('d-none');
                if (this.value === 'modify') {
                    modifyFields.classList.remove('d-none');
                }
            }
            commonFields.classList.remove('d-none');
            submitBtn.disabled = false;
        });
    });

    prioritySelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            currentBudget.value = 'R' + parseInt(selected.dataset.budget || 0).toLocaleString();
        }
    });
});
</script>
