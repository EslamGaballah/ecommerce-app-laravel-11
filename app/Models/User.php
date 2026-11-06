<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'secondary_phone',
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

    protected static function booted(): void
    {
        static::deleted(function (User $user) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
        });
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

   public function scopeFilter(Builder $builder, $filters)
{
    if (!empty($filters['name'])) {
        $builder->where('name', 'LIKE', "%{$filters['name']}%");
    }

    if (!empty($filters['user_type'])) {
        if ($filters['user_type'] === 'admins') {
            $builder->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'user');
            });
        }

        if ($filters['user_type'] === 'regular') {
            $builder->whereHas('roles', function ($query) {
                $query->where('name', 'user');
            });
        }
    }
}


    public function roles() {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission($permission) 
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })->exists();
    }

        /** Every product card asks this, so each request reads the list once. */
    public function favoriteProductIds(): array
    {
        return once(fn () => $this->favorites()->pluck('products.id')->all());
    }

    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'favorites');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function avatar() 
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'avatar');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
