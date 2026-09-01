<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions EXCEPT website control permissions
        // Handles both 'Super Admin' (space) and 'superAdmin' (camelCase) role names
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            $websitePrefixes = [
                'website-settings',
                'web_products',
                'coupons',
                'web_orders',
                'web_users',
            ];
            foreach ($websitePrefixes as $prefix) {
                if (str_starts_with($ability, $prefix)) {
                    return null; // Check explicit permissions instead of auto-granting
                }
            }

            if (
                $user->email === 'admin@admin.com' ||
                $user->hasRole(['Super Admin', 'superAdmin', 'superadmin', 'Superadmin', 'Admin', 'admin'])
            ) {
                return true;
            }
            return null;
        });
    }
}
