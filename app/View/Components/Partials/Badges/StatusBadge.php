<?php

namespace App\View\Components\Partials\Badges;

use Illuminate\View\Component;

class StatusBadge extends Component
{
    public $status;
    public $showText;

    public function __construct($status = 'success', $showText = true)
    {
        $this->status = $status;
        $this->showText = $showText;
    }

    public function render()
    {
        return view('components.partials.badges.status-badge');
    }
}