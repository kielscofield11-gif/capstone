<?php

namespace App\Providers;

use App\Models\Blotter;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\DocumentTemplate;
use App\Models\Household;
use App\Models\Resident;
use App\Models\User;
use App\Policies\BlotterPolicy;
use App\Policies\DocumentRequestPolicy;
use App\Policies\DocumentTypePolicy;
use App\Policies\DocumentTemplatePolicy;
use App\Policies\HouseholdPolicy;
use App\Policies\ResidentPolicy;
use App\Policies\UserPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Gate::policy(Resident::class, ResidentPolicy::class);
        Gate::policy(Household::class, HouseholdPolicy::class);
        Gate::policy(Blotter::class, BlotterPolicy::class);
        Gate::policy(DocumentRequest::class, DocumentRequestPolicy::class);
        Gate::policy(DocumentType::class, DocumentTypePolicy::class);
        Gate::policy(DocumentTemplate::class, DocumentTemplatePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::define('view-reports', fn (User $user) => $user->hasAnyRole(User::ROLES));
        Gate::define('view-audit-logs', fn (User $user) => $user->isAdmin());
    }
}
