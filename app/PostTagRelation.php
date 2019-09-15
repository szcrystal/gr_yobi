<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostTagRelation extends Model
{
	protected $fillable = [
    	'postrel_id',
        'tag_id',
        'sort_num',
    ];
}

