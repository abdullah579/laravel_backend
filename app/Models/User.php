<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * The articles authored by the user.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Check if user has any of the specified roles.
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->exists();
    }

    /**
     * Check if user has all of the specified roles.
     */
    public function hasAllRoles(array $roleNames): bool
    {
        return $this->roles()->whereIn('name', $roleNames)->count() === count($roleNames);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return false;
        }

        if (!$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }

        return true;
    }

    /**
     * Assign multiple roles to the user.
     */
    public function assignRoles(array $roleNames): bool
    {
        $roles = Role::whereIn('name', $roleNames)->get();

        if ($roles->count() !== count($roleNames)) {
            return false;
        }

        $roleIds = $roles->pluck('id')->toArray();
        $existingRoleIds = $this->roles()->pluck('roles.id')->toArray();
        $newRoleIds = array_diff($roleIds, $existingRoleIds);

        if (!empty($newRoleIds)) {
            $this->roles()->attach($newRoleIds);
        }

        return true;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(string $roleName): bool
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return false;
        }

        $this->roles()->detach($role->id);
        return true;
    }

    /**
     * Remove multiple roles from the user.
     */
    public function removeRoles(array $roleNames): bool
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $roleIds = $roles->pluck('id')->toArray();

        $this->roles()->detach($roleIds);
        return true;
    }

    /**
     * Sync user roles (remove all existing and assign new ones).
     */
    public function syncRoles(array $roleNames): bool
    {
        $roles = Role::whereIn('name', $roleNames)->get();

        if ($roles->count() !== count($roleNames)) {
            return false;
        }

        $roleIds = $roles->pluck('id')->toArray();
        $this->roles()->sync($roleIds);

        return true;
    }

    /**
     * Remove all roles from the user.
     */
    public function removeAllRoles(): void
    {
        $this->roles()->detach();
    }

    /**
     * Get role names as array.
     */
    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is editor.
     */
    public function isEditor(): bool
    {
        return $this->hasRole('editor');
    }

    /**
     * Check if user is author.
     */
    public function isAuthor(): bool
    {
        return $this->hasRole('author');
    }

    /**
     * Check if user can manage users (admin only).
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage all articles (admin or editor).
     */
    public function canManageAllArticles(): bool
    {
        return $this->hasAnyRole(['admin', 'editor']);
    }

    /**
     * Check if user can publish articles (admin or editor).
     */
    public function canPublishArticles(): bool
    {
        return $this->hasAnyRole(['admin', 'editor']);
    }

    /**
     * Check if user can edit a specific article.
     */
    public function canEditArticle(Article $article): bool
    {
        // Admin and editor can edit any article
        if ($this->canManageAllArticles()) {
            return true;
        }

        // Author can only edit their own articles
        return $this->isAuthor() && $article->author_id === $this->id;
    }
}
