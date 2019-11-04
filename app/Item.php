<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [ //varchar:文字数
        'open_status',
        'is_secret',
                    
        'number',
        'title',
        'title_addition',
        'catchcopy',
        
        //'is_pot_parent',
        'pot_sort',
        'is_potset',
        'pot_parent_id',
        'pot_count',
        'cate_id',
        'subcate_id',
        
        'is_ensure',
        'item_type',
        
        'main_img',
        'main_caption',
        
        'price',
        'cost_price',
        'sale_price',
        
        'consignor_id',
        'cod',
        'farm_direct',
        
        'dg_id',
        'is_delifee',
        'is_once',
        'once_price',
        'factor',
        'is_once_recom',
        'deli_plan_text',
        
        'stock',
        'stock_show',
        'stock_type',
        'stock_reset_month',
        'stock_reset_count',
            
        'point_back',
        
        'exp_first',
        'explain',
        
        'about_ship',
        'is_delifee_table',
        
        'icon_id',
        
        'contents',
        'caution',
        
        'free_space',
                
        'meta_title',
        'meta_description',
        'meta_keyword',
        
        'upper_title',
        'upper_text',
        
        //'open_date',
        'view_count',
        'sale_count',
        'admin_id',

    ];
    
    
}


