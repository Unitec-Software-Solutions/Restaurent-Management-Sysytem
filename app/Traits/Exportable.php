<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GenericExport;

trait Exportable
{
    /**
     * Export data to Excel with filters
     */
    public function exportToExcel(Request $request, $query, string $filename = null, array $headers = [])
    {
        // Check permission
        if (!Auth::user()->can('export_data')) {
            abort(403, 'Unauthorized to export data');
        }

        // Get the model name for default filename
        $modelName = class_basename($query->getModel());
        $filename = $filename ?: strtolower($modelName) . '_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        // Apply any filters from request
        $query = $this->applyFiltersToQuery($query, $request);

        // Get data
        $data = $query->get();

        // Generate export
        return Excel::download(new GenericExport($data, $headers), $filename);
    }

    /**
     * Apply filters to query based on request parameters
     */
    protected function applyFiltersToQuery($query, Request $request)
    {
        // Common filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                // Get searchable columns from model if defined
                $searchableColumns = $this->getSearchableColumns();
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$searchTerm}%");
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Organization scoping for non-super admins
        $user = Auth::user();
        if (!($user->is_super_admin ?? false) && $user->organization_id) {
            if (method_exists($query->getModel(), 'scopeForOrganization')) {
                $query->forOrganization($user->organization_id);
            } elseif (in_array('organization_id', $query->getModel()->getFillable())) {
                $query->where('organization_id', $user->organization_id);
            }
        }

        return $query;
    }

    /**
     * Get searchable columns for the model
     */
    protected function getSearchableColumns(): array
    {
        // Default searchable columns
        $columns = ['name'];

        // Check if model has defined searchable columns
        if (method_exists($this, 'getModelSearchableColumns')) {
            $columns = $this->getModelSearchableColumns();
        } elseif (property_exists($this, 'searchableColumns')) {
            $columns = $this->searchableColumns;
        }

        return $columns;
    }
}
