<?php

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Component;

class StatsOverview extends Component
{
    public array $stats = [];
    public $topTravelers;

    public function mount(DashboardService $service): void
    {
        $this->stats        = $service->summary();
        $this->topTravelers = $service->topTravelers();
    }

    public function render()
    {
        return view('livewire.dashboard.stats-overview');
    }
}
