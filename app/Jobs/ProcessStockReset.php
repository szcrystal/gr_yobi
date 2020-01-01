<?php

namespace App\Jobs;

use App\Item;
use App\ItemStockChange;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use DateTime;

class ProcessStockReset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = new DateTime('now');
        $nowMonth = $now->format('n');
        
        $items = Item::get();
        
        //Item内の何月入荷の指定月が当月であれば、item内のリセットカウントをセットする
        foreach($items as $item) {
            if($nowMonth == $item->stock_reset_month && $item->pot_parent_id !== 0) { //親ポットは除外する
                
                $item->update(['stock'=>$item->stock_reset_count]);
                
                //子ポットの時、親ポットにstock合計をセットする
                if($item->pot_type == 3) {
                    $potsSum = Item::where(['open_status'=>1, 'pot_type'=>3, 'pot_parent_id'=>$item->pot_parent_id])->sum('stock');
                    Item::find($item->pot_parent_id)->update(['stock'=>$potsSum]);
                    
                    //子ポットの時は親のIDをセットする StockChange DB用
                    $itemScId = $item->pot_parent_id;
                }
                else {
                    // StockChange DB用
                    $itemScId = $item->id;
                }
                
                //$itemScId = $item->is_potset ? $item->pot_parent_id : $item->id;
                                
                //StockChange save
                ItemStockChange::updateOrCreate( //データがなければ各種設定して作成
                	['item_id'=>$itemScId], 
                    ['is_auto'=>1, 'updated_at'=>date('Y-m-d H:i:s', time())]
                ); 
                
            }
        }
    }
    
}

