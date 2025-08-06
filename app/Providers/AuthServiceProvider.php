<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Article::class => ArticlePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Register policies
        $this->registerPolicies();

        // Define Gates
        $this->defineGates();
    }

    /**
     * Define authorization gates.
     */
    private function defineGates(): void
    {
        // Admin-only gates
        Gate::define('view-all-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('assign-roles', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('delete-article', function (User $user) {
            return $user->isAdmin();
        });

        // Content creation gates
        Gate::define('create-article', function (User $user) {
            return $user->hasAnyRole(['author', 'editor', 'admin']);
        });

        // Article editing gates
        Gate::define('edit-own-article', function (User $user, Article $article) {
            return $user->canEditArticle($article);
        });

        // Publishing gates
        Gate::define('publish-article', function (User $user) {
            return $user->canPublishArticles();
        });

        // Viewing gates
        Gate::define('view-published', function (User $user) {
            // All authenticated users can view published articles
            return true;
        });

        // Additional useful gates
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('manage-all-articles', function (User $user) {
            return $user->canManageAllArticles();
        });

        // Role-specific gates
        Gate::define('is-admin', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('is-editor', function (User $user) {
            return $user->isEditor();
        });

        Gate::define('is-author', function (User $user) {
            return $user->isAuthor();
        });
    }
}
