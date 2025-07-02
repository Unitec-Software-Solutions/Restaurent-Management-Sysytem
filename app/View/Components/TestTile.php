<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class TestTile extends Component
{
    public string $label;
    public string $route;
    public string $icon;
    public bool $disabled;
    public bool $available;
    public string $url;
    public string $statusBadge;
    public string $cardClass;
    public string $iconClass;
    public string $textClass;
    public bool $hasSystemIntegrity;

    /**
     * Create a new component instance following UI/UX guidelines.
     */
    public function __construct(
        string $label,
        string $route,
        string $icon = 'fa-link',
        bool $disabled = false
    ) {
        $this->label = $label;
        $this->route = $route;
        $this->icon = $icon;
        $this->disabled = $disabled;
        $this->available = Route::has($route);
        $this->url = $this->available ? route($route) : '#';
        $this->hasSystemIntegrity = $this->validateSystemIntegrity();
        
        // Set UI properties following the universal UI/UX guidelines
        $this->setUIProperties();
    }

    /**
     * Validate system integrity for specific route types
     */
    private function validateSystemIntegrity(): bool
    {
        try {
            // Admin route validation
            if (str_contains($this->route, 'admin')) {
                return $this->validateAdminSystem();
            }
            
            // Inventory route validation
            if (str_contains($this->route, 'inventory')) {
                return $this->validateInventorySystem();
            }
            
            // Order route validation
            if (str_contains($this->route, 'order')) {
                return $this->validateOrderSystem();
            }
            
            // Menu route validation
            if (str_contains($this->route, 'menu')) {
                return $this->validateMenuSystem();
            }
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate admin system integrity with comprehensive checks
     */
    private function validateAdminSystem(): bool
    {
        // Check if Admin model exists
        if (!class_exists(\App\Models\Admin::class)) {
            return false;
        }

        // Check if admins table exists
        if (!Schema::hasTable('admins')) {
            return false;
        }

        // Check for core required columns
        $columns = Schema::getColumnListing('admins');
        $requiredColumns = [
            'id', 'name', 'email', 'password', 
            'organization_id', 'is_super_admin', 'is_active'
        ];
        
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $columns)) {
                return false;
            }
        }

        // Check for role system columns (either legacy or Spatie)
        $hasRoleSystem = in_array('role', $columns) || in_array('current_role_id', $columns);
        if (!$hasRoleSystem) {
            return false;
        }

        // Check if HasRoles trait is properly implemented
        try {
            $adminModel = new \App\Models\Admin();
            if (!method_exists($adminModel, 'assignRole')) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        // Check if roles table exists for Spatie integration
        if (!Schema::hasTable('roles')) {
            return false;
        }

        return true;
    }

    /**
     * Validate inventory system integrity
     */
    private function validateInventorySystem(): bool
    {
        $requiredTables = ['item_masters', 'item_categories'];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate order system integrity
     */
    private function validateOrderSystem(): bool
    {
        $requiredTables = ['orders', 'order_items'];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate menu system integrity
     */
    private function validateMenuSystem(): bool
    {
        $requiredTables = ['menus', 'menu_categories', 'menu_items'];
        
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Set UI properties following the universal UI/UX guidelines
     */
    private function setUIProperties(): void
    {
        // Priority: Disabled > Missing Route > System Error > Available
        if ($this->disabled) {
            $this->statusBadge = 'Disabled';
            $this->cardClass = 'bg-gray-50 border-gray-200 cursor-not-allowed opacity-75';
            $this->iconClass = 'text-gray-400';
            $this->textClass = 'text-gray-500';
        } elseif (!$this->available) {
            $this->statusBadge = 'Missing Route';
            $this->cardClass = 'bg-red-50 border-red-200 cursor-not-allowed';
            $this->iconClass = 'text-red-500';
            $this->textClass = 'text-red-700';
        } elseif (!$this->hasSystemIntegrity) {
            $this->statusBadge = 'System Error';
            $this->cardClass = 'bg-yellow-50 border-yellow-200 cursor-not-allowed';
            $this->iconClass = 'text-yellow-500';
            $this->textClass = 'text-yellow-700';
        } else {
            $this->statusBadge = 'Available';
            $this->cardClass = 'bg-white border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all duration-200';
            $this->iconClass = 'text-indigo-600';
            $this->textClass = 'text-gray-900';
        }
    }

    /**
     * Get status indicator class following UI/UX color palette
     */
    public function getStatusClass(): string
    {
        return match($this->statusBadge) {
            'Available' => 'bg-green-100 text-green-700',
            'Missing Route' => 'bg-red-100 text-red-600',
            'System Error' => 'bg-yellow-100 text-yellow-600',
            'Disabled' => 'bg-gray-100 text-gray-500',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Check if tile is clickable
     */
    public function isClickable(): bool
    {
        return $this->available && !$this->disabled && $this->hasSystemIntegrity;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.test-tile');
    }
}
