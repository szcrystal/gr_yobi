<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopSetting extends Model
{
    protected $fillable = [ //varchar:文字数
        'contents',
        'search_words',
        
        'meta_title',
        'meta_description',
        'meta_keyword',
        
        'post_meta_title',
        'post_meta_description',
        'post_meta_keyword',

    ];
}
