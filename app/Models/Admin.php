<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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

    public function shops()
    {
        return $this->belongsToMany(User::class, 'shop_admins', 'admin_id', 'shop_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'admin_roles');
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()->with('permissions')->get()->contains(function ($role) use ($permissionName) {
            return $role->permissions->contains('name', $permissionName);
        });
    }

    public function canAccessShop(int $shopId): bool
    {
        if ($this->relationLoaded('shops')) {
            return $this->getRelation('shops')->contains('id', $shopId);
        }

        if (isset($this->shops) && $this->shops instanceof \Illuminate\Support\Collection) {
            return $this->shops->contains('id', $shopId);
        }

        return $this->shops()->where('users.id', $shopId)->exists();
    }
}
