<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\Cabinet;
use App\Policies\CabinetPolicy;
use App\Models\Patient;
use App\Policies\PatientPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Cabinet::class => CabinetPolicy::class,
        Patient::class => PatientPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
