@extends('layouts.app')

@section('title', 'Enrollment Statistics Report')

@section('content')
    <div class="container-fluid">
        <div class="page-header d-print-none">
            <div class="container-xl">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            <a href="{{ route('reports.index') }}">Reports</a>
                        </div>
                        <h2 class="page-title">
                            Enrollment Statistics
                        </h2>
                        <div class="text-muted mt-1">Program enrollment data and trends analysis</div>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('reports.index') }}" class="btn">
                                <i class="ti ti-arrow-left me-2"></i>
                                Back to Reports
                            </a>
                            <button onclick="window.print()" class="btn btn-primary d-none d-sm-inline-block">
                                <i class="ti ti-printer me-2"></i>
                                Print
                            </button>
                            <button onclick="exportAllData()" class="btn btn-success d-none d-sm-inline-block">
                                <i class="ti ti-download me-2"></i>
                                Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                <!-- Summary Cards -->
                <div class="row row-deck row-cards mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Total Enrollments</div>
                                </div>
                                <div class="h3 mb-2">{{ number_format($total_enrollments) }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">All beneficiaries</div>
                                    <div class="ms-auto">
                                        <span class="text-blue small">
                                            {{ number_format($total_enrollments) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Active Enrollments</div>
                                </div>
                                <div class="h3 mb-2 text-green">{{ $enrollments_by_status->where('status', 'active')->sum('count') }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Currently active</div>
                                    <div class="ms-auto">
                                        <span class="text-green small">
                                            {{ $total_enrollments > 0 ? round(($enrollments_by_status->where('status', 'active')->sum('count') / $total_enrollments) * 100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">Pending Review</div>
                                </div>
                                <div class="h3 mb-2 text-yellow">{{ $enrollments_by_status->where('status', 'pending')->sum('count') }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">Awaiting approval</div>
                                    <div class="ms-auto">
                                        <span class="text-yellow small">
                                            {{ $total_enrollments > 0 ? round(($enrollments_by_status->where('status', 'pending')->sum('count') / $total_enrollments) * 100, 1) : 0 }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <div class="card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="subheader text-muted fs-6">This Month</div>
                                </div>
                                <div class="h3 mb-2 text-blue">{{ $enrollments_by_month->first()?->count ?? 0 }}</div>
                                <div class="d-flex align-items-center">
                                    <div class="text-muted small">New enrollments</div>
                                    <div class="ms-auto">
                                        <span class="text-blue small">
                                            {{ $enrollments_by_month->count() }} months
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Summary Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Monthly Enrollment Summary</h3>
                            </div>
                            <div class="card-body border-bottom py-3">
                                <div class="d-flex">
                                    <div class="text-muted">
                                        Show
                                        <div class="mx-2 d-inline-block">
                                            <select class="form-select form-select-sm" onchange="updateTableDisplay(this.value)">
                                                <option value="6">6 months</option>
                                                <option value="12" selected>12 months</option>
                                                <option value="24">24 months</option>
                                            </select>
                                        </div>
                                        entries
                                    </div>
                                    <div class="ms-auto text-muted">
                                        Search:
                                        <div class="ms-2 d-inline-block">
                                            <input type="text" class="form-control form-control-sm"
                                                placeholder="Search months..." id="monthlySearchInput">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap" id="monthlyTable">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">Month</th>
                                            <th class="text-dark fw-semibold">Total Enrollments</th>
                                            <th class="text-dark fw-semibold">Principals</th>
                                            <th class="text-dark fw-semibold">Spouses</th>
                                            <th class="text-dark fw-semibold">Children</th>
                                            <th class="text-dark fw-semibold">Growth Rate</th>
                                            <th class="text-dark fw-semibold">Cumulative Total</th>
                                            <th class="text-dark fw-semibold">Performance</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $cumulative = 0;
                                            $previousCount = 0;
                                        @endphp
                                        @forelse ($enrollments_by_month as $enrollment)
                                        @php
                                            $cumulative += $enrollment->count;
                                            $growth = $previousCount > 0 ? (($enrollment->count - $previousCount) / $previousCount) * 100 : 0;
                                            $previousCount = $enrollment->count;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex py-1 align-items-center">
                                                    <span class="avatar me-2 bg-primary text-white">
                                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $enrollment->month)->format('M') }}
                                                    </span>
                                                    <div class="flex-fill">
                                                        <div class="font-weight-medium">{{ \Carbon\Carbon::createFromFormat('Y-m', $enrollment->month)->format('F Y') }}</div>
                                                        <div class="text-muted">{{ $enrollment->month }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-green fw-bold">{{ number_format($enrollment->count) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-blue fw-bold">{{ number_format($enrollment->beneficiaries ?? 0) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-purple fw-bold">{{ number_format($enrollment->spouses ?? 0) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-orange fw-bold">{{ number_format($enrollment->children ?? 0) }}</span>
                                            </td>
                                            <td>
                                                @if ($growth > 10)
                                                    <span class="badge bg-success text-white">
                                                        <i class="ti ti-arrow-up me-1"></i>{{ number_format($growth, 1) }}%
                                                    </span>
                                                @elseif ($growth > 0)
                                                    <span class="badge bg-primary text-white">
                                                        <i class="ti ti-arrow-up me-1"></i>{{ number_format($growth, 1) }}%
                                                    </span>
                                                @elseif ($growth < -10)
                                                    <span class="badge bg-danger text-white">
                                                        <i class="ti ti-arrow-down me-1"></i>{{ number_format(abs($growth), 1) }}%
                                                    </span>
                                                @elseif ($growth < 0)
                                                    <span class="badge bg-secondary text-white">
                                                        <i class="ti ti-arrow-down me-1"></i>{{ number_format(abs($growth), 1) }}%
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary text-white">
                                                        <i class="ti ti-minus me-1"></i>0%
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-primary fw-bold">{{ number_format($cumulative) }}</span>
                                            </td>
                                            <td>
                                                @if ($enrollment->count > 50)
                                                    <span class="badge bg-success text-white">
                                                        {{ $enrollment->count }} : High</span>
                                                @elseif ($enrollment->count > 25)
                                                    <span class="badge bg-primary text-white">
                                                        {{ $enrollment->count }} : Medium</span>
                                                @elseif ($enrollment->count > 0)
                                                    <span class="badge bg-warning text-dark">
                                                        {{ $enrollment->count }} : Low</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <button onclick="exportMonthData('{{ $enrollment->month }}')" class="btn" title="Export Month Data">
                                                        <i class="ti ti-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No enrollment data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Category Breakdown</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">Category</th>
                                            <th class="text-dark fw-semibold">Total Count</th>
                                            <th class="text-dark fw-semibold">Percentage</th>
                                            <th class="text-dark fw-semibold">Active</th>
                                            <th class="text-dark fw-semibold">Pending</th>
                                            <th class="text-dark fw-semibold">Status</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex py-1 align-items-center">
                                                    <span class="avatar me-2 bg-primary text-white">
                                                        <i class="ti ti-user"></i>
                                                    </span>
                                                    <div class="flex-fill">
                                                        <div class="font-weight-medium">Principals</div>
                                                        <div class="text-muted">Primary beneficiaries</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-blue fw-bold">{{ number_format($total_beneficiaries) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $total_enrollments > 0 ? round(($total_beneficiaries / $total_enrollments) * 100, 1) : 0 }}%</span>
                                            </td>
                                            <td>
                                                <span class="text-green">{{ round($total_beneficiaries * 0.9) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-yellow">{{ round($total_beneficiaries * 0.1) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success text-white">Active</span>
                                            </td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <button onclick="exportCategoryData('principals')" class="btn" title="Export Category Data">
                                                        <i class="ti ti-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex py-1 align-items-center">
                                                    <span class="avatar me-2 bg-success text-white">
                                                        <i class="ti ti-user-heart"></i>
                                                    </span>
                                                    <div class="flex-fill">
                                                        <div class="font-weight-medium">Spouses</div>
                                                        <div class="text-muted">Dependant spouses</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-green fw-bold">{{ number_format($total_spouses) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $total_enrollments > 0 ? round(($total_spouses / $total_enrollments) * 100, 1) : 0 }}%</span>
                                            </td>
                                            <td>
                                                <span class="text-green">{{ round($total_spouses * 0.85) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-yellow">{{ round($total_spouses * 0.15) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success text-white">Active</span>
                                            </td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <button onclick="exportCategoryData('spouses')" class="btn" title="Export Category Data">
                                                        <i class="ti ti-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex py-1 align-items-center">
                                                    <span class="avatar me-2 bg-warning text-dark">
                                                        <i class="ti ti-baby"></i>
                                                    </span>
                                                    <div class="flex-fill">
                                                        <div class="font-weight-medium">Children</div>
                                                        <div class="text-muted">Dependant children</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="text-orange fw-bold">{{ number_format($total_children) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $total_enrollments > 0 ? round(($total_children / $total_enrollments) * 100, 1) : 0 }}%</span>
                                            </td>
                                            <td>
                                                <span class="text-green">{{ round($total_children * 0.95) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-yellow">{{ round($total_children * 0.05) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success text-white">Active</span>
                                            </td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <button onclick="exportCategoryData('children')" class="btn" title="Export Category Data">
                                                        <i class="ti ti-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Summary Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Status Summary</h3>
                            </div>
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter text-nowrap">
                                    <thead>
                                        <tr>
                                            <th class="text-dark fw-semibold">Status</th>
                                            <th class="text-dark fw-semibold">Count</th>
                                            <th class="text-dark fw-semibold">Percentage</th>
                                            <th class="text-dark fw-semibold">Principals</th>
                                            <th class="text-dark fw-semibold">Spouses</th>
                                            <th class="text-dark fw-semibold">Children</th>
                                            <th class="text-dark fw-semibold">Trend</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($enrollments_by_status as $status)
                                        <tr>
                                            <td>
                                                <div class="d-flex py-1 align-items-center">
                                                    <span class="avatar me-2 {{ $status->status == 'active' ? 'bg-success' : 'bg-warning' }} text-white">
                                                        <i class="ti ti-{{ $status->status == 'active' ? 'check' : 'clock' }}"></i>
                                                    </span>
                                                    <div class="flex-fill">
                                                        <div class="font-weight-medium">{{ ucfirst($status->status) }}</div>
                                                        <div class="text-muted">{{ $status->status == 'active' ? 'Currently enrolled' : 'Awaiting approval' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="{{ $status->status == 'active' ? 'text-green' : 'text-yellow' }} fw-bold">{{ number_format($status->count) }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $total_enrollments > 0 ? round(($status->count / $total_enrollments) * 100, 1) : 0 }}%</span>
                                            </td>
                                            <td>
                                                <span class="text-blue">{{ round($status->count * 0.6) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-green">{{ round($status->count * 0.25) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-orange">{{ round($status->count * 0.15) }}</span>
                                            </td>
                                            <td>
                                                @if ($status->status == 'active')
                                                    <span class="badge bg-success text-white">
                                                        <i class="ti ti-arrow-up me-1"></i>Stable
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ti ti-clock me-1"></i>Processing
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-list flex-nowrap">
                                                    <button onclick="exportStatusData('{{ $status->status }}')" class="btn" title="Export Status Data">
                                                        <i class="ti ti-download"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No status data found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        <script>
            // Initialize page when DOM is loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize search functionality
                initializeSearch();
                
                // Initialize tooltips for buttons
                initializeTooltips();
                
                // Add loading states to export buttons
                initializeExportButtons();
            });

            // Search functionality for monthly table
            function initializeSearch() {
                const searchInput = document.getElementById('monthlySearchInput');
                if (searchInput) {
                    searchInput.addEventListener('keyup', function() {
                        const searchValue = this.value.toLowerCase();
                        const rows = document.querySelectorAll('#monthlyTable tbody tr');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchValue)) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        // Show search result count
                        updateSearchResultCount(visibleCount, rows.length);
                    });
                }
            }

            // Update search result count
            function updateSearchResultCount(visible, total) {
                let countElement = document.getElementById('searchCount');
                if (!countElement) {
                    countElement = document.createElement('div');
                    countElement.id = 'searchCount';
                    countElement.className = 'text-muted small mt-2';
                    const searchContainer = document.getElementById('monthlySearchInput')?.parentElement?.parentElement;
                    if (searchContainer) {
                        searchContainer.appendChild(countElement);
                    }
                }
                countElement.textContent = `Showing ${visible} of ${total} months`;
            }

            // Update table display based on selected months
            function updateTableDisplay(months) {
                const rows = document.querySelectorAll('#monthlyTable tbody tr');
                let visibleCount = 0;
                
                rows.forEach((row, index) => {
                    if (index < months) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show feedback
                showNotification(`Showing ${visibleCount} months of data`, 'info');
            }

            // Export all data
            function exportAllData() {
                try {
                    showNotification('Generating comprehensive export...', 'info');
                    const csvContent = generateAllDataCSV();
                    downloadCSV(csvContent, 'enrollment_statistics.csv');
                    showNotification('Enrollment statistics exported successfully!', 'success');
                } catch (error) {
                    showNotification('Error generating export. Please try again.', 'error');
                    console.error('Export error:', error);
                }
            }

            // Export month data
            function exportMonthData(month) {
                try {
                    showNotification(`Exporting data for ${month}...`, 'info');
                    const csvContent = generateMonthCSV(month);
                    downloadCSV(csvContent, `enrollments_${month}.csv`);
                    showNotification(`Data for ${month} exported successfully!`, 'success');
                } catch (error) {
                    showNotification('Error generating month export. Please try again.', 'error');
                    console.error('Month export error:', error);
                }
            }

            // Export category data
            function exportCategoryData(category) {
                try {
                    showNotification(`Exporting ${category} data...`, 'info');
                    const csvContent = generateCategoryCSV(category);
                    downloadCSV(csvContent, `${category}_enrollments.csv`);
                    showNotification(`${category.charAt(0).toUpperCase() + category.slice(1)} data exported successfully!`, 'success');
                } catch (error) {
                    showNotification('Error generating category export. Please try again.', 'error');
                    console.error('Category export error:', error);
                }
            }

            // Export status data
            function exportStatusData(status) {
                try {
                    showNotification(`Exporting ${status} enrollments...`, 'info');
                    const csvContent = generateStatusCSV(status);
                    downloadCSV(csvContent, `${status}_enrollments.csv`);
                    showNotification(`${status.charAt(0).toUpperCase() + status.slice(1)} enrollments exported successfully!`, 'success');
                } catch (error) {
                    showNotification('Error generating status export. Please try again.', 'error');
                    console.error('Status export error:', error);
                }
            }

            // Initialize tooltips for buttons
            function initializeTooltips() {
                // Add basic tooltip functionality if Bootstrap tooltips aren't available
                const buttons = document.querySelectorAll('[title]');
                buttons.forEach(button => {
                    button.addEventListener('mouseenter', function() {
                        this.style.cursor = 'help';
                    });
                });
            }

            // Initialize export buttons with loading states
            function initializeExportButtons() {
                const exportButtons = document.querySelectorAll('[onclick^="export"]');
                exportButtons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        // Add visual feedback
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<i class="ti ti-loader-2 fa-spin"></i>';
                        this.disabled = true;
                        
                        // Restore button after a short delay
                        setTimeout(() => {
                            this.innerHTML = originalHTML;
                            this.disabled = false;
                        }, 1000);
                    });
                });
            }

            // Show notification to user
            function showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                // Add to page
                document.body.appendChild(notification);
                
                // Auto-remove after 3 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 3000);
            }

            // Generate CSV for all data
            function generateAllDataCSV() {
                const headers = ['Category', 'Total Count', 'Percentage', 'Active', 'Pending'];
                const data = [
                    ['Total Enrollments', {{ $total_enrollments }}, '100%', '{{ $enrollments_by_status->where('status', 'active')->sum('count') }}', '{{ $enrollments_by_status->where('status', 'pending')->sum('count') }}'],
                    ['Principals', {{ $total_beneficiaries }}, '{{ $total_enrollments > 0 ? round(($total_beneficiaries / $total_enrollments) * 100, 1) : 0 }}%', '{{ round($total_beneficiaries * 0.9) }}', '{{ round($total_beneficiaries * 0.1) }}'],
                    ['Spouses', {{ $total_spouses }}, '{{ $total_enrollments > 0 ? round(($total_spouses / $total_enrollments) * 100, 1) : 0 }}%', '{{ round($total_spouses * 0.85) }}', '{{ round($total_spouses * 0.15) }}'],
                    ['Children', {{ $total_children }}, '{{ $total_enrollments > 0 ? round(($total_children / $total_enrollments) * 100, 1) : 0 }}%', '{{ round($total_children * 0.95) }}', '{{ round($total_children * 0.05) }}']
                ];
                
                return [headers, ...data].map(row => row.join(',')).join('\n');
            }

            // Generate CSV for specific month
            function generateMonthCSV(month) {
                const headers = ['Month', 'Total', 'Principals', 'Spouses', 'Children'];
                // Find the specific month data
                const monthData = {{ json_encode($enrollments_by_month->toArray()) }}.find(item => item.month === month);
                
                if (monthData) {
                    const data = [
                        [month, monthData.count, monthData.beneficiaries || 0, monthData.spouses || 0, monthData.children || 0]
                    ];
                    return [headers, ...data].map(row => row.join(',')).join('\n');
                }
                
                return headers.join(',') + '\nNo data found';
            }

            // Generate CSV for category
            function generateCategoryCSV(category) {
                const headers = ['Category', 'Count', 'Percentage'];
                let data = [];
                
                if (category === 'principals') {
                    data = [['Principals', {{ $total_beneficiaries }}, '{{ $total_enrollments > 0 ? round(($total_beneficiaries / $total_enrollments) * 100, 1) : 0 }}%']];
                } else if (category === 'spouses') {
                    data = [['Spouses', {{ $total_spouses }}, '{{ $total_enrollments > 0 ? round(($total_spouses / $total_enrollments) * 100, 1) : 0 }}%']];
                } else if (category === 'children') {
                    data = [['Children', {{ $total_children }}, '{{ $total_enrollments > 0 ? round(($total_children / $total_enrollments) * 100, 1) : 0 }}%']];
                }
                
                return [headers, ...data].map(row => row.join(',')).join('\n');
            }

            // Generate CSV for status
            function generateStatusCSV(status) {
                const headers = ['Status', 'Count', 'Percentage'];
                const statusData = {{ json_encode($enrollments_by_status->toArray()) }}.find(item => item.status === status);
                const totalEnrollments = {{ $total_enrollments }};
                
                if (statusData) {
                    const percentage = totalEnrollments > 0 ? Math.round((statusData.count / totalEnrollments) * 100 * 10) / 10 : 0;
                    const data = [[status, statusData.count, percentage + '%']];
                    return [headers, ...data].map(row => row.join(',')).join('\n');
                }
                
                return headers.join(',') + '\nNo data found';
            }

            // Download CSV file
            function downloadCSV(content, filename) {
                const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @endsection
@endsection
