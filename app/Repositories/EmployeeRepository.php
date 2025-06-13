<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\Branch;
use App\Models\User;

class EmployeeRepository
{
    /**
     * Find existing employee by email or create new one
     */
    public function findOrCreateForUser(object $user, int $branchId): Employee
    {
        $organizationId = $user->organization_id;
        $employee = Employee::where('email', $user->email)
            ->where('organization_id', $organizationId)
            ->first();

        if (!$employee) {
            // Verify branch belongs to the organization
            $branch = Branch::where('id', $branchId)
                ->where('organization_id', $organizationId)
                ->firstOrFail();

            $employee = Employee::create([
                'emp_id' => $this->generateEmployeeId($organizationId),
                'name' => $user->name ?? 'Auto Employee',
                'email' => $user->email,
                'phone' => $user->phone_number ?? 'N/A',
                'role' => 'manager',
                'branch_id' => $branchId,
                'organization_id' => $organizationId,
                'is_active' => true,
                'joined_date' => now(),
                'address' => '',
                'emergency_contact' => '',
            ]);
        }

        return $employee;
    }

    /**
     * Generate unique employee ID
     */
    private function generateEmployeeId(int $organizationId): string
    {
        $prefix = 'EMP-' . date('Y') . '-';
        $lastEmployee = Employee::where('organization_id', $organizationId)
            ->where('emp_id', 'LIKE', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($lastEmployee) {
            $lastNumber = (int) substr($lastEmployee->emp_id, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Find employee by email
     */
    public function findByEmail(string $email, int $organizationId): ?Employee
    {
        return Employee::where('email', $email)
            ->where('organization_id', $organizationId)
            ->first();
    }
}
