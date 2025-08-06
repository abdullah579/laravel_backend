<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->name === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->name === 'editor';
    }

    public function isAuthor(): bool
    {
        return $this->name === 'author';
    }
}
