<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view articles list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Article $article): bool
    {
        // All authenticated users can view published articles
        // Authors can view their own drafts
        // Editors and admins can view all articles
        if ($article->isPublished()) {
            return true;
        }

        // For draft articles
        return $user->canManageAllArticles() || $article->author_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Authors, editors, and admins can create articles
        return $user->hasAnyRole(['author', 'editor', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Article $article): bool
    {
        // Use the existing canEditArticle method from User model
        return $user->canEditArticle($article);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Article $article): bool
    {
        // Only admins can delete articles
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, Article $article): bool
    {
        // Only editors and admins can publish articles
        return $user->canPublishArticles();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Article $article): bool
    {
        // Only admins can restore deleted articles
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Article $article): bool
    {
        // Only admins can permanently delete articles
        return $user->isAdmin();
    }
}
