<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class CrmExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Ticket::with(['category', 'assignedUser', 'facility', 'createdBy']);

        // Apply filters
        if (!empty($this->filters['date_range'])) {
            switch ($this->filters['date_range']) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'quarter':
                    $query->whereBetween('created_at', [now()->startOfQuarter(), now()->endOfQuarter()]);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['category'])) {
            $query->where('ticket_category_id', $this->filters['category']);
        }

        if (!empty($this->filters['priority'])) {
            $query->where('priority', $this->filters['priority']);
        }

        if (!empty($this->filters['department'])) {
            $query->where('department', $this->filters['department']);
        }

        // Validate department is one of the allowed values
        $allowedDepartments = ['ES Office', 'Finance', 'ICT', 'Admin', 'Programmes', 'PRS', 'SQA'];
        if (!empty($this->filters['department']) && !in_array($this->filters['department'], $allowedDepartments)) {
            // If invalid department, don't apply the filter
            unset($this->filters['department']);
        }

        if (!empty($this->filters['assigned_date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['assigned_date_from']);
        }

        if (!empty($this->filters['assigned_date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['assigned_date_to']);
        }

        if (!empty($this->filters['resolved_date_from'])) {
            $query->whereDate('resolved_at', '>=', $this->filters['resolved_date_from']);
        }

        if (!empty($this->filters['resolved_date_to'])) {
            $query->whereDate('resolved_at', '<=', $this->filters['resolved_date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function map($ticket): array
    {
        return [
            $ticket->ticket_id,
            $ticket->boschma_no ?: 'N/A',
            $ticket->name,
            $ticket->email ?: 'N/A',
            $ticket->phone ?: 'N/A',
            $ticket->beneficiary_type ?: 'N/A',
            $ticket->category->name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $ticket->status)),
            ucfirst($ticket->priority),
            $ticket->department,
            $ticket->complaint ? strip_tags(html_entity_decode($ticket->complaint)) : 'N/A',
            $ticket->description ?: 'N/A',
            $ticket->assignedUser->fullname ?? 'Unassigned',
            $ticket->createdBy->fullname ?? 'N/A',
            $ticket->facility->name ?? 'N/A',
            $ticket->sla_hours ?? 'N/A',
            $ticket->created_at->format('Y-m-d H:i:s'),
            $ticket->resolved_at?->format('Y-m-d H:i:s') ?? 'N/A',
            $ticket->due_date->format('Y-m-d H:i:s'),
            $ticket->resolved_at ? 
                $ticket->created_at->diffInHours($ticket->resolved_at) . 'h' : 'N/A',
            $ticket->resolved_at ? 
                $ticket->created_at->diffInDays($ticket->resolved_at) . ' days' : 'N/A',
            $ticket->status === 'completed' ? 'Yes' : 'No',
            $ticket->due_date->isPast() && $ticket->status !== 'completed' ? 'Overdue' : 'On Time',
        ];
    }

    public function headings(): array
    {
        return [
            'Ticket ID',
            'Boschma No',
            'Customer Name',
            'Email',
            'Phone',
            'Beneficiary Type',
            'Complaint Category',
            'Status',
            'Priority',
            'Department',
            'Complaint Subject',
            'Description',
            'Assigned To',
            'Created By',
            'Facility',
            'SLA Hours',
            'Assigned Date',
            'Resolved Date',
            'Due Date',
            'Resolution Time (Hours)',
            'Resolution Time (Days)',
            'Is Resolved',
            'SLA Status'
        ];
    }

    public function title(): string
    {
        $title = 'Customer Care Report';
        
        if (!empty($this->filters)) {
            $filterParts = [];
            
            if (!empty($this->filters['date_range'])) {
                $filterParts[] = ucfirst($this->filters['date_range']);
            }
            
            if (!empty($this->filters['status'])) {
                $filterParts[] = ucfirst(str_replace('_', ' ', $this->filters['status']));
            }
            
            if (!empty($this->filters['department'])) {
                $filterParts[] = $this->filters['department'];
            }
            
            if (!empty($this->filters['category'])) {
                $category = \App\Models\TicketCategory::find($this->filters['category']);
                if ($category) {
                    $filterParts[] = $category->name;
                }
            }
            
            if (!empty($filterParts)) {
                $title .= ' - ' . implode(' - ', $filterParts);
            }
        }
        
        return $title;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A1:V1' => [
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DC3545']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]
            ],
        ];
    }
}
