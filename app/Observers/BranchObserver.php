<?php

namespace App\Observers;

use App\Models\Branch;
use App\Services\BranchAutomationService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BranchObserver
{
    protected $branchAutomationService;

    public function __construct(BranchAutomationService $branchAutomationService)
    {
        $this->branchAutomationService = $branchAutomationService;
    }

    public function creating(Branch $branch)
    {
        if (empty($branch->slug)) {
            $branch->slug = Str::slug($branch->name);
        }
        
        if (empty($branch->activation_key)) {
            $branch->activation_key = Str::random(40);
        }
    }

    public function created(Branch $branch)
    {
        Log::info("BranchObserver::created triggered for branch: {$branch->name}, ID: {$branch->id}, is_head_office: " . ($branch->is_head_office ? 'true' : 'false'));
        
        // Skip automation for head office branches - they are handled by OrganizationAutomationService
        if ($branch->is_head_office) {
            Log::info("Skipping BranchObserver automation for head office branch: {$branch->name}");
            return;
        }
        
        // Use the automation service to handle non-head office branch setup
        $this->branchAutomationService->setupNewBranch($branch);
    }

    /**
     * Handle the Branch "updated" event.
     */
    public function updated(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "restored" event.
     */
    public function restored(Branch $branch): void
    {
        //
    }

    /**
     * Handle the Branch "force deleted" event.
     */
    public function forceDeleted(Branch $branch): void
    {
        //
    }
}
