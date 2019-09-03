<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
    	'salerel_id',
		'order_number',
        
        'item_id', 
        'item_count', 
        
        /*
        'regist',
        'user_id',
        'is_user',
        'receiver_id',
        */
        
        'plan_date',
        'plan_time',
        'is_huzaioki',

        'pay_method',
        'deli_fee',
        'cod_fee',
        'use_point',
        'add_point',
        'seinou_sunday',
        'seinou_huzai',
        
        'single_price',
        'total_price',
        'cost_price',
        'charge_loss',
        'arari',
        
        'deli_company_id',
        'deli_slip_num',
        
        'deli_start_date',
        'deli_schedule_date',
        
        'deli_sended_date',
        'deli_done',
        
        'is_keep',
        'is_cancel',
        'keep_date',
        'cancel_date',
        
//        'thanks_done',
//        'stocknow_done',

        //'pay_done',
        
//        'information',
//        'memo',
//        'craim',
        
        /*
        'destination',
        
        'mail_done',
        
        'pay_trans_code',
        'pay_user_id',
        'pay_order_number',
        'pay_payment_code', //ネットバンク、GMO後払いのみ  
    	'pay_result', //クレカのみ
        'pay_state',
        */

    ];
}
