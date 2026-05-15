@extends('layouts.app')

@section('title', 'Service Categories')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="page-title mb-1">Service Categories</h4>
                                <p class="text-muted mb-0">Manage service categories for the healthcare system</p>
                            </div>
                            <div>
                                <a href="{{ route('service-categories.create') }}" class="btn btn-primary">
                                    <i class="fe fe-plus me-1"></i> Add Category
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover" id="serviceCategoriesTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Service Types</th>
                                        <th>Service Items</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#serviceCategoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('service-categories.index') }}',
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'service_types_count',
                        name: 'service_types_count',
                        className: 'text-center'
                    },
                    {
                        data: 'service_items_count',
                        name: 'service_items_count',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search categories..."
                }
            });
        });
    </script>
@endpush
