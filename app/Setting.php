<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [ //varchar:文字数
                    
        'admin_name',
        'admin_email',
        'admin_forward_email',
        'mail_footer',
        
        'is_product',
        'tax_per',
        
        'is_sale',
        'sale_per',
        'is_point',
        'point_per',
        'cot_per',
        'kare_ensure',
        'btn_color_1',
        'btn_color_2',
        'bank_info',
        
        'rewrite_time',
        'rank_term',
        'rank_term_ueki',
        
        'contents',
        
        'snap_news',
        'snap_top',
        
        'snap_primary',
        'snap_secondary',
        
        'snap_block_a',
        'snap_block_b',
        'snap_block_c',
        
        'post_block',
        
        'snap_category',
        'snap_fix',
        
        'meta_title',
        'meta_description',
        'meta_keyword',
        
        'fix_need',
        'fix_other',
        
        'twitter_id',
        'fb_app_id',
        'instagram_id',
        'analytics_code',

    ];
}
