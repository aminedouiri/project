<?php

namespace App\Models;


use App\Models\Post;
use Spatie\Tags\HasTags;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'title', 'content', 'start_at', 'end_at', 'slug', 'published', 'published_at'
    ];

    protected $translatable = [
        'title', 'content'
    ];
}

