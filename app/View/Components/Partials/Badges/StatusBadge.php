<?php

namespace App\View\Components\Partials\Badges;

use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $status;
    public string $text;
    public bool $showText;
    public string $color;

    public function __construct(
    string $status = 'default',
    string $text = 'Badge',
    bool $showText = true,
    string $color = ''
)

{
        $this->status = $status;
        $this->text = $text;
        $this->showText = $showText;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.partials.badges.status-badge');
    }
}
