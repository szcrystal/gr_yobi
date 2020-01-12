<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataRanking extends Model
{
    protected $fillable = [
        'sale_id',
        'item_id',
        'cate_id',
        'subcate_id',
        'pot_type',
        
        'sale_count',
        'sale_price',
        
        'created_at',
        'updated_at',
    ];
}
