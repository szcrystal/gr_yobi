<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'rel_id',
        'block',
        'img_path',
        'url',
        'title',
        'detail',            
        'sort_num',
        'is_section',
        'is_intro',
        'mid_title_id',
    ];
}

