<?php

use Illuminate\Foundation\Inspiring;

use App\Setting;
use App\User;
use App\UserNoregist;

use App\Item;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');


// Set Setting For Local ===============
Artisan::command('setsetting', function () {
    $set = Setting::first();
    
    $set->admin_email = 'red.beryl@zoho.com';
    $set->admin_forward_email = 'opal@emerald.wjg.jp';
    $set->is_product = 0;
    $set->analytics_code = '';
    
    $set->save();
    
    $this->comment('Set Setting done');
});


// Set User Address3 ==================
Artisan::command('useraddr3', function () {
    $users = User::all();
    
    foreach($users as $user) {
        $addr_1 = $user->address_1 . $user->address_2;
        $addr_2 = $user->address_3;
        
        $user->address_1 = $addr_1;
        $user->address_2 = $addr_2;
        $user->address_3 = null;
        $user->save();
    }
    
    $this->comment('User change address3 done');
});

Artisan::command('nouseraddr3', function () {
    $noUsers = UserNoregist::all();
    
    foreach($noUsers as $noUser) {
        $addr_1 = $noUser->address_1 . $noUser->address_2;
        $addr_2 = $noUser->address_3;
        
        $noUser->address_1 = $addr_1;
        $noUser->address_2 = $addr_2;
        $noUser->address_3 = null;
        $noUser->save();
    }
    
    $this->comment('NoUser change address3 done');
});

//親ポット 'pot_parent_id'に0をセットする
Artisan::command('setPotParent', function () {
    $items = Item::all();
    $ar = array();
    
    foreach($items as $item) {
    	$isPotParent = 0; //このitemがpotParentなら、1
    	$isStock = 0; //このpotParentの子供ポットの在庫が全て0なら、0
        $stockNum = 0;
        
    	$pots = Item::where(['open_status'=>1, 'is_potset'=>1, 'pot_parent_id'=>$item->id])->get();
    	
        if($pots->isNotEmpty()) {
            foreach($pots as $pot) {
                if($pot->stock) {
                	$isStock = 1;
                    break;
                    //$stockNum += $pot->stock;
                }
            }
            
            $isPotParent = 1;
        }

		//Set 0 ====================        
        if($isPotParent && ! isset($item->pot_parent_id)) {        	
            $item->update(['pot_parent_id' => 0]);
        	$this->comment($item->id . ': PotParent:0 Set Done !');
        }
        
        //Set Stock ====================        
//        if($isPotParent) {
//            $item->update(['stock'=>$isStock]);
//            $this->comment($item->id . ': Set Stock Done !');
//        }
    }
    
    
    //$this->comment('NoUser change address3 done');
})->describe('Display potParentItem No Input pot_parent_id ');




