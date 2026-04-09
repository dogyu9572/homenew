<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPostSection extends Model
{
    protected $fillable = [
        'blog_post_id',
        'sort_order',
        'subtitle',
    ];

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BlogPostSectionItem::class)->orderBy('sort_order');
    }
}
