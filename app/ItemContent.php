<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemContent extends Model
{
    protected $fillable = [
        'item_id',
        
        'exp_first',
        'explain',
        'about_ship',
        'contents',
        'caution',
        'free_space',

        'meta_title',
        'meta_description',
        'meta_keyword',
        
        'upper_title',
        'upper_text',
    ];
}

