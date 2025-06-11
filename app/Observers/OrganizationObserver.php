<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\Branch;
use Illuminate\Support\Str;

class OrganizationObserver
{
    public function creating(Organization $organization)
    {
        $organization->activation_key = Str::random(40);
    }

    public function created(Organization $organization)
    {
        $organization->branches()->create([
            'name' => $organization->name . ' Head Office',
            'type' => 'head_office',
            'address' => $organization->address,
            'phone' => $organization->phone,
            'opening_time' => '08:00:00', // Default opening time
            'closing_time' => '22:00:00', // Default closing time
            'total_capacity' => 100, // Default total capacity
            'reservation_fee' => 0.00, // Default reservation fee
            'cancellation_fee' => 0.00, // Default cancellation fee
            'activation_key' => Str::random(40),
            'is_active' => true,
        ]);
    }
}

