<?php

namespace App\View\Components\Partials\Cards;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatsCard extends Component
{
    public $title;
    public $value;
    public $trend;
    public $icon;
    public $color;

    /**
     * Create a new component instance.
     */
    public function __construct($title, $value, $trend = '', $icon = 'fas fa-chart-line', $color = 'indigo')
    {
        $this->title = $title;
        $this->value = $value;
        $this->trend = $trend;
        $this->icon = $icon;
        $this->color = $color;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.partials.cards.stats-card');
    }
}
