<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'tb_categories';
    protected $guarded = [];
    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
