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
    ];
    
}

