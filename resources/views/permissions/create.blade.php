<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="page-title mb-1">Create Permissions</h4>
                    <p class="text-muted mb-0">Add multiple system permissions at once</p>
                </div>
                <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">
                    <i class="ti-arrow-left me-1"></i> Back to Permissions
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="bulkCreateForm" action="{{ route('permissions.bulk.store') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered" id="permissionsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Group <span class="text-danger">*</span></th>
                                        <th>Action <span class="text-danger">*</span></th>
                                        <th>Permission Preview</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="permissionsTableBody">
                                    <tr class="permission-row" data-row="1">
                                        <td><span class="row-number">1</span></td>
                                        <td>
                                            <input type="text" class="form-control" name="permissions[0][group]"
                                                required placeholder="e.g., drugs">
                                        </td>
                                        <td>
                                            <select class="form-select" name="permissions[0][action]" required>
                                                <option value="">Select Action</option>
                                                <option value="view">View</option>
                                                <option value="create">Create</option>
                                                <option value="edit">Edit</option>
                                                <option value="delete">Delete</option>
                                                <option value="approve">Approve</option>
                                                <option value="reject">Reject</option>
                                                <option value="dispense">Dispense</option>
                                                <option value="manage">Manage</option>
                                                <option value="admin">Admin</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="preview-container">
                                                <code class="text-primary permission-preview">group.action</code>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row"
                                                onclick="removeRow(this)">
                                                <i class="ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <button type="button" class="btn btn-outline-primary" onclick="addRow()">
                                    <i class="ti-plus me-1"></i> Add Row
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="clearTable()">
                                    <i class="ti-refresh me-1"></i> Clear All
                                </button>
                            </div>
                            <div>
                                <span class="text-muted me-3">
                                    <strong>Total Rows:</strong> <span id="rowCount">1</span>
                                </span>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti-save me-1"></i> Create All Permissions
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        let rowCount = 1;

        function addRow() {
            rowCount++;
            const tbody = document.getElementById('permissionsTableBody');
            const newRow = document.createElement('tr');
            newRow.className = 'permission-row';
            newRow.setAttribute('data-row', rowCount);

            newRow.innerHTML = `
        <td><span class="row-number">${rowCount}</span></td>
        <td>
            <input type="text" class="form-control" name="permissions[${rowCount - 1}][group]" required placeholder="e.g., drugs">
        </td>
        <td>
            <select class="form-select" name="permissions[${rowCount - 1}][action]" required>
                <option value="">Select Action</option>
                <option value="view">View</option>
                <option value="create">Create</option>
                <option value="edit">Edit</option>
                <option value="delete">Delete</option>
                <option value="approve">Approve</option>
                <option value="reject">Reject</option>
                <option value="dispense">Dispense</option>
                <option value="manage">Manage</option>
                <option value="admin">Admin</option>
            </select>
        </td>
        <td>
            <div class="preview-container">
                <code class="text-primary permission-preview">group.action</code>
            </div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)">
                <i class="ti-trash"></i>
            </button>
        </td>
    `;

            tbody.appendChild(newRow);
            attachEventListeners(newRow);
            updateRowCount();
        }

        function removeRow(button) {
            const row = button.closest('tr');
            const rows = document.querySelectorAll('.permission-row');

            if (rows.length > 1) {
                row.remove();
                updateRowNumbers();
                updateRowCount();
            } else {
                alert('You must have at least one row.');
            }
        }

        function updateRowNumbers() {
            const rows = document.querySelectorAll('.permission-row');
            rows.forEach((row, index) => {
                row.setAttribute('data-row', index + 1);
                row.querySelector('.row-number').textContent = index + 1;

                // Update input names to maintain sequential order
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        const newName = name.replace(/\[\d+\]/, `[${index}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });

            rowCount = rows.length;
        }

        function updateRowCount() {
            document.getElementById('rowCount').textContent = rowCount;
        }

        function updatePreview(row) {
            const group = row.querySelector('input[name$="[group]"]').value || 'group';
            const action = row.querySelector('select[name$="[action]"]').value || 'action';
            row.querySelector('.permission-preview').textContent = group + '.' + action;
        }

        function attachEventListeners(row) {
            const groupInput = row.querySelector('input[name$="[group]"]');
            const actionSelect = row.querySelector('select[name$="[action]"]');

            groupInput.addEventListener('input', () => updatePreview(row));
            actionSelect.addEventListener('change', () => updatePreview(row));
        }

        function clearTable() {
            if (confirm('Are you sure you want to clear all data?')) {
                const tbody = document.getElementById('permissionsTableBody');
                tbody.innerHTML = `
            <tr class="permission-row" data-row="1">
                <td><span class="row-number">1</span></td>
                <td>
                    <input type="text" class="form-control" name="permissions[0][group]" required placeholder="e.g., drugs">
                </td>
                <td>
                    <select class="form-select" name="permissions[0][action]" required>
                        <option value="">Select Action</option>
                        <option value="view">View</option>
                        <option value="create">Create</option>
                        <option value="edit">Edit</option>
                        <option value="delete">Delete</option>
                        <option value="manage">Manage</option>
                        <option value="admin">Admin</option>
                    </select>
                </td>
                <td>
                    <div class="preview-container">
                        <code class="text-primary permission-preview">group.action</code>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)">
                        <i class="ti-trash"></i>
                    </button>
                </td>
            </tr>
        `;
                rowCount = 1;
                updateRowCount();
                attachEventListeners(document.querySelector('.permission-row'));
            }
        }

        // Form submission validation
        document.getElementById('bulkCreateForm').addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.permission-row');
            let hasValidData = false;

            rows.forEach(row => {
                const group = row.querySelector('input[name$="[group]"]').value.trim();
                const action = row.querySelector('select[name$="[action]"]').value;

                if (group && action) {
                    hasValidData = true;
                }
            });

            if (!hasValidData) {
                e.preventDefault();
                alert('Please fill in at least one complete permission row with all required fields.');
                return false;
            }
        });

        // Initialize event listeners on page load
        document.addEventListener('DOMContentLoaded', function() {
            attachEventListeners(document.querySelector('.permission-row'));
            updateRowCount();
        });
    </script>
@endpush
