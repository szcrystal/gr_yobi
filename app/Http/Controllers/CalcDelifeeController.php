<?php

namespace App\Http\Controllers;

use App\Item;
use App\DeliveryGroup;
use App\DeliveryGroupRelation;
use App\Prefecture;
use App\Setting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Ctm;
    
class CalcDelifeeController extends Controller
{
	//府中ガーデン-下草小のid（商品個数x係数の合計に応じて容量と送料区分が決まる）
    private $sitakusaSmId = 1;
    //府中ガーデン-下草大のid
    private $sitakusaBgId = 2;
    
    //高木コニファー(千代田プランツ)： ->高木が含まれていれば高木として計算（府中-下草と同じ計算方法　商品個数x係数の合計に応じて容量と送料区分が決まる）
    //千代田プランツ-高木コニファー小のid
    private $koubokuSmId = 3;
    //千代田プランツ-高木コニファー大のid
    private $koubokuBgId = 4;
    //千代田プランツ-下草コニファー ->下草のみなら大小関係しない通常計算 ->変更大小ありの行き来するに変更 下草コニファー（大）となる
    private $sitakoniBgId = 5;
    //千代田プランツ-下草コニファー（小） ->2019/04追加->変更大小ありの行き来するに変更
    private $sitakoniSmId = 19;
    
    
    //シモツケ(千代田プランツ)  -> 下草と同じ計算方法
    //配送区分：シモツケ小のid
    private $simotukeSmId = 6;
    //配送区分：シモツケ大のid
    private $simotukeBgId = 7;
    

    //モリヤコニファー：大の商品が含まれていれば強制的に大となり、なければ小になる 
    //配送区分：モリヤコニファー下草のid
    private $coniferKsId = 8;
    //配送区分：モリヤコニファー小のid
    private $coniferSmId = 9;
    //配送区分：モリヤコニファー大のid
    private $coniferBgId = 10;
    
    
    public function __construct($itemData, $prefId)
    {
    	/***********************
         $itemDataはitemのobjectに[count]（購入個数）を足したObjectを一つずつ配列にしたもの
        ************************/
        
        $this->item = new Item;
        $this->dg = new DeliveryGroup;
        $this->dgRel = new DeliveryGroupRelation;
        $this->prefecture = new Prefecture;
        
        $this->setting = new Setting;
        
        $this->itemData = $itemData;
        $this->prefId = $prefId;
                
    }
    
    
    public function index()
    {
    	$dgId = 0;
    	foreach($this->itemData as $item) {
        	$dgId = $item->id;
            break;
        }
        
    	$dg = $this->dg->find($dgId);
        $dgRel = $this->dgRel->where(['dg_id'=>$dg->id, 'pref_id'=>$this->prefId])->get()->first();
        
        
        echo $dgRel->fee;
        //echo $this->dgId;
        exit;
    }
    
    public function checkIsDelivery()
    {
    	$errorArr = array();
        $prefId = $this->prefId;
                
        foreach($this->itemData as $keys => $item) {
            
            $prefFee = $this->dgRel->where(['dg_id'=>$item->dg_id, 'pref_id'=>$prefId])->first()->fee;
            //getTax get tax ============
            $prefFee = Ctm::getPriceWithTax($prefFee);
    		//getTax get tax END ========
            
            if($prefFee == '99999' || $prefFee === null) {            	
                $title = $item->title;
                $prefName = $this->prefecture->find($prefId)->name;
                
                $errorArr['no_delivery.'. $keys][] = '「'. $title .'」['.  $item->number .'] の「'. $prefName .'」への配送は不可です。';
            }
        }
        
        return $errorArr;
/*        
//        if(count($errorArr) > 0) { //配送不可ならリダイレクト
//            return redirect($toRedirect)->withErrors($errorArr)->withInput();
//        }
//        else {
//        	return;
//        }
*/
    }
    
    

    /* 送料　通常の計算の関数 **************************************************************** */
    public function normalCalc($dgId, $factor) //factor->事前に個数の倍数であること
    {
    	$deliveryFee = 0;
        
        $prefId = $this->prefId;
    	$dg = $this->dg->find($dgId);
                
        $capacity = $dg->capacity;
        $answer = $factor / $capacity;
        $amari = $factor % $capacity;
        
        //decimal = 小数点以下の数値
        $isDecimalFactor = $factor - floor($factor);
        $isDecimalAnswer = $answer - floor($answer); //小数点以下の数値があるかどうか。 切り捨てした数値を引いて例えば0.3など残ればtrueとなる
        
        $fee = $this->dgRel->where(['dg_id'=>$dgId, 'pref_id'=>$prefId])->first()->fee;
        //getTax get tax ============
        $fee = Ctm::getPriceWithTax($fee);
        //getTax get tax END ========
    
        if($amari > 0) { //割り切れない時
            if($answer <= 1) {
                $deliveryFee += $fee;
            }
            else {
                $answer = ceil($answer); //切り上げ
                $deliveryFee += $fee * $answer;
            }
        }
        else { //割り切れる時
            if($isDecimalAnswer) { //割り切れる時($amariが0の時)で、なおかつ小数点の余がある時。12.3 / 6 の時amariは0だが、0.3の端数が出る
                $deliveryFee += $fee * ceil($answer); //切り上げ
            }
            else {
                $deliveryFee += $fee * $answer;
            }
        }
        
        return $deliveryFee;
    }
    
    /* 下草・シモツケ・高木コニファー　小の容量を超えれば大の送料になる特別計算の関数 ************************************************** */ 
    public function specialCalc($smId, $bgId, $factor)
    {
        $deliveryFee = 0;
        $prefId = $this->prefId;
        
        //下草小の容量: 20
        $smCapa = $this->dg->find($smId)->capacity;
        //下草大の容量: 40
        $bgCapa = $this->dg->find($bgId)->capacity;
        
        //下草小と大のそれぞれの送料
        $smFee = $this->dgRel->where(['dg_id'=>$smId, 'pref_id'=>$prefId])->first()->fee;
        $bgFee = $this->dgRel->where(['dg_id'=>$bgId, 'pref_id'=>$prefId])->first()->fee;
        
        //getTax get tax ============
        $smFee = Ctm::getPriceWithTax($smFee);
        $bgFee = Ctm::getPriceWithTax($bgFee);
        //getTax get tax END ========
        
        //$factor = 27.9;
    
        if($factor <= $smCapa) { //個数x係数が20以下なら下草小
//                $answer = $factor / $sitakusaSmCapa;
//                $fee = $this->dgRel->where(['dg_id'=>$sitakusaSmId, 'pref_id'=>$prefId])->first()->fee;
            $deliveryFee += $smFee;        
        }
        else {  //個数x係数が20以上なら容量で割る各種計算が必要
        	//$factor = 60;
            $amari = $factor % $bgCapa;
            $answer = $factor / $bgCapa; //解の型は「double / intの除算」により、doubleになるので注意（int同士の除算なら解はint）。$factorがdouble型なので（DB上double型 ->小数点入力値あり）
            
            //decimal = 小数点以下の数値
            $isDecimalFactor = $factor - floor($factor);
            $isDecimalAnswer = $answer - floor($answer); //小数点以下の数値があるかどうか。 切り捨てした数値を引いて例えば0.3など残ればtrueとなる
//          echo ($isPointNum ? 1 : 0) . '::';

            //amariについて
            //0.3 % 6 = 0
            //1.3 % 6 = 1
            //5.3 % 6 = 5
            //6.3 % 6 = 0
            //7.3 % 6 = 1
            //12.3 % 6 = 0
            //13.3 % 6 = 1
            
//            echo $amari . '/' . $answer. '/'. 27.9 % 6 . '/'. is_float($answer). '/' . gettype($factor). '/' . gettype($bgCapa);
//            exit;
            
            if($amari > 0) { //amariがある時 0以上の時

                if($answer <= 1) {
                    $deliveryFee += $bgFee;
                }
                else {
                    if($amari <= $smCapa) { //40で割ったamariが下草小で可能の時 下草小のcapacity以下の時 合計係数が95なら40で割ると余は15となり下草小で可能
                    	if($amari == $smCapa && $isDecimalFactor) { //factor:27.9 / 容量6の時など 27.9 / 6 小数点分でsmCapacityの容量を超えるので -> 条件はfactorに小数点以下の数値があるかどうかにしている ->実際にどのような時に当てはまるか不明（もしかすると不要かも）
                        	$deliveryFee += $bgFee * ceil($answer); //切り上げ
                        }
                        else {
                        	$deliveryFee += $smFee;
                        	$deliveryFee += $bgFee * floor($answer); //切り捨て
                        }
                    }
                    else {
                        $deliveryFee += $bgFee * ceil($answer); //切り上げ
                    }
                }
            }
            else { //amari 0 割り切れる時
                            
                if($isDecimalAnswer) { //割り切れる時で、なおかつ小数点の余がある時。12.3 / 6 の時amariは0だが、0.3の端数が出る
                	$deliveryFee += $smFee;
                    $deliveryFee += $bgFee * floor($answer); //切り捨て
                }
                else { 
                    if($answer <= 1) {   
                        $deliveryFee += $bgFee;
                    }
                    else {
                        $deliveryFee += $bgFee * $answer; //割り切れるので切り上げ切り捨てなし
                    }
                }
            }
        }
        
        return $deliveryFee;
    }
    /* 下草　特別計算の関数 END ************************************************** */
    
    
    
    /* ここから各送料区分ごとの送料関数 ******************************************** */
    
    //府中ガーデン下草（大・小）========================================================
    public function HutyuGardeSitakusa($sitakusaItem)
    {
        //係数x個数の合計に対して容量を決める特別計算
    	//大と小を行ったり来たりする。余る係数が小に収まれば小、小を超えると大
        //特殊計算：あり。
    	
        $countSm = 0;
        $countBg = 0;
        
        $factor = 0;
        $factorFreeSm = 0;
        $factorFreeBg = 0;
        
        $freeCapaSm = 0;
        $freeCapaBg = 0;
        
        $sitakusaBgId = $this->sitakusaBgId;
        $sitakusaSmId = $this->sitakusaSmId;
        
        $deliFee = 0;
           

        //下草商品の係数の合計を算出 送料無料のものと有料のものを分ける
        foreach($sitakusaItem as $ioi) { 
            if($ioi->is_delifee) { //送料無料のもの この場合、余る容量を計算するための準備をここでする
                if($ioi->dg_id == $sitakusaSmId) {
                    $factorFreeSm += $ioi->factor * $ioi->count;
                    $countSm++;
                }
                else {
                    $factorFreeBg += $ioi->factor * $ioi->count;
                    $countBg++;
                }
            }
            else { //送料有料のもの
                $factor += $ioi->factor * $ioi->count;
            } 
        }
        
        if($countSm > 0) {
            $sitakusaSmCapa = $this->dg->find($sitakusaSmId)->capacity;
            $sitakusaSmCapa = $sitakusaSmCapa * $countSm;
            $freeCapaSm = $sitakusaSmCapa - $factorFreeSm;
        }
        
        if($countBg > 0) {
            $sitakusaBgCapa = $this->dg->find($sitakusaBgId)->capacity;
            $sitakusaBgCapa = $sitakusaBgCapa * $countBg;
            $freeCapaBg = $sitakusaBgCapa - $factorFreeBg;
        }
        
        //最終のFactorを算出 最終Factorが0以上ならその分が送料となるので最終計算をさせる。0以下なら全て無料容量に収まっていることになるので計算不要（送料0）となる。
        $factor = $factor - ($freeCapaSm + $freeCapaBg);
        
//            echo $factorFreeSm . '/' . $countSm. '/' . $factorFreeBg . '/' . $countBg . '/' . $factor . '/' . $freeCapaSm . '/' . $freeCapaBg;
//            exit;
        
        if($factor > 0) { //係数が0以下なら全て無料容量に収まっていることになるので計算不要（送料0）となる。
            $deliFee = $this->specialCalc($sitakusaSmId, $sitakusaBgId, $factor); //特別関数で計算
        }
        
        return $deliFee;
    }
    
    
    //千代田プランツ(高木-小／大・下草) =====================================================
    public function TiyodaPrantsConifer($tiyodaItem)
    {
    	//高木が一つでもあれば高木の大小として計算。計算方法は府中ガーデン下草と同じ
        //高木がない場合（下草のみ）は大小関係がない通常の計算
        //特殊計算：なし。
        
        $factor = 0;
        $switch = 0; //高木コニファーがない時False 高木コニファーがある時True
        
        $factorKouboku = 0;
        $factorTeiboku = 0;
        
        $koubokuBgId = $this-> koubokuBgId;
        $koubokuSmId = $this-> koubokuSmId;
        
        $sitakoniBgId = $this-> sitakoniBgId;
        $sitakoniSmId = $this-> sitakoniSmId;
        
        $deliFee = 0;
        
        foreach($tiyodaItem as $itemObject) {
        	//高木があれば強制的に全て高木の計算になるのでその判定用のSwitchを作る
            if($koubokuSmId == $itemObject->dg_id || $koubokuBgId == $itemObject->dg_id) {
                $switch = 1;
                break;
            }
        }
        
        foreach($tiyodaItem as $itemObject) {
        	//係数の合計を算出
            /*★★★ 
            高木コニファーがある場合は余る容量を算出する必要がある
            余る場合はそこに低木を回し、残りの低木は低木としての大小SpecialCalcで計算する
            高木のみ、低木のみであれば余りを出す必要はない
            ★★★
            */
            
            if($switch) {
            	if($itemObject->dg_id == $koubokuBgId || $itemObject->dg_id == $koubokuSmId) { //高木用のfactorに加算
            		$factorKouboku += $itemObject->factor * $itemObject->count;
                }
                else { //低木用のfactorに加算
                	$factorTeiboku += $itemObject->factor * $itemObject->count;
                }
            }
            else {
            	$factor += $itemObject->factor * $itemObject->count;
            }
        }
        
        
        //高木で余る容量を算出する 高木factorと低木factorがある時のみ下記計算にてfactorを計算し直す--------
        if($factorKouboku > 0 && $factorTeiboku > 0) {
        
            //切り上げ 係数を切り上げる
        	$factorKouboku = ceil($factorKouboku);
            $factorTeiboku = ceil($factorTeiboku);
            
            //容量取得
            $koubokuBgCapa = $this->dg->find($koubokuBgId)->capacity;
            $koubokuSmCapa = $this->dg->find($koubokuSmId)->capacity;

			$amariCapa = 0;
            
            if($factorKouboku <= $koubokuSmCapa) { //合計factorが高木(小)で収まるなら
                if($factorKouboku < $koubokuSmCapa) {
                	$amariCapa = $koubokuSmCapa - $factorKouboku;
                }
				//factorと高木(小)容量が同じであれば、余りは出ないのでスルーする
            }
            else { //合計factorが高木(小)で収まらなければ、必ず昇格1回目は高木（大）になる
                $amari = $factorKouboku % $koubokuBgCapa;
                $answer = $factorKouboku / $koubokuBgCapa;
                
//                echo $amari. '/' . $factorKouboku;
//                exit;
                
                if($amari > 0) {
                    if($answer < 1) { //bgCapaに余裕が出る時（昇格しない時） factor:3/capa:6 の時など amari:3/answer:0.5
                        $amariCapa = $koubokuBgCapa - $factorKouboku;
                    }
                    else { //昇格する時 factor:7/capa:6 の時など amari:1/answer:1.11・・
                        if($amari <= $koubokuSmCapa) { //amariが高木(小)容量内で収まる時
                        	if($amari < $koubokuSmCapa) {
                            	$amariCapa = $koubokuSmCapa - $amari;
                            }
                            //amariと高木(小)容量が同じであれば、余りは出ないのでスルーする
                        }
                        else {
                            $amariCapa = $koubokuBgCapa - $amari;
                        }
                        
                    }
                }
                //amariが0（割り切れる）であれば、余りは出ないのでスルーする
            }
            
            //余ったcapaを高木factorに回し、低木factorから引く
            if($amariCapa > 0) {
            	$factorKouboku = $factorKouboku + $amariCapa;
                $factorTeiboku = $factorTeiboku - $amariCapa;
            }
        }
        
//            echo $factor . '/'. $switch . '/' . $this->prefId;
//            exit;
        
        if($switch) {
        	$teibokuDf = 0;
            
            if($factorTeiboku > 0) {
            	$teibokuDf = $this->specialCalc($sitakoniSmId, $sitakoniBgId, $factorTeiboku);
            }
            
            $deliFee = $this->specialCalc($koubokuSmId, $koubokuBgId, $factorKouboku) + $teibokuDf;
            
            //2019/04変更
            //$deliFee = $this->specialCalc($koubokuSmId, $koubokuBgId, $factor); //特別関数で計算
        }
        else { //下草コニファー（千代田プランツ）は大小の区別がないので通常計算で可能
            //2019/04変更 下草（低木）コニファー（小）を追加し、元の下草コニファーを（大）として、大小行き来の計算をする
            //ORG : $deliFee = $this->normalCalc($sitakoniId, $factor);
            $deliFee = $this->specialCalc($sitakoniSmId, $sitakoniBgId, $factor);
        }
        
        return $deliFee;
    }
    //千代田プランツ(高木-小／大・下草) END =====================================================
    
    
    //千代田プランツ(シモツケ) =================================================================
    public function TiyodaPrantsSimotuke($simotukeItem)
    {
    	//府中ガーデン下草と同じ計算方法。小と大を行ったり来たりする。
        //特殊計算：なし。
        
        $factor = 0;
        
        $simotukeSmId = $this-> simotukeSmId;
        $simotukeBgId = $this-> simotukeBgId;
        
        $deliFee = 0;
        
        //シモツケ商品の係数の合計を算出
        foreach($simotukeItem as $ioi) {
            $factor += $ioi->factor * $ioi->count;   
        }
        
        $deliFee = $this->specialCalc($simotukeSmId, $simotukeBgId, $factor); //下記の特別関数で計算
        
        return $deliFee;
    }
    //千代田プランツ(シモツケ) END =================================================================
    
    
    
    //モリヤコニファー（大・小・下草）========================================================
    public function MoriyaConifer($coniferItem)
    {
    	//モリヤコニファー：大の商品が含まれていれば強制的に大となり、なければ小になる
        //特殊計算は、下草のみ発生する。送料区分は下草の中で特殊送料を計算し、有料分の余りが出れば、大小がある場合はそれを大、小に回す
        //特殊計算：下草のみあり
    	
        $factor = 0;
        $factorKs = 0;
        
        $factorFreeKs = 0;
        $countKs = 0;
        
        $isBg = 0;
        $isSm = 0;
        
        $deliFee = 0;
        
        $coniferBgId = $this-> coniferBgId;
        $coniferSmId = $this-> coniferSmId;
        $coniferKsId = $this-> coniferKsId;
        
        //大があるか、なければ小があるかを判別
        foreach($coniferItem as $ioi) {
            if($ioi->dg_id == $coniferBgId) {
                $isBg = 1;
                break;
            }
        }
        
        if(!$isBg) {
            foreach($coniferItem as $ioi) {
                if($ioi->dg_id == $coniferSmId) {
                    $isSm = 1;
                    break;
                }
            }
        }
        
        

        //コニファー商品の係数の合計を算出
        foreach($coniferItem as $ioi) {	
            /* 特殊計算 対象とする送料区分は下草の中だけで計算し、有料分の余りがあれば、大小がある場合には大小に移動する ******************* */
            
            if($ioi->dg_id == $coniferKsId) { //モリヤ下草の時
            	//特殊送料の余る容量を計算するための準備をここでする
                if($ioi->is_delifee) { //下草で送料無料のもの
                    $factorFreeKs += $ioi->factor * $ioi->count;
                    $countKs++;
                }
                else { //下草で送料有料のもの
                    $factorKs += $ioi->factor * $ioi->count;
                }
            }
            else { //下草以外
            	if(! $ioi->is_delifee) { //送料無料でなければfactor計算する
                	$factor += $ioi->factor * $ioi->count;
                }
            } 
            /* 特殊計算 END ******************* */
            
            //$factor += $ioi->factor * $ioi->count;   
        }
        
        if($countKs > 0) {
            $sitakusaKsCapa = $this->dg->find($coniferKsId)->capacity;
            $sitakusaKsCapa = $sitakusaKsCapa * $countKs;
            $freeCapaKs = $sitakusaKsCapa - $factorFreeKs;
            
            //最終の下草Factorを算出 最終下草Factorが0以上ならその分が送料となるので最終計算をさせる。0以下なら全て無料容量に収まっていることになるので計算不要（送料0）となる。
            $factorKs = $factorKs - $freeCapaKs;
        }

        
        if($factorKs > 0) { //下草特殊送料で有料となる余りが出る場合
            $factor = $factor + $factorKs; //モリヤコニファー大/小に下草有料分の余り係数を足す
        }
        

        if($factor > 0) {
            if($isBg) { //大を含む時
                $deliFee = $this->normalCalc($coniferBgId, $factor);
            }
            elseif($isSm) { //小を含む時
                $deliFee = $this->normalCalc($coniferSmId, $factor);
            }
            else { //下草のみの時
                $deliFee = $this->normalCalc($coniferKsId, $factor);
            }
        }
        
        return $deliFee;
    }
    //モリヤコニファー（大・小・下草）END ========================================================
    
	/* 各送料区分ごとの送料関数 END ******************************************** */
    
    
    /* Main **************************************************************** */
    public function getDelifee()
    {
    	//送料 ---------------------------------
        $deliFee = 0;
        
        $isOnceItem = array();
        $sitakusaItem = array();
        $tiyodaItem = array();
        $simotukeItem = array();
        $coniferItem = array();
        

        //同梱包可能で、配送区分も同じ場合を区別する必要がある
        //同梱包可能なもので配送区分の同じものと異なるものを分けて送料を出す
        foreach($this->itemData as $item) {
        	//府中ガーデンとモリヤコニファーは特殊送料があるので送料有無どちらでも配列に入れる
            
            //府中ガーデン下草->大小を行ったり来たりする
            if($item->dg_id == $this->sitakusaSmId || $item->dg_id == $this->sitakusaBgId) { //下草 府中ガーデンの時 送料有料／無料どちらも
            	if(! $item->is_once) { //下草府中ガーデンで同梱包不可のものはそれぞれ単独で -> 多数あるようなので、ここも重要
                	if(! $item->is_delifee) { //送料無料でなければ計算する
                    	$factor = $item->factor * $item->count;
                    	$deliFee += $this->specialCalc($this->sitakusaSmId, $this->sitakusaBgId, $factor);
                    }                    
                }
                else {
            		$sitakusaItem[] = $item;
                }
        	}
            //モリヤコニファー->大の商品が含まれていれば強制的に大となり、なければ小になる
            elseif($item->dg_id == $this->coniferKsId || $item->dg_id == $this->coniferSmId || $item->dg_id == $this->coniferBgId) { //モリヤコニファーの時 送料有料／無料どちらも
            	if(! $item->is_once) { //モリヤコニファーで同梱包不可のものはそれぞれ単独で -> 多数あるようなので、ここも重要
                	if(! $item->is_delifee) { //送料無料でなければ計算する
                    	$factor = $item->factor * $item->count;
                    	$deliFee += $this->normalCalc($item->dg_id, $factor); 
                    }                   
                }
                else {
            		$coniferItem[] = $item;
                }
            }
            elseif(! $item->is_delifee) { //送料が無料でないもの
                if(! $item->is_once) { //同梱包不可のものはそれぞれ単独で
                    $factor = $item->factor * $item->count;
                    $deliFee += $this->normalCalc($item->dg_id, $factor);
                    
                    //ORG ---
                    //$deliFee += $this->dgRel->where(['dg_id'=>$item->dg_id, 'pref_id'=>$this->prefId])->first()->fee;
                } 
                else { //同梱包可能なものは別配列へ入れて下記へ

					// 高木コニファー(千代田プランツ)の時 高木用の配列に入れる->高木が一つでも含まれていれば高木。大小は府中ガーデン下草と同じ
                    if($item->dg_id == $this->koubokuSmId || $item->dg_id == $this->koubokuBgId || $item->dg_id == $this->sitakoniSmId || $item->dg_id == $this->sitakoniBgId) {
                    	$tiyodaItem[] = $item;
                    }
                    //シモツケの時 シモツケ用の配列に入れる->府中ガーデン下草と同じ
                    elseif($item->dg_id == $this->simotukeSmId || $item->dg_id == $this->simotukeBgId) {
                    	$simotukeItem[] = $item;
                    }
                                     
                    else { //下草・コニファー以外の同梱包商品
                        $isOnceItem[$item->dg_id][] = $item; //dgIdをKeyとして、itemを入れる dgIdが同じitemはdgIdのkeyに対しての配列としてpushされる
                    }
                }
            }
            
            
        }
  		
        
        //下草・コニファー以外の同梱包商品 係数 x 個数の合計が容量を越えれば都道府県送料の倍数となる ============     
        if(count($isOnceItem) > 0) {
        
        	foreach($isOnceItem as $dgIdKey => $itemArrs) {
            	//$count = 0;
            	$factor = 0;
                
                //同じ配送区分のItemに対して 係数 x 個数を出す
                foreach($itemArrs as $ioi) {
                	//$count += $obj->count; //買い物個数
                    $factor += $ioi->factor * $ioi->count;
                }
                
                //dgの容量を取り、係数に対して割り、余りも出す。余りが0なら割ったanswerの倍数送料、余りがあればanswer１以上で少数切り上げをしてその整数値を送料に掛ける
                //通常計算関数にて
                $deliFee += $this->normalCalc($dgIdKey, $factor);
                
            } //foreach first
        
        } // if(count($isOnceItem) > 0)
        

        
        //下草(府中ガーデン)の時 ================
        if(count($sitakusaItem) > 0) {
        	$deliFee += $this->HutyuGardeSitakusa($sitakusaItem);
        }
        
        //高木コニファー・下草コニファー（千代田プランツ）の時  ================
        if(count($tiyodaItem) > 0) {
            $deliFee += $this->TiyodaPrantsConifer($tiyodaItem);
        }
        
        //シモツケ（千代田プランツ）の時 ================
        if(count($simotukeItem) > 0) {
        	$deliFee += $this->TiyodaPrantsSimotuke($simotukeItem);
        }
        
        //モリヤコニファーの時 モリヤコニファーは大商品が含まれているかどうかを確認する必要があるので　大or小を判別できればあとは通常計算で ========
        if(count($coniferItem) > 0) {
        	$deliFee += $this->MoriyaConifer($coniferItem);
        }
        
         
        return $deliFee;
        
        //送料END -----------------
    }
    
    
    
    
    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
