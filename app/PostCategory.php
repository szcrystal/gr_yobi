<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
	protected $fillable = [
        'name',
        'link_name',
        'slug',
        
        'is_top',
        'postcate_img_path',
        'postcate_title',
        'postcate_text',
        
        'meta_title',
        'meta_description',
        'meta_keyword',
        
        'contents',
        
        'view_count',
        'sort_num',
    ];

 
}
