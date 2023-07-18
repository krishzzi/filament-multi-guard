<?php

namespace Iotronlab\FilamentMultiGuard\Http\Livewire\Auth;

use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class Verify extends Component implements HasForms
{

    use InteractsWithForms, WithRateLimiting;

}
