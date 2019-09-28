<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostRelation extends Model
{
    protected $fillable = [
        'cate_id',
        'type_code',
        'open_status',
        'is_index',
        'thumb_path',
        'big_title',
        
        'item_cate_id',
        'item_subcate_id',
        's_word',
        'item_ids',
        
        'meta_title',
        'meta_description',
        'meta_keyword',
        
        'view_count',
    ];
    
}

