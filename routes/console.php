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


//子ポットの在庫を合計して、親ポットにセットする
Artisan::command('setStockPotParent', function () {
    $items = Item::all();
    $ar = array();
    
    foreach($items as $item) {
        //$isPotParent = 0; //このitemがpotParentなら、1
        //$isStock = 0; //このpotParentの子供ポットの在庫が全て0なら、0
        
        if($item->pot_parent_id === 0) { //親ポット時
            $pots = Item::where(['open_status'=>1, 'is_potset'=>1, 'pot_parent_id'=>$item->id])->get();
            
            if($pots->isNotEmpty()) {
                
                $item->update([
                    'pot_type' => 2,
                    'price' => $pots->min('price'),
                    'sale_price' => $pots->whereNotIn('sale_price', [null, 0])->min('sale_price'), //子ポット-sale_priceが全てnullならnullが返るのでこれでOK
                    'stock' => $pots->sum('stock'),
                ]);
                
                $this->comment('ID' . $item->id . ': PotParent:Stock/Price Set Done !');
            }
        }
        elseif($item->pot_parent_id === null) { //通常時
            $item->update(['pot_type'=>1]);
            $this->comment('ID' . $item->id . ': Normal:Type Set Done !');
        }
        elseif($item->pot_parent_id) { //子ポット時
            $item->update(['pot_type'=>3]);
            $this->comment('ID' . $item->id . ': ChildPot:Type Set Done !');
        }
 
    }
    
    
    //$this->comment('NoUser change address3 done');
})->describe('Display potParentItem Set Stock And Price');




