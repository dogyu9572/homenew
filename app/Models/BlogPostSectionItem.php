<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostSectionItem extends Model
{
    protected $fillable = [
        'blog_post_section_id',
        'sort_order',
        'subheading',
        'content',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(BlogPostSection::class, 'blog_post_section_id');
    }
}
