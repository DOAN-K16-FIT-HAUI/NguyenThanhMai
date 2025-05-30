<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tb_users'; // <<< thêm dòng này


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'created_by',
        'updated_by',
        'status',
        'role'
    ];

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function findOrFail($id)
    {
        // Tìm kiếm bản ghi theo id
        $model = static::find($id);
        // Nếu không tìm thấy, ném ngoại lệ với thông báo tùy chỉnh
        if (!$model) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Record with ID {$id} not found.");
        }
        return $model;
    }


    public function hasRequestedItem($itemId)
    {
        return $this->transactions()->where('item_id', $itemId)->exists();
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function reportsMade()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function reportsReceived()
    {
        return $this->hasMany(Report::class, 'reported_user_id');
    }
}
