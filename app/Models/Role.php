<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * The users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Check if this is an admin role.
     */
    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    /**
     * Check if this is an editor role.
     */
    public function isEditor(): bool
    {
        return $this->name === 'editor';
    }

    /**
     * Check if this is an author role.
     */
    public function isAuthor(): bool
    {
        return $this->name === 'author';
    }
}
