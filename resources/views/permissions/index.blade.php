<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="page-title mb-1">Permissions Management</h4>
                    <p class="text-muted mb-0">Manage system permissions and access control</p>
                </div>
                <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                    <i class="ti-plus me-1"></i> Add Permission
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Permission Name</th>
                                    <th>Group</th>
                                    <th>Action</th>
                                    <th>Created At</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($permissions->count() > 0)
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td>
                                                <code class="text-primary">{{ $permission->name }}</code>
                                            </td>
                                            <td>
                                                @php
                                                    $parts = explode('.', $permission->name);
                                                    $group = $parts[0] ?? 'general';
                                                @endphp
                                                <span class="badge bg-info">{{ ucfirst($group) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $action = $parts[1] ?? 'unknown';
                                                @endphp
                                                <span class="badge bg-secondary">{{ ucfirst($action) }}</span>
                                            </td>
                                            <td>{{ $permission->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        onclick="copyPermission('{{ $permission->name }}')">
                                                        <i class="ti-clipboard"></i>
                                                    </button>
                                                    <form action="{{ route('permissions.destroy', $permission->id) }}"
                                                        method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Are you sure you want to delete this permission?')">
                                                            <i class="ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="ti-shield" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No permissions found</p>
                                                <a href="{{ route('permissions.create') }}"
                                                    class="btn btn-sm btn-primary">
                                                    Create your first permission
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function copyPermission(permissionName) {
            navigator.clipboard.writeText(permissionName).then(function() {
                // Show temporary success message
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="ti-check text-success"></i>';
                btn.disabled = true;

                setTimeout(function() {
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }, 2000);
            });
        }
    </script>
@endpush
