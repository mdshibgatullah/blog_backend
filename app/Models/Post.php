<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'image',
        'description',
        'popular',
        'status',
    ];

    // Category Relationship
    public function category()
    {
        return $table = $this->belongsTo(Category::class);
    }

    // Tag Relationship (Many to Many)
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}