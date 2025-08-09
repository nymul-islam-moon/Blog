<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model
{

     use HasFactory;

    protected $fillable = ['user_id', 'title', 'content', 'is_published'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
