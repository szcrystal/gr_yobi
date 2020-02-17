<?php

namespace App\Http\Controllers\Cart;

use App\Item;
use App\Setting;
use App\User;
use App\UserNoregist;
use App\Sale;
use App\SaleRelation;
use App\Receiver;
use App\PayMethod;
use App\Prefecture;
use App\DeliveryGroup;
use App\DeliveryGroupRelation;
use App\Favorite;
use App\PayMethodChild;
use App\DataRanking;

use App\Mail\OrderEnd;
use App\Mail\Register;
use App\Mail\NoStocked;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Ctm;
use Mail;
use Auth;
use DB;
use Delifee;
use Exception;
use Validator;
use DateTime;
use Route;
use Cookie;
use Payment;

use Client; //AmznPay SDK

use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function __construct(Item $item, Setting $setting, User $user, UserNoregist $userNor, Sale $sale, SaleRelation $saleRel, Receiver $receiver, PayMethod $payMethod, Prefecture $prefecture, DeliveryGroup $dg, DeliveryGroupRelation $dgRel, Favorite $favorite, PayMethodChild $payMethodChild, DataRanking $dataRanking)
    {
        
        //$this -> middleware('adminauth');
        //$this -> middleware('log', ['only' => ['getIndex']]);
        
        $this ->item = $item;
        $this ->setting = $setting;
        $this ->set = $this->setting->get()->first();
        
        $this->user = $user;
        $this->userNor = $userNor;
        $this->sale = $sale;
        $this->saleRel = $saleRel;
        $this->receiver = $receiver;
        $this->payMethod = $payMethod;
        $this->prefecture = $prefecture;
        $this->dg = $dg;
        $this->dgRel = $dgRel;
        $this->favorite = $favorite;
        $this->payMethodChild = $payMethodChild;
        $this->dr = $dataRanking;
        
        //西濃運輸用変数
        $this->seinouObj = Ctm::getSeinouObj();
        //$this->dgSeinouId = $seinouObj->id;
        
        //西濃に対する加減算の金額は消費税対象となる（+3000, -1000）
//		$this->seinouHuzaiokiFee = $seinouObj->huzaiokiFee;
//        $this->seinouSundayFee = $seinouObj->sundayFee;
        
        
        //GMO 決済ID
        $this->gmoId = Ctm::gmoId();
        
//        $this->perPage = 20;
        
        // URLの生成
        //$url = route('dashboard');
        
        /* ************************************** */
        //env()ヘルパー：環境変数（$_SERVER）の値を取得 .env内の値も$_SERVERに入る
    }
    
    
    
    public function index()
    {
        
        $itemObjs = Item::orderBy('id', 'desc')->paginate($this->perPage);
        
        $cates= $this->category;
        
        
        //$status = $this->articlePost->where(['base_id'=>15])->first()->open_date;
        
        return view('dashboard.item.index', ['itemObjs'=>$itemObjs, 'cates'=>$cates,  ]);
    }

    public function show($id)
    {
        $item = $this->item->find($id);
        $cates = $this->category->all();
        $subcates = $this->categorySecond->where(['parent_id'=>$item->cate_id])->get();
        $consignors = $this->consignor->all();
        //$users = $this->user->where('active',1)->get();
        
        $tagNames = $this->tagRelation->where(['item_id'=>$id])->get()->map(function($item) {
            return $this->tag->find($item->tag_id)->name;
        })->all();
        
        $allTags = $this->tag->get()->map(function($item){
            return $item->name;
        })->all();
        
        return view('dashboard.item.form', ['item'=>$item, 'cates'=>$cates, 'subcates'=>$subcates, 'consignors'=>$consignors, 'tagNames'=>$tagNames, 'allTags'=>$allTags, 'id'=>$id, 'edit'=>1]);
    }
   
    public function create()
    {
        $cates = $this->category->all();
        $consignors = $this->consignor->all();
        
        $allTags = $this->tag->get()->map(function($item){
            return $item->name;
        })->all();
//        $users = $this->user->where('active',1)->get();
        return view('dashboard.item.form', ['cates'=>$cates, 'consignors'=>$consignors, 'allTags'=>$allTags]);
    }
    
    public function getClear(Request $request)
    {
    	$request->session()->forget('item');
        $request->session()->forget('all');
        
        return redirect('/');
    }
    
    /* 送料　通常の計算の関数 ******************************** */
    /*
    public function normalCalc($dgId, $prefId, $factor)
    {
    	$deliveryFee = 0;
        
    	$dg = $this->dg->find($dgId);
                
        $capacity = $dg->capacity;
        $answer = $factor / $capacity;
        $amari = $factor % $capacity;
        
        $fee = $this->dgRel->where(['dg_id'=>$dgId, 'pref_id'=>$prefId])->first()->fee;
    
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
            if(is_float($answer)) { //割り切れる時で、なおかつ小数点の余がある時。12.3 / 6 の時amariは0だが、0.3の端数が出る
                $deliveryFee += $fee * ceil($answer); //切り上げ
            }
            else {
                $deliveryFee += $fee * $answer;
            }
        }
        
        return $deliveryFee;
    }
    */
    /* 送料　通常の計算の関数 END ******************************** */
    
    /* 下草・シモツケ・高木コニファー　特別計算の関数 ************************************************** */
    /*
    public function specialCalc($smId, $bgId, $prefId, $factor)
    {
        $deliveryFee = 0;
        
        //下草小の容量: 20
        $smCapa = $this->dg->find($smId)->capacity;
        //下草大の容量: 40
        $bgCapa = $this->dg->find($bgId)->capacity;
        
        //下草小と大のそれぞれの送料
        $smFee = $this->dgRel->where(['dg_id'=>$smId, 'pref_id'=>$prefId])->first()->fee;
        $bgFee = $this->dgRel->where(['dg_id'=>$bgId, 'pref_id'=>$prefId])->first()->fee;
        
        //$factor = 27.9;
    
        if($factor <= $smCapa) { //個数x係数が20以下なら下草小
//                $answer = $factor / $sitakusaSmCapa;
//                $fee = $this->dgRel->where(['dg_id'=>$sitakusaSmId, 'pref_id'=>$prefId])->first()->fee;
            $deliveryFee += $smFee;        
        }
        else {  //個数x係数が20以上なら容量で割る各種計算が必要
            $amari = $factor % $bgCapa;
            $answer = $factor / $bgCapa;
            
            //amariについて
            //0.3 % 6 = 0
            //1.3 % 6 = 1
            //5.3 % 6 = 5
            //6.3 % 6 = 0
            //7.3 % 6 = 1
            //12.3 % 6 = 0
            //13.3 % 6 = 1
            
//            echo $amari . '/' . $answer. '/'. 27.9 % 6 . '/'. is_float($answer);
//            exit;
            
            if($amari > 0) { //amariがある時 0以上の時

                if($answer <= 1) {
                    $deliveryFee += $bgFee;
                }
                else {
                    if($amari <= $smCapa) { //40で割ったamariが下草小で可能の時 下草小のcapacity以下の時 合計係数が95なら40で割ると余は15となり下草小で可能
                    	if($amari == $smCapa && is_float($answer)) { //factor:27.9 / 容量6の時など 27.9 / 6 小数点分でsmCapacityの容量を超えるので
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
                if(is_float($answer)) { //割り切れる時で、なおかつ小数点の余がある時。12.3 / 6 の時amariは0だが、0.3の端数が出る
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
    */
    /* 下草　特別計算の関数 END ************************************************** */
    
    
    /* 値段 商品金額 算出 Sale or Normal Price **************************************************************** */
    private function getItemPrice($item) {
        
        //商品に入力されているSale金額が最優先
        //1円の時のSale計算は矛盾が出るので除外
        
        $isSale = $this->set->is_sale;
        $price = 0;
        //$seinouFee = 0;
        
        if(isset($item->sale_price)) {
            $price = Ctm::getPriceWithTax($item->sale_price);
            //ORG : $price = Ctm::getPriceWithTax($item->sale_price);
        }
        else {
            $itemPrice = isset($item->is_once_down) ?
                isset($item->once_price) ? $item->once_price : $item->price
                : $item->price;
            
            if($isSale) {
                $price = Ctm::getSalePriceWithTax($itemPrice);
                //ORG : $price = Ctm::getSalePriceWithTax($item->price);
            }
            else {
                $price = Ctm::getPriceWithTax($itemPrice);
                //ORG : $price = Ctm::getPriceWithTax($item->price);
            }
        }
        
        if(isset($item->is_huzaioki) && $item->is_huzaioki) {
            $seinouObj = Ctm::getSeinouObj();
            $price = $price - $seinouObj->huzaiokiFee;
        }

        return $price;
    }
    /* END 値段 商品金額 算出 Sale or Normal Price **************************************************************** */
    
    /* ポイント 還元率 算出 is_point or item point **************************************************************** */
    private function getPointBack($item) {
        
        //商品に入力されているポイント還元率が最優先
        
        //$setting = $this->setting->get()->first();
        $pointBack = 0;
        
        if(isset($item->point_back)) {
            $pointBack = $item->point_back / 100;
        }
        else {
            if($this->set->is_point) {
                $pointBack = $this->set->point_per / 100;
            }
        }
        
        return $pointBack;
    }
    /* ポイント 還元率 算出 is_point or item point END **************************************************************** */
    
    
    public function getThankyou(Request $request)
    {
    	if(! $request->session()->has('all')) {
        	return redirect('/');
        }
        
    	$data = $request->all();
     
//         print_r(session('all'));
//         print_r(session('item.data'));
//         exit;

        $itemData = session('item.data');
     	$all = session('all'); //session(all): regist, allPrice, order_number
      	$allData = $all['data']; //session(all.data): destination, pay_method, user, receiver  
     	
      	$regist = $all['regist']; 
       	$allPrice = $all['all_price']; //商品合計のみの金額。（送料、手数料、マイナスポイント）は入っていない
        $totalFee = $all['total_fee']; //商品合計 + 送料／手数料／ポイント全て含む金額
        $deliFee = $all['deli_fee'];
        $codFee = $all['cod_fee'];
        //$takeChargeFee = $all['take_charge_fee'];
        $usePoint = $all['use_point'];      
      	$addPoint = $all['add_point'];
        
        $isAmznPay = $all['is_amzn_pay'];
        $amznOrderReferenceId = $all['order_reference_id']; //amazonPay以外でもnullが必ずセットされている
        
        $destination = $allData['destination'];
        $pm = $allData['pay_method'];
        
        //配送時間
        //$planTime = isset($allData['plan_time']) ? $allData['plan_time'] : array();
        
        //不在置き指定
        //$isHuzaioki = isset($allData['is_huzaioki']) ? $allData['is_huzaioki'] : null;
        
        $userData = Auth::check() ? $this->user->find(Auth::id()) : $allData['user']; //session(all.data.user)
      	$receiverData = $allData['receiver']; //session('all.data.receiver');
        
        
        //order_numberの変数入れ
       	$orderNumber = $all['order_number'];
       
       	//クレカ関連
       	$memberId = isset($allData['member_id']) && $allData['member_id'] != '' ? $allData['member_id'] : null;
       	
        //ここはカード決済Methodの中でカード登録が正常完了した時に取得するSession
       	$isCardRegist = isset($allData['card_regist']) && $allData['card_regist'] != '' ? 1 : 0;

        
        // AmazonPay ======================================
//        if($isAmznPay) {
//            $payObj = new Payment();
//            $payObj->setAmznPay();
            
        /*
            $config = array(
                'merchant_id' => 'AUT5MRXA61A3P',
                'access_key'  => 'AKIAIULMCJL2WZE3LLAQ',
                'secret_key'  => '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',
                'client_id'   => 'amzn1.application-oa2-client.471a3dc352524c5cb3066ece8967eeb2',
                'region'      => 'jp',
                
                //'mws developer_id' => '879609259100',
                //'mws_access_token' => '3pKDQQL1eRfsZpFM0mTMaYxkLScapMmcOAbYoGr5',
            );

            // or, instead of setting the array in the code, you can
            // initialze the Client by specifying a JSON file
            // $config = 'PATH_TO_JSON_FILE';

            // Instantiate the client class with the config type
            $client = new Client($config);
            $client->setSandbox(true);
            
            $requestParameters = array();
            $orderReferenceId = session('all.order_reference_id');

            // Optional Parameter
            $requestParameters['mws_auth_token'] = '3pKDQQL1eRfsZpFM0mTMaYxkLScapMmcOAbYoGr5';
            
            $requestParameters['amazon_order_reference_id'] = $orderReferenceId;
            $requestParameters['address_consent_token'] = session('all.access_token');
            
            //$response = $client->getMerchantAccountStatus($requestParameters);
            //$response = $client->getOrderReferenceDetails($requestParameters);

            // setOrderReferenceDetails -------------
            $setParams = [
                'amazon_order_reference_id' => $orderReferenceId,
                'amount' => $totalFee,
                'currency_code' => 'JPY',
                'seller_order_id' => $orderNumber,
            ];
            
            //$setParams = array_merge($requestParameters, $setParams);
            
            $response = $client->setOrderReferenceDetails($setParams);
            
            //echo $response->toXml() . "\n";
            $obj = simplexml_load_string($response->toXml());
            $obj = json_decode(json_encode($obj), true);
            
            if(isset($obj['Error'])) {
                //Error処理・・・
    //            [Error] => Array
    //            (
    //                [Type] => Sender
    //                [Code] => InvalidParameterValue
    //                [Message] => The Value 'null' is invalid for the Parameter 'Amount'
    //            )
                print_r($obj);
                exit;
            }
            
            // confirmOrderReference --------------------------
            $confirmParams = [
                'amazon_order_reference_id' => $orderReferenceId,
            ];
            
            $response = $client->confirmOrderReference($confirmParams);
            
            $obj = simplexml_load_string($response->toXml());
            $obj = json_decode(json_encode($obj), true);
            
            if(isset($obj['Error'])) {
            // Error処理・・・
            //    [Error] => Array
            //     (
            //          [Type] => Sender
            //          [Code] => InvalidParameterValue
            //          [Message] => The Value 'null' is invalid for the Parameter 'Amount'
            //      )
                print_r($obj);
                exit;
            }
            
            // ==========================
            // getOrderReferenceDetailsはここでは不要
//            $response = $client->getOrderReferenceDetails($requestParameters);
//
//            $obj = simplexml_load_string($response->toXml());
//            $obj = json_decode(json_encode($obj), true);
//
//            if(isset($obj['Error'])) { //stateがopenならの条件もあった方がいいか
//                // Error処理・・・
//                print_r($obj);
//                exit;
//            }
            //================================
                    
            // Authorize ======
            $authParams = [
                'amazon_order_reference_id' => $orderReferenceId,
                'authorization_reference_id' => $orderNumber,
                'authorization_amount' => $totalFee,
                'currency_code' => 'JPY',
                'transaction_timeout' => 0,
                //'capture_now' => TRUE,
            ];
            
            $response = $client->authorize($authParams);
            
            $obj = simplexml_load_string($response->toXml());
            $obj = json_decode(json_encode($obj), true);
            
            if(isset($obj['Error'])) { //stateがopenならの条件もあった方がいいか
                // Error処理・・・
                print_r($obj);
                exit;
            }
            
            echo "Atuh";
            print_r($obj);
            exit;
            
            //        echo $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination']['AddressLine1'];
            //        echo $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer']['Name'];
            //        echo $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer']['Email'];
            //        exit;
        */
//        }
        // AmazonPay END ====================================


/*
        //配送時間指定 itemごとにitemDataの配列内に入れる
//        if(count($planTime) > 0) {
//            foreach($itemData as $key => $value) {
//                
//                foreach($planTime as $dgKey => $dgTime ) {
//                    $dgId = $this->item->find($value['item_id'])->dg_id;
//                    if($dgKey == $dgId) {
//                        $itemData[$key]['plan_time'] = $dgTime;
//                    }
//                }
//            }
//        }
*/
      
      	//User登録処理 ==============
      	$userId = 0;
        $isUser = 0;
        
       	if(Auth::check()) { 
        	$uObj = $this->user->find(Auth::id());
        	$userId = $uObj->id;
         	
            $uObj->decrement('point', $usePoint);
          	//$uObj->increment('point', $addPoint); //追加pointは配送後に加算する
            
            if($isCardRegist) { //カード登録時
				$uObj->increment('card_regist_count', 1);
            }
            
            if(isset($memberId)) { //gmo-member登録時 新規登録以外はnullとなる
            	$uObj->member_id = $memberId;
                $uObj->member_regist_date = date('Y-m-d H:i:s', time());
            	$uObj->save();
            }
                     
         	$isUser = 1;   
        }
        else {   
            $userData['magazine'] = isset($userData['magazine']) ? $userData['magazine'] : 0;
            
            //Birth Input 年月日1つでも0があるなら入力しない　ことにしているがどうか
//            if( ! $userData['birth_year'] || ! $userData['birth_month'] || ! $userData['birth_day']) {
//            	$userData['birth_year'] = 0;
//                $userData['birth_month'] = 0;
//                $userData['birth_day'] = 0;
//            }
            
            //session('all.data.user.magazine', $userData['magazine']); //session入れ　不要？？
            
            if($regist) {   
                $userData['password'] = bcrypt($userData['password']);
      			//$userData['point'] = $addPoint; //=>pointは配送後に加算する
                
                if($isCardRegist) { //カード登録時
					$userData['card_regist_count'] = 1;
            	}
            
            	if(isset($memberId)) { //gmo-member登録時 新規登録以外はnullとなる
            		$userData['member_id'] = $memberId;
                    $userData['member_regist_date'] = date('Y-m-d H:i:s', time());
            	}
                
//                $userData['birth_year'] = $userData['birth_year'] ? $userData['birth_year'] : null;
//                $userData['birth_month'] = $userData['birth_month'] ? $userData['birth_month'] : null;
//                $userData['birth_day'] = $userData['birth_day'] ? $userData['birth_day'] : null;
                
                $user = $this->user;
                $user->fill($userData);
                $user->save();
                
                $userId = $user->id;
                $isUser = 1;
                
                Auth::login($user);
                
                //ポイントの処理が必要
            }
            else {
                $userNor = $this->userNor;
                $userNor->fill($userData);
                $userNor->save();
                
                $userId = $userNor->id;
            }
        } //AuthCheck
        
        
        //配送先登録 Receiver 別先であってもなくても登録
        $isEmpty = isset($receiverData) ? 1 : 0; //不要かも
//        foreach($receiverData as $receive) {
//        	if(empty($receive)) { //空の時はTrueになる
//         		$isEmpty = 0;
//           		break;      
//         	}    
//        }
       
       //if($isEmpty && ! $destination) { //receiveDataが入力されている時
       $receiverData['user_id'] = $userId;
       $receiverData['regist'] = $regist;
       $receiverData['order_number'] = $all['order_number'];
       //$receiverData['is_user'] = $isUser;
       
       if(! $destination) { //配送先が登録先の時（別配送先でない時）
       		$receiverData['name'] = $userData['name'];
         	$receiverData['hurigana'] = $userData['hurigana'];      
       		$receiverData['tel_num'] = $userData['tel_num'];
         	$receiverData['post_num'] = $userData['post_num'];
          	$receiverData['prefecture'] = $userData['prefecture'];
          	$receiverData['address_1'] = $userData['address_1'];
           	$receiverData['address_2'] = $userData['address_2']; 
            //$receiverData['address_3'] = $userData['address_3'];               	  
        }
        
        $receiver = $this->receiver;
        $receiver->fill($receiverData);
        $receiver->save();
        
        $receiverId = $receiver->id;
        //配送先END -----------------------------------------------
   
       
       //paymentCode ネットバンクとGMOのみ
       $pmChild = null;
       $payPaymentCode = null;
       
       if($pm == 3) {
            $pmChild = isset($allData['net_bank']) ? $allData['net_bank'] : 0;
            
       		if($data['payment_code'] == 4)
         		$payPaymentCode = 'ジャパンネットバンク';
         	elseif($data['payment_code'] == 5) 
          		$payPaymentCode = '楽天銀行';
            elseif($data['payment_code'] == 17)  
            	$payPaymentCode = 'SBIネット銀行';     
       }
       elseif($pm == 4) {
       		$payPaymentCode = 'GMO後払い';
       }
       

       
       	//SaleRelationのcreate  ========================
        if(Ctm::isAgent('sp')) {
        	$agentType = 1;
        }
        elseif(Ctm::isAgent('tab')) {
        	$agentType = 2;
        }
        else {
        	$agentType = 0;	
        }
        
    	$saleRel = $this->saleRel->create([
            'order_number' => $orderNumber, //コンビニなし
            'regist' =>$regist,
            'user_id' =>$userId,
            'is_user' => $isUser,
            'receiver_id' => $receiverId, 
            'pay_method' => $pm,
            'pay_method_child' => $pmChild,
            
            'deli_fee' => $deliFee,
            'cod_fee' => $codFee,
            //'take_charge_fee' => $takeChargeFee,
            'use_point' => $usePoint,
            'add_point' => $addPoint,
            'seinou_huzai' => $all['s_huzai_price'],
            'seinou_sunday' => $all['s_sunday_price'],
            'all_price' => $allPrice, //商品withTax x 個数 の合計　同梱包割引／不在置きは含まれる。送料等は含まれない。
            'total_price' => $totalFee, //送料 手数料 日曜配達　含む全ての合計
                //$allPrice + $deliFee + $codFee - $usePoint - $allData['s_huzai_price'] + $allData['s_sunday_price']
            'destination' => $destination,
            'huzai_comment' => isset($allData['huzai_comment']) ? $allData['huzai_comment'] : null,
            'user_comment' => $allData['user_comment'],
            
            'amzn_reference_id' => $amznOrderReferenceId, //amazonPay以外でもnullが必ずセットされている
            
            'deli_done' => 0,
            'pay_done' => 0,
            
            //'pay_trans_code' =>$data['trans_code'], //コンビニはこれのみ
            //'pay_user_id' =>isset($data['user_id']) ? $data['user_id'] : null, //コンビニなし
            
            'pay_payment_code' => $payPaymentCode, //ネットバンク、GMO後払いのみ  
            'pay_result' => isset($data['result']) ? $data['result'] : null, //クレカのみ
            'pay_state' => isset($data['state']) ? $data['state'] : null,  //ネットバンク、GMO後払いのみ
              
        	'agent_type' => $agentType,
        ]);
        
        $saleRelId = $saleRel->id;
        
        $receiver->salerel_id = $saleRelId;
        $receiver->save();
    
    	$saleIds = array();
        $saleObjs = array();
        $stockNone = array();
        
//        $prefectureId = $this->prefecture->where('name', $receiver->prefecture)->first()->id;
        
        //売上登録処理 Sale create ========================
        foreach($itemData as $key => $oneSesData) {
        	
//            $oneItemData = array();
            
			$i = $this->item->find($oneSesData['item_id']);
            
            $singleSellCount = $oneSesData['item_count']; //DB追加以降でも使用する変数
            $itemTotalPrice = $oneSesData['item_total_price'];
            
            
			//$oneItemData[] = $i;
            
            $sale = $this->sale->create(
                [
                	'salerel_id' => $saleRelId,
                	'order_number' => $orderNumber, //コンビニなし
                    
                    'item_id' => $oneSesData['item_id'],
                    'item_count' => $singleSellCount, 
                    
                    'regist' =>$all['regist'],
                    'user_id' =>$userId,
                    'is_user' => $isUser,
                    'receiver_id' => $receiverId,
					
                    'pay_method' => $pm,
                    'deli_fee' => $oneSesData['single_deli_fee'],
                    'cod_fee' => $codFee,
                    'use_point' => 0,
                    'add_point' => $oneSesData['single_point'],
            		'seinou_huzai' => $oneSesData['seinou_huzai_down_price'],
                    'seinou_sunday' => $oneSesData['seinou_sunday_up_price'],
                    
                    'single_price' => $this->getItemPrice($i),
                    'total_price' => $itemTotalPrice,
                    
                    'cost_price' => $i->cost_price * $singleSellCount,
                    'charge_loss' => 0,
                    
                    'plan_date' => isset($allData['plan_date']) ? $allData['plan_date'] : null,
                    'plan_time' => $oneSesData['plan_time'],
                    
                    'is_huzaioki' => isset($oneSesData['is_huzaioki']) ? $oneSesData['is_huzaioki'] : null, //不在割引
                    'is_once_down' => isset($oneSesData['is_once_down']) ? $oneSesData['is_once_down'] : null, //同梱包割引
                    
                    'deli_done' => 0,
                    'pay_done' => 0,
                    
                    /*
                    'destination' => $destination,
                    
                    'pay_trans_code' =>$data['trans_code'], //コンビニはこれのみ
            		'pay_user_id' =>isset($data['user_id']) ? $data['user_id'] : null, //コンビニなし
            		
              		'pay_payment_code' => $paymentCode, //ネットバンク、GMO後払いのみ  
              		'pay_result' => isset($data['result']) ? $data['result'] : null, //クレカのみ
                	'pay_state' => isset($data['state']) ? $data['state'] : null,  //ネットバンク、GMO後払いのみ
                 	*/   
                              
                ]
            );
            
            $saleIds[] = $sale->id;
            $saleObjs[] = $sale; //for ggl

            //在庫引く処理 ===================
            //$item = $this->item->find($val['item_id']);
            $i->timestamps = false; //在庫引く時とsale Countでタイムスタンプを上書きしない
            $i->decrement('stock', $singleSellCount);

            if(! $i->stock || $i->stock < 0) { //在庫が0になればitem_idを配列へ メールで知らせるため
            	$stockNone[] = $i->id;
            }
            
            //Sale Count処理（itemの売れた個数）================
            $parentItem = null;
            
            if($i->pot_type == 3) {
            	$parentItem = $this->item->find($i->pot_parent_id);
                $parentItem->increment('sale_count', $singleSellCount);
                
                $pots = $this->item->where(['open_status'=>1, 'pot_type'=>3, 'pot_parent_id'=>$i->pot_parent_id])->get();
                $stockCount = 0;
                
                if($pots->isNotEmpty()) {
                    $stockCount = $pots->sum('stock');
                    $parentItem->update(['stock' => $stockCount]);
                }
                
            }
            else {
            	$i->increment('sale_count', $singleSellCount);
            }
            
            // DataRankingにSet ===========================
            $itemForRank = isset($parentItem) ? $parentItem : $i;
            
            $this->dr->create([
                'sale_id' => $sale->id,
                'item_id' => $itemForRank->id,
                'cate_id' => $itemForRank->cate_id,
                'subcate_id' => $itemForRank->subcate_id,
                'pot_type' => $itemForRank->pot_type,
                'sale_count' => $singleSellCount,
                'sale_price' => $itemTotalPrice,
            ]);
            
            //お気に入りにsale_idを入れる。お気に入りに購入履歴を残すため。==========
            if($isUser) {
            	$fav = $this->favorite->where(['user_id'=>$userId, 'item_id'=>$i->id])->first();
                
             	if(isset($fav)) {
              		$fav->sale_id = $sale->id;
                	$fav->save();      
              	}
            }
            
                        
        } //foreach
        
        //各商品の合計金額
        //$allTotal = $this->sale->find($saleIds)->sum('total_price');
        
        
        
        //Mail送信 ----------------------------------------------
        //Ctm::sendMail($data, 'itemEnd');
        $now = now();
        
        //for User
        Mail::to($userData['email'], $userData['name'])->queue(new OrderEnd($saleRelId, 1));
        
        //for Admin（3分後に送信 -> 1分後に変更）
        Mail::to($this->set->admin_email, $this->set->admin_name)->later($now->addMinutes(2), new OrderEnd($saleRelId, 0))/*->queue(new OrderEnd($saleRelId, 0))*/;
        
        if($regist) { //ユーザー新規登録の時（2分後に送信）
        	Mail::to($userData['email'], $userData['name'])->later($now->addMinutes(3), new Register($userId))/*->queue(new Register($userId))*/; //for User New Regist
        }
        
        
        //在庫確認（5分後に送信） -----------------------------------------------
        if(count($stockNone) > 0) {
        	
        	$str = '下記商品の在庫がなくなりました。'. "\n\n";
            
            foreach($stockNone as $itemIdVal) {
                $itemVal = $this->item->find($itemIdVal);
                
            	$str .= '●' . $itemVal->number. "\n";
            	$str .= $itemVal->title. "\n";
        		$str .= url('dashboard/items/'. $itemIdVal). "\n\n";
                
                if($itemVal->stock < 0) {
                    $str .= "<b>この商品の在庫に不整合が起きています。直ぐに確認して下さい。<br>原因として、在庫数以上の購入がされたこと等が考えられます。</b><br><br><br>";
                }
            }
            
            Mail::later($now->addMinutes(5), new NoStocked($str)); //queue
        
//            Mail::raw($str, function ($message) {
//            	$setting = $this->setting->get()->first();
//                
//                $message -> from('no-reply@green-rocket.jp', $setting->admin_name)
//                         -> to($setting->admin_email, $setting->admin_name)
//                         -> subject('商品の在庫がなくなりました。');          
//            });
            
        }
        
        
        if(! Ctm::isEnv('local')) {
            $request->session()->forget('item');
            $request->session()->forget('all'); 
		}   
     
     	$pmModel = $this->payMethod;
        
        $metaTitle = 'ご注文完了' . '｜植木買うならグリーンロケット';
     	
        return view('cart.end', ['regist'=>$regist, 'orderNumber'=>$orderNumber, 'metaTitle'=>$metaTitle]);
      
      
      //クレカからの戻りサンプルURL
      //https://192.168.10.16/shop/thankyou?trans_code=718296&user_id=9999&result=1&order_number=679294540
      //後払い戻りサンプルURL
      //https://192.168.10.16/shop/thankyou?trans_code=718177&order_number=1449574270&state=5&payment_code=18&user_id=9999    
    }

/*
    public function postAfterPay(Request $request)
    {
    	
    }
*/
    
    //決済エラー画面
    public function getShopError(Request $request)
    {
    	$data = $request->all();
        
    	return view('cart.error', []);
    }
    
    
/*
    //クレカ決済 Confirm上でトークンを取得後ここにPostされる
    public function postCardPay(Request $request)
    {
    	$data = $request->all();
     
        //$data['Amount'] => 送信される総合金額
        
        //URL 接続ドメイン ---------------
        //$url = $this->set->is_product ? "https://p01.mul-pay.jp/" : "https://pt01.mul-pay.jp/";
        
        //cUrl Option
//        $options = [
//            //CURLOPT_URL => $url . "/payment/SaveMember.idPass",
//            CURLOPT_RETURNTRANSFER => true, //文字列として返す
//            CURLOPT_POST => true,
//            //CURLOPT_POSTFIELDS => http_build_query($userRegDatas),
//            CURLOPT_TIMEOUT => 60, // タイムアウト時間
//        ];
        
        
        $isRegistUser = session('all.regist');
        $isRegistCard = session('all.data.is_regist_card');
        //$isRegistCard = session('all.data.is_regist_card') != '' ? session('all.data.is_regist_card') : 0;
        
        $cardSeqSession = session('all.data.card_seq');
        
        $memberId = null;
        $cardSeqNum = null;
        
        //カード登録番号を新しい変数に入れる　新しいカードであればこの変数($cardSeqNum)はnullのまま
        if($cardSeqSession != '' && $cardSeqSession != 99) {
            $cardSeqNum = $cardSeqSession;
        }
        
        
        if(Auth::check() ) {
            $u = $this->user->find(Auth::id());
            
            if(isset($u->member_id)) {
            	$memberId = $u->member_id;
            }
        }
        
//        echo $isRegistCard;
//        exit;
        
        //メンバー登録 & カード登録-------------------------------------------
        if($isRegistCard && $cardSeqSession == 99) { //決済の下に移動すればダブルTokenでエラーに出来る
            
            if(! isset($memberId)) {
                
                //会員だがmemberId nullの時、新規会員登録の時
                //GMOの保管期間は無限
                //2重登録はされないようなのでカード登録なら必ずここを通すか？？
                
                $memberId = Ctm::getOrderNum(15);
                
                $memberRegDatas = [
                    'SiteID' => $this->gmoId['siteId'],
                    'SitePass' => $this->gmoId['sitePass'],
                    'MemberID' => $memberId,
                    //'MemberName' => ,
                ];
                
                $memberRegResponse = Ctm::cUrlFunc("SaveMember.idPass", $memberRegDatas);
                
                //正常Str：MemberID=wff877177929430
                $memberRegArr = explode('&', $memberRegResponse);
                $memberRegSuccess = array();
            
            
                foreach($memberRegArr as $res) {
                    $arr = explode('=', $res);
                    $memberRegSuccess[$arr[0]] = $arr[1];
                }
                
                //Error時 $memberRegResponse Error処理をここに ***********
                //ErrCode=E01&ErrInfo=E01210002
                if(array_key_exists('ErrCode', $memberRegSuccess)) {
                    return view('cart.error', ['erroeName'=>'[cc-5001-'.$memberRegSuccess['ErrInfo'] . ']', 'active'=>3]);
                }
                else {
                	session()->put('all.data.member_id', $memberId);
                }
                
            }
        
            //クレカ登録 -----------------------------------------
            $cardRegDatas = [
            	'SiteID' => $this->gmoId['siteId'],
            	'SitePass' => $this->gmoId['sitePass'],
           		'MemberID' => $memberId, //ここでnullであることはない
                'SeqMode' => 0, //shopping中はCardSeqがずれることはないので論理で
            	//$registDatas['MemberName'] = ;
            	'Token' => $data['token'],
            ];
            
            $cardRegResponse = Ctm::cUrlFunc("SaveCard.idPass", $cardRegDatas);
            
            //正常Str：CardSeq=0&CardNo=*************111&Forward=2a99662
            $cardRegArr = explode('&', $cardRegResponse);
        	$cardRegSuccess = array();
        
        
            foreach($cardRegArr as $res) {
                $arr = explode('=', $res);
                $cardRegSuccess[$arr[0]] = $arr[1];
            }
            
            //$userRegResponse Error処理をここに ***********
            if(array_key_exists('ErrCode', $cardRegSuccess)) {
            	//カード会社から返却された時 or E61010002（カード番号異常/利用不可カードの時）
                if(strpos($cardRegSuccess['ErrCode'], 'G') !== false || strpos($cardRegSuccess['ErrCode'], 'C') !== false || strpos($cardRegSuccess['ErrInfo'], 'E61010002') !== false) {
                    //$errors['carderr'] = 'カード情報が正しくないか、お取り扱いが出来ません。';
                    return redirect('shop/form?carderr=1000')->with('ErrInfo', '[cc-5002-'.$cardRegSuccess['ErrInfo'].']');;
                }
                else {
                    return view('cart.error', ['erroeName'=>'[cc-5002-'.$cardRegSuccess['ErrInfo'].']', 'active'=>3]);
                }
            }
            else {
            	$cardSeqNum = $cardRegSuccess['CardSeq']; //新しい論理のCardSeq値が返る

                //カード登録するの判定をsession入れ => カード登録出来たという判定
                session()->put('all.data.card_regist', 1);
            }

            
//            echo $cardRegResponse;
//            exit;
        
        }
        
        
        //決済 -------------------------------------------
        //取引実行 ---------------------------------
        
        $switchSec = 1;
        
        if($switchSec) {
        //User識別
        $trstDatas = [
        	'ShopID' => $this->gmoId['shopId'],
            //'ShopID' => '1111111', //ID or パスワードを変えると意図的にエラーにできる
        	'ShopPass' => $this->gmoId['shopPass'],
	        //'ShopPass' => 'bgx3a3x';
        
        	'JobCd' => 'CAPTURE', //即時売上
        	'OrderID' => $data['OrderID'],
        	'Amount' => $data['Amount'],
        ];
        
        //print_r($datas);
        //exit;
        
        $trstResponse = Ctm::cUrlFunc("EntryTran.idPass", $trstDatas);

		// =========================
//        $ch = curl_init();
//
//        $options = [
//            CURLOPT_URL => $url . "payment/EntryTran.idPass",
//            CURLOPT_RETURNTRANSFER => true, //文字列として返す
//            CURLOPT_POST => true,
//            CURLOPT_POSTFIELDS => http_build_query($datas),
//            CURLOPT_TIMEOUT => 20, // タイムアウト時間
//        ];
//
//        curl_setopt_array($ch, $options);
//
//        $response = curl_exec($ch);
//        curl_close($ch);
        // ===========================
        
        //ErrCode=E01&ErrInfo=E01040010
        //AccessID=5bdbac2fa1e034a90227382dcd67239f&AccessPass=96bb7efe36501aba8865696db0f9687c
        //echo $response;
                
        $resArr = explode('&', $trstResponse);
        $sucArr = array();
        
        
        foreach($resArr as $res) {
        	$arr = explode('=', $res);
        	$sucArr[$arr[0]] = $arr[1];
        }
        
        //Error時
        if(array_key_exists('ErrCode', $sucArr)) {
        	return view('cart.error', ['erroeName'=>'[cc-5003-'.$sucArr['ErrInfo'].']', 'active'=>3]);
        }
        
        
//        print_r($sucArr);
//        exit;
        
        
        //決済実行 -------------------------
        $settleDatas = [
        	'AccessID' => $sucArr['AccessID'],
            //'AccessID' => 1234, //このIDを変えるとエラーに出来る
            'AccessPass' => $sucArr['AccessPass'],
            'OrderID' => $data['OrderID'],
            'Method' => 1, //支払い方法:一括
        ];
        
        if(isset($cardSeqNum)) { //カード登録時 登録したカード連番を利用
            $settleDatas['SiteID'] = $this->gmoId['siteId'];
            $settleDatas['SitePass'] = $this->gmoId['sitePass'];
           	$settleDatas['MemberID'] = $memberId;
            $settleDatas['SeqMode'] = 0; //shopping中はCardSeqがずれることはないので論理で
            $settleDatas['CardSeq'] = $cardSeqNum;
        }
        else { //カード登録しない時 Tokenを利用
        	$settleDatas['Token'] = $data['token'];
        }
        
        //cUrl
        $settleResponse = Ctm::cUrlFunc("ExecTran.idPass", $settleDatas);
        
//        echo $settleResponse;
//        exit;
        
        //返るresponseを配列に
        $resSecondArr = explode('&', $settleResponse);
        $sucSecArr = array();
        
        foreach($resSecondArr as $res) {
        	$arr = explode('=', $res);
        	$sucSecArr[$arr[0]] = $arr[1];
        }
        
        //print_r($sucSecArr);
        //$sucSecArr['ErrInfo'] = 'E61010002|E41170099';
        //exit;
        
        //Error時
        if(array_key_exists('ErrCode', $sucSecArr)) {
        	//カード会社から返却された時 or E01260010（カード番号異常/利用不可カードの時。カード登録時と返るエラー番号が違うので注意）
        	if(
            	strpos($sucSecArr['ErrCode'], 'G') !== false || 
                strpos($sucSecArr['ErrCode'], 'C') !== false || 
                strpos($sucSecArr['ErrInfo'], 'E01260010') !== false ||
                strpos($sucSecArr['ErrInfo'], 'E411') !== false
                ) {
            	//$errors['carderr'] = 'カード情報が正しくないか、お取り扱いが出来ません。';
            	return redirect('shop/form?carderr=1000')->with('ErrInfo', '[cc-5004-'.$sucSecArr['ErrInfo'].']');
            }
            else {
        		return view('cart.error', ['erroeName'=>'[cc-5004-'.$sucSecArr['ErrInfo'].']', 'active'=>3]);
            }
        }
        
//        echo $resSecond;
//        exit;

		}//switchSec
        


        return redirect('shop/thankyou');
        
        
        //Epsilon ========================================================================================================
        //1回postで送信（file_get_contentsで）し、結果がxmlで返る。その結果が正常ならepsilonへリダイレクトするという仕様
        //イプシロン_system_manual.pdfの47ページに返り値があり
        //しかし、この仕様については詳しく書いていない。サンプルCGIを見ろということらしい
        //https://www.epsilon.jp/developer/each_time.html
        
        $strData = implode(',', $datas);
        
    	mb_language('Japanese');
        mb_internal_encoding('UTF-8');

        // ヘッダで、相手方に送信フォーマットとデータの長さを伝える
        $header = [
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: '. strlen($strData)
        ];

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode('\r\n', $header),
                'content' => http_build_query($datas, '', '&'),
                //'content' => http_build_query($datas),
                'max_redirects' => 3,        // リダイレクトの最大回数 def:3
                'timeout' => '20',           // タイムアウトの秒数指定
            ]
        ]);
        
        
        $isProduct = $this->set->is_product;

        if($isProduct) { //本番環境
            $url = "https://pt01.mul-pay.jp/payment/EntryTran.idPass"; //本番(完了通知書：contract_66254480.pdf内にあり)
        }
        else { //テスト環境
            // 結果問い合わせ用URL CGI-2利用(basicAuth不要、設定画面からのIP制限有)
            // サンプルソースのCGI-1はIP制限無し、basicAuthのみで制限
            $url = "https://pt01.mul-pay.jp/payment/EntryTran.idPass";
        }
//        else{
//            throw new Exception('決済サーバURLの指定が異常');
//        }

        $res = file_get_contents($url, false, $context);
        
        if(!$res){
        	throw new Exception('決済サーバに情報が送信できない');
        }
        
        //return (string)$res;
        
        $xml = (string)$res;
        
        echo $res;
        exit;
        
        $obj = simplexml_load_string($xml);
        if(!$obj){
        	throw new Exception('決済サーバからの情報を解析できない');
        }

        //受け取ったxmlをjsonに変換してからデコードして配列にするという黒魔術
        $json_res =json_encode($obj);
        $decode_res = json_decode($json_res,TRUE);
        
//        print_r($decode_res);
//        exit;

        //普通にforeach１段で回すと、無駄な多次元配列になってしまう 例:$arr[0]['result']
        //2段で回すことで、添字が文字列のみの一次元連想配列にする 例:$arr['result']
        //流石に同じ添字が存在しないことを祈る（API信用できてない）
        $array_res = [];
        foreach($decode_res['result'] as $key => $val){
            $attributes = $val['@attributes'];
            foreach( $attributes as $key_attr => $val_attr ){
                $array_res[$key_attr] = (string)$val_attr;
            }
        }
        //return $array_res;

        if(!$array_res['result']){ //失敗時の処理
            $err_code = $array_res['err_code'];
            $err_detail = urldecode($array_res['err_detail']);

            $err_msg = '決済データの送信に失敗 code-' . $err_code . ':' . $err_detail . PHP_EOL;
            $err_msg .= 'memo1:' . $array_res['memo1'] . PHP_EOL;
            $err_msg .= 'memo2:' . $array_res['memo2'] . PHP_EOL;
            
            //throw new Exception(mb_convert_encoding($err_msg, "UTF-8", "auto"));
            
            $errors['fromEpe'] = $err_msg;
            return redirect('shop/confirm')->withErrors($errors);

        }//成功時の処理
        
        $redirectUrl = urldecode($array_res['redirect']);
        return redirect()->away($redirectUrl);
        
        //Epsilon END ========================================================================================================
    }
*/

    public function postConfirm(Request $request)
    {
        //print_r($request->all());
//        echo session('all.access_token');
        //exit;
        
        //$data = $request->all();
        //echo $data['order_reference_id'];
        
//    	if($request->isMethod('get')) {
//        	abort(404);
//        }

        $loginBtn = $request->has('loginBtn') ? 1 : 0;
        
        if($loginBtn) {
            $rules = [
                'email' => 'required|email|max:255',
                'password' => 'required|min:8',
            ];

            $messages = [];

            //$this->validate($request, $rules, $messages);
            $v = Validator::make($request->all(), $rules, $messages);
            if($v->fails()) {
                return redirect()->back()->withErrors($v, 'login')->withInput();
            }

            $data = $request->all();

            $credentials = $request->only('email', 'password');
            $credentials['active'] = 1;

            $remember = $request->has('remember') ? 1 : 0;

            //$prevUrl = $request->has('to_cart') ? '/shop/form' : $data['previous'];
            $prevUrl = '/shop/form';
            
            if (Auth::attempt($credentials, $remember)) { // 認証に成功した
                return redirect()->intended($prevUrl)->withInput();
            }
            else {
                $errors = ['認証できません。ご入力内容を確認して下さい。'];
                return redirect()->back()->withInput()->withErrors($errors, 'login');
            }

            //Route::post('login', 'Auth\LoginController@login');
            //return redirect()->action('Auth\LoginController@login');        
        }
    
    	$pt=0; //ポイント
    	if(Auth::check()) {
     		$pt = $this->user->find(Auth::id())->point;
     	}

		//Regist or Not 既存ユーザーはregist:0
		$regist = $request->has('regist') && $request->input('regist') ? 1 : 0;

        $rules = [
        	'regist' => 'sometimes|required',
            'user.name' => 'sometimes|required|max:255',
            'user.hurigana' => 'sometimes|required|max:255',
            'user.tel_num' => 'sometimes|required|numeric',
//            'cate_id' => 'required',
			'user.post_num' => 'sometimes|required|nullable|numeric|digits:7', //numeric|max:7
   			'user.prefecture' => 'sometimes|not_in:0',         
   			'user.address_1' => 'sometimes|required|max:255',
      		//'user.address_2' => 'sometimes|required|max:255', 
            'user.email' => 'sometimes|required|email|max:255', 
        	//'user.password' => 'sometimes|required|min:8|confirmed', 
         	//'user.password_confirmation' => 'sometimes|required|min:8',
            
            'user.password' => $regist ? 'sometimes|required|min:8|confirmed' : '', 
         	'user.password_confirmation' => $regist ? 'sometimes|required|min:8' : '',
            
			'use_point' => 'numeric|max:'.$pt,
   			        
			//'destination' => 'required_without:receiver.name,receiver.hurigana,receiver.tel_num,receiver.post_num,receiver.prefecture,receiver.address_1,receiver.address_2,receiver.address_3',
            'receiver.name' => 'required_if:destination,1|max:255', //ORG:required_with:destination|max:255
            'receiver.hurigana' => 'required_if:destination,1|max:255',
            'receiver.tel_num' => 'required_if:destination,1|nullable|numeric',
            'receiver.post_num' => 'required_if:destination,1|nullable|numeric|digits:7',
            'receiver.prefecture' => 'required_if:destination,1',
            'receiver.address_1' => 'required_if:destination,1|max:255',
            //'receiver.address_2' => 'required_with:destination|max:255',
            //'receiver.address_3' => 'max:255',
            
            //'huzai_comment' => 'required_if:is_huzaioki,1|max:30000' ,
            'huzai_comment' => 'required_if:is_huzaioki,1|max:30000' ,
            'user_comment' => 'max:30000',
            
//            'user_comment' => [
//            	'max:30000',
//                function($attribute, $value, $fail) use($request) {
//                    //if( $request->has('is_huzaioki') && in_array(1, $request->input('is_huzaioki')) ) {
//                    if( $request->has('is_huzaioki') && $request->input('is_huzaioki') ) {
//                    	if($value == '') {
//                        	return $fail('「不在置きを了承する」場合は「その他コメント」に不在時の荷物の置き場所を記載して下さい。');
//                        }
//                    }
//                },
//            ],
            
            'pay_method' => [
            	'required', 
                function($attribute, $value, $fail) use($request) {
                    if ($value == 5 && $request->input('is_huzaioki')) 
                        return $fail('「お支払い方法：代金引換」は、不在置き了承時は選択出来ません。');
                },
            ],   
            'net_bank'=> 'required_if:pay_method,3',
            
            
//            'cardno' => 'required_if:pay_method,1|nullable|numeric',
//            'securitycode' => 'required_if:pay_method,1|nullable|digits_between:3,4|numeric',
//            'expire_year' => 'required_if:pay_method,1|nullable|numeric',
//            'expire_month' => 'required_if:pay_method,1|nullable|numeric',
            //'holdername' => 'required_if:pay_method,1',
            
        ];
        
        
        //会員新規登録時でのemailバリデーション
        if(! Ctm::isEnv('local')) {
            if($regist) {
                $rules['user.email'] = [
                    'filled',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->where(function ($query) {
                        return $query->where('active', 1); //uniqueする対象をactive:1のみにする
                    }),
                ];
            }
        }        
        
        
        //クレカの時のバリデーション
        if($request->input('pay_method') == 1) {
            if(! $request->has('card_seq') || ($request->has('card_seq') && $request->input('card_seq') == 99)) {
            
                $now = new DateTime();
                $ym = $now->format('ym');
                
                $expire = $request->input('expire_year') . $request->input('expire_month');
                        
                $rules['cardno'] = 'required|digits_between:10,16|numeric';
                $rules['securitycode'] = 'required|digits_between:3,4|numeric';
                $rules['expire_year'] = 'required';
                $rules['expire_month'] = 'required';

                $rules['expire'] = [
                    function($attribute, $value, $fail) use($ym, $expire) {
                        if ($expire < $ym) 
                            return $fail('「有効期限」は現在以降を指定して下さい。');
                    },
                ];
            }
                        
        }
        
         $messages = [
            //'title.required' => '「商品名」を入力して下さい。',
            'user.prefecture.not_in' => '「都道府県」を選択して下さい。',
            'destination.required_with' => '「配送先」を入力して下さい。', //登録先住所に配送の場合は「登録先住所に配送する」にチェックをして下さい。
            'pay_method.required' => '「お支払い方法」を選択して下さい。',
            'use_point.max' => '「ポイントを利用する」が保持ポイントを超えています。',
            'net_bank.required_if'=> '「お支払い方法」ネットバンク決済の銀行を選択して下さい。',
            'huzai_comment.required_if' => '「不在時の置き場所」は必須です。',
            'user_comment.max' => '「コメント」の文字数が長すぎます。',
            
//            'cardno.required_if' => '「カード番号」は必須です。',
//            'securitycode.required_if' => '「セキュリティコード」は必須です。',
//            'expire_year.required_if' => '「有効期限（年）」は必須です。',
//            'expire_month.required_if' => '「有効期限（月）」は必須です。',
        ];
        
        //$this->validate($request, $rules, $messages);
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect('shop/form')->withErrors($validator)->withInput();
        }
        
        
        $data = $request->all();
        
        
//        if(! Auth::check()) {
//            //Birth Input 月日全て入力で登録することにしているがどうか
//            if(! $data['user']['birth_year'] || ! $data['user']['birth_month'] || ! $data['user']['birth_day']) {
//                $data['user']['birth_year'] = 0;
//                $data['user']['birth_month'] = 0;
//                $data['user']['birth_day'] = 0;
//            }
//        }

		//クレカ登録のOn/Off: registしないの時は強制的に0にする。temp_is_regist_cardは表示用だけのsessionにする
        $data['temp_is_regist_card'] = isset($data['is_regist_card']) ? 1 : 0;
        $data['is_regist_card'] = isset($data['is_regist_card']) && (Auth::check() || $regist) ? 1 : 0;
                
        
        //itemのsessionをgetする
        $itemSes = session('item.data');
        //$regist = session('all.regist');

		//ここでall_priceをsessionから取得すると金額変動などあった時にsession維持でずれるので取得しない　この関数の中で入れ直す
        //$allPrice = session('all.all_price');
        
        
        //商品テーブル用のオブジェクト取得 -------------------------------
        $itemData = array();
        $addPoint = 0;
        $allPrice = 0;
        
        $isAmznPay = session('all.is_amzn_pay');
        $amznOrderReferenceId = isset($data['order_reference_id']) ? $data['order_reference_id'] : null;
        
        // AmazonPay ===================================================================
        if($isAmznPay) {
            $paymentObj = new Payment();
            
            $res = $paymentObj->getAmznDetail($amznOrderReferenceId);
            
            if(is_object($res)) { //エラーの時、Illuminate\Http\RedirectResponse オブジェクトが返るのでそれをreturnする。 get_class($res);
                return $res;
            }
            
            extract($res);
            
            $postNum = str_replace('-', '', $addInfo['PostalCode']);
            $telNum = str_replace('-', '', $addInfo['Phone']);
            
            $data['destination'] = 1;
            
            $data['receiver']['post_num'] = $postNum;
            $data['receiver']['prefecture'] = $addInfo['StateOrRegion'];
            $data['receiver']['address_1'] = $addInfo['AddressLine1'] . $addInfo['AddressLine2'];
            $data['receiver']['address_2'] = $addInfo['AddressLine3'];
            $data['receiver']['name'] = $addInfo['Name'];
            $data['receiver']['tel_num'] = $telNum;
            
            $data['user']['name'] = $userInfo['Name'];
            $data['user']['email'] = $userInfo['Email'];
            
            if($regist) {
                $data['user']['hurigana'] = '';
                $data['user']['post_num'] = $postNum;
                $data['user']['prefecture'] = $addInfo['StateOrRegion'];
                $data['user']['address_1'] = $addInfo['AddressLine1'] . $addInfo['AddressLine2'];
                $data['user']['address_2'] = $addInfo['AddressLine3'];
                $data['user']['tel_num'] = $telNum;
            }

        }
        
        //referenceIdをsessionに入れる session入れ amazonPayでない場合はnullがセットされる
        session([
            'all.order_reference_id'=>$amznOrderReferenceId,
        ]);
        // amznPay END ===========================


		//ユーザー(配送先)の都道府県NameとIdを取得
        if(isset($data['destination']) && $data['destination']) {
        	$prefName = $data['receiver']['prefecture'];
         	//$prefId = $this->prefecture->where('name', $prefName)->first()->id;   
        }
        else {
        	if(Auth::check()) {
        		$prefName = $this->user->find(Auth::id())->prefecture;
         	}
          	else {
                $prefName = $data['user']['prefecture'];
           }
        }
        
        //都道府県ID
        $prefId = $this->prefecture->where('name', $prefName)->first()->id;
        
        
        //全データ（$data）をsessionに入れる session入れ
        session([
            'all.data' => $data, //user receiver destination paymentMethod
            'all.regist' => $regist, //$registはこのメソッドの先頭で取得
        ]);
        //$request->session()->put('all.data', $data); //user receiver destination paymentMethod
        //$request->session()->put('all.user', $data['user']);
        //$request->session()->put('all.receiver', $data['receiver']);
        //$request->session()->put('user.data', $data['user']);
        //$request->session()->put('receiver.data', $data['receiver']);
        
        
        //Important ! ***************************************
        //既存Session新規データを追加　各データの修正をここでする。新規データをSessionに追加する
        
        $seinouSundayAllPrice = 0;
        $seinouHuzaiAllPrice = 0;
        
//        print_r($itemSes);
//        exit;
        
        foreach($itemSes as $key => $val) {
        	
            //西濃-加減算用の調整する金額
            $seinouSundayUpPrice = 0; 
            $seinouHuzaiDownPrice = 0; 
            
        	$obj = $this->item->find($val['item_id']);
            
            // 配送先が配送可能かどうかを確認するための配列をここで作る
			//$prefDeli[$obj->dg_id][] = $val['item_id'];
            
         	//カウント 個数  送料計算用
         	$obj->count = $val['item_count'];
          
            //同梱包割引の有無
            if(isset($val['is_once_down']))
                $obj->is_once_down = $val['is_once_down'];
                
            //西濃不在置きの有無
            if(isset($val['is_huzaioki']))
                $obj->is_huzaioki = $val['is_huzaioki'];
            
            //トータルプライス Singleの商品金額x個数の金額  
            $itemTotalPrice = $val['item_total_price'];
            
            //ポイント　ポイント加算
            $pointBack = $this->getPointBack($obj);
            $obj->point = ceil($val['item_total_price'] * $pointBack); //商品金額のみに対してのパーセント 切り上げ 切り捨て->floor()
			$addPoint += $obj->point;
            
            //Session入れ ポイント計算後
            //session(['item.data.'. $key . '.single_point' => $obj['point']]);
            
            
            //商品個別送料の計算
            $df = new Delifee([$obj], $prefId);
            $obj->single_deli_fee = $df->getDelifee();
            
            
            //配送希望時間
            if(isset($data['plan_time']) && isset($data['plan_time'][$obj->dg_id])) {
            	
                $obj->plan_time = $data['plan_time'][$obj->dg_id];
            }
            
            //西濃運輸の場合に、日曜配送は+1000加算、不在置き了承で商品から-3000
            if(isset($obj->is_huzaioki)) { //ORG : if($obj->dg_id == $this->seinouObj->id) {
            	
                if(Ctm::isSeinouSunday($data['plan_date'])) { //日曜配達プラス
                	/* 日曜1000円を送料として捉える場合 **************
                	$addDeliFee = $this->seinouSundayFee * $obj->count;
                    $obj->single_deli_fee += $addDeliFee;
                    $seinouSundayDeliFee += $addDeliFee; //最終的なトータルの送料計算用
                    **************************** */
                    
                    /* 日曜1000円を商品金額として捉える場合 ***** */
                    $seinouSundayUpPrice = $this->seinouObj->sundayFee * $obj->count;
                    $seinouSundayAllPrice += $seinouSundayUpPrice;
                    //$itemTotalPrice += $seinouSundayUpPrice;
                }
                
                //ORG : if(isset($data['is_huzaioki'][$obj->id])) { //if(isset($data['is_huzaioki'])) { //if(isset($obj->is_huzaioki)) {
                if($obj->is_huzaioki) { //不在置きマイナス
                    //item_total_price(商品金額x個数)をここでsessionに上書きするとズレが出るので、down_priceとして新データをsessionに入れる
                    $seinouHuzaiDownPrice = $this->seinouObj->huzaiokiFee * $obj->count;
                    $seinouHuzaiAllPrice += $seinouHuzaiDownPrice;
                    //$itemTotalPrice -= $seinouHuzaiDownPrice;
                }
                    
                    //$obj->is_huzaioki = $data['is_huzaioki'];
                //}
            }
            
            //トータルプライス Confirm表示用 sessionには入れ直さない 1246行目でSessionから取得する（これ以外にない）ので、Sessionに入れ直すとずれていく
            $obj->seinou_sunday_price = $seinouSundayUpPrice;
            $obj->seinou_huzai_price = $seinouHuzaiDownPrice;
            $obj->item_total_price = $itemTotalPrice;

			//allPrice加算
            $allPrice += $itemTotalPrice;

            //sessionに新しいデータを入れる。ここに上書きデータを入れてはいけない。reload等でループされるので。
            $itemSes[$key]['single_point'] = $obj->point;
            $itemSes[$key]['single_deli_fee'] = $obj->single_deli_fee;
            $itemSes[$key]['plan_time'] = isset($obj->plan_time) ? $obj->plan_time : null;
            
            $itemSes[$key]['seinou_sunday_up_price'] = $seinouSundayUpPrice;
            $itemSes[$key]['seinou_huzai_down_price'] = $seinouHuzaiDownPrice;
            
            //$itemSes[$key]['is_huzaioki'] = isset($obj->is_huzaioki) ? $obj->is_huzaioki : null;
            
            
            //送料計算用とConfirm画面表示用に使用するObjの配列
			$itemData[] = $obj; 
        }

//		print_r(session('item.data'));
//        exit;
        
        //Session入れ 新規データを追加したitemDataとAllPrice allPriceはここで計算された金額がsessionに入る =======================
        session([
        	'item.data'=>$itemSes,
            'all.all_price'=>$allPrice,
            'all.s_sunday_price' => $seinouSundayAllPrice,
            'all.s_huzai_price' => $seinouHuzaiAllPrice,
        ]);
        // ==============================================================================================
        
//        print_r(session('item.data'));
//        exit;
        
        

        //配送先都道府県への配送が可能かどうかを確認 -------------------------
        $df = new Delifee($itemData, $prefId); //CalcDelifeeController Init 
                
        $errorArr = $df->checkIsDelivery();
        
        /*
        $errorArr = array();
        
        foreach($prefDeli as $dgKey => $item_ids) {
            
            $prefFee = $this->dgRel->where(['dg_id'=>$dgKey, 'pref_id'=>$prefId])->first()->fee;
	
            if($prefFee == '99999' || $prefFee === null) {            	
                foreach($item_ids as $item_id) {
                	$title = $this->item->find($item_id)->title;
                    $errorArr['no_delivery'][] = '「'. $title .'」の商品の'. $prefName .'への配送は不可です。';
                }
                
                //$noDeliPref = 0;
            }
        }
        */
        
        if(count($errorArr) > 0) { //配送不可ならリダイレクト リダイレクトを別クラスに入れるとおかしくなるのでここに
        	return redirect('shop/form')->withErrors($errorArr)->withInput();
        }
        

        //手数料、送料、ポイントをここで合計する -------------------------
        //allPrice -> 商品金額x個数=商品金額の合計（同梱包割引、不在置き割引含む）、totalFee ->送料等全て含めた総合金額
        $totalFee = 0;
        
        //西濃日曜配達追加 （不在置きは元々商品金額として計算されているので日曜配達分のみプラス）---------
        $totalFee = $allPrice + $seinouSundayAllPrice/* - $seinouHuzaiAllPrice*/;
        
        //ポイント 使用ポイント減算-----------
        $usePoint = $data['use_point'];
        $totalFee = $totalFee - $usePoint;
        //ポイント END-----------
        
        //送料 --------------
        $deliFee = $df->getDelifee();
        
        // Seinou Correct **************************
//        if($seinouSundayDeliFee) { //西濃の日曜配達なら
//        	$deliFee = $deliFee + $seinouSundayDeliFee;
//        }
        
        $totalFee = $totalFee + $deliFee;
        //送料END -----------------
        
        
        $codFee = 0;
        $taxPer = $this->set->tax_per / 100;
        
        $errors = array();
        
        //コンビニ手数料 --------------
        if($data['pay_method'] == 2) { 
        	//https://www.epsilon.jp/pricelist/com_conv.html
        	
            //$taxPer = $this->set->tax_per / 100;
            
         	if($totalFee <= 1999) {
            	$epTesu = 130;
          		$codFee = ceil(($epTesu * $taxPer) + $epTesu);
          	}
           	elseif ($totalFee >= 2000 && $totalFee <= 2999) {
            	$epTesu = 150;
          		$codFee = ceil(($epTesu * $taxPer) + $epTesu);
            }
            elseif ($totalFee >= 3000 && $totalFee <= 4999) {
            	$epTesu = 180;
          		$codFee = ceil(($epTesu * $taxPer) + $epTesu);
            }
            elseif ($totalFee >= 5000) {
            	$epTesu = $totalFee * 0.04;
            	$codFee = ceil(($epTesu * $taxPer) + $epTesu);
            }
            
            //コンビニ上限額
            if( ($totalFee + $codFee) > 300000) {
            	$errors['konbiniLimit'] = 'コンビニ決済の上限額30万円を超えています。';
                
            }
                   
        }
        
        //NP後払い手数料 -> 一律205 （190 + 税）切り捨て関数必要 => 手数料のみ205円の非課税となる なので手数料のみは税計算をしない ----------------
        else if($data['pay_method'] == 4) {
        	
        	$codFee = 205; //ORG:190
            $codMax = 50000;
            
            //$codFee += floor($codFee * $taxPer);
            $codMax += $codMax * $taxPer; //後払い上限金額の税計算
                        
            //NP後払い上限額
            if( ($totalFee + $codFee) > $codMax) {
                //confirmページにエラーを表示させる場合
            	$errors['gmoLimit'] = 'NP後払い決済の上限額'. number_format($codMax) .'円を超えています。';
                
                //formページに戻す場合
//                return redirect('shop/form')->withErrors([
//                    'pay_method'=>'NP後払い決済の上限額'. number_format($codMax) .'円を超えています。',
//                ])->withInput();
            }
        }
        
        //代引き手数料 -> 送料、ポイント利用含めたトータル金額に対して-----------
        else if($data['pay_method'] == 5) { 
        	
            $codFee = Ctm::daibikiCodFee($totalFee);
/*            
         	if($totalFee <= 10000) {
          		$codFee = 324;
          	}
           	elseif ($totalFee >= 10001 && $totalFee <= 30000) {
            	$codFee = 432;
            }
            elseif ($totalFee >= 30001 && $totalFee <= 100000) {
            	$codFee = 648;
            }
            elseif ($totalFee >= 100001 && $totalFee <= 300000) {
            	$codFee = 1080;
            }
            elseif ($totalFee >= 300001 && $totalFee <= 500000) {
                $codFee = 2160;
            }
            elseif ($totalFee >= 500001 && $totalFee <= 1000000) {
                $codFee = 3240;
            }
            elseif ($totalFee >= 1000001 && $totalFee <= 999999999) {
                $codFee = 4320;
            }
*/
        }
        
        $totalFee = $totalFee + $codFee;
        //代引き END ---------------------
        
        
        //送料、手数料、ポイントのsession入れ *********************
        //allPrice -> 商品金額x個数=商品金額の合計（同梱包割引、不在置き割引含む）、totalFee ->送料等全て含めた総合金額
        session([
            'all.total_fee'=>$totalFee,
            'all.deli_fee'=>$deliFee,
            'all.cod_fee'=>$codFee,
            'all.use_point'=>$usePoint,
            'all.add_point'=>$addPoint,
            //'all.take_charge_fee'=>$takeChargeFee,
        ]);

        
        // Settle 決済 ====================================================
        $title = $itemData[0]->title; //購入１個目の商品をタイトルにする。これ以外なさそう。
        $number = $itemData[0]->number;
        
        //注文番号作成 Order_Number ==========================
        //Amazonエラーから再送信の時のみsessionのorderNumを再利用する
        $orderNum = isset($data['ref_id_error_back']) ? session('all.order_number') : Ctm::getOrderNum(11);
        
        
        //UserInfo ========================================
        if(isset($data['user'])) { //Authでなければ$data['user']にデータが入る
        	//$user_id = Ctm::getOrderNum(15);
        	$user_name = $data['user']['name'];
         	$user_email = $data['user']['email'];   
        }
        else {
        	$u = $this->user->find(Auth::id());
        	
            //$user_id = isset($u->gmo_id) ? $u->gmo_id : Ctm::getOrderNum(15);
        	$user_name = $u->name;
            $user_email = $u->email;
        }
        
        $settles = array();
        $actionUrl = '';
        
        if($data['pay_method'] == 1) {
            $actionUrl = url('shop/paydo');
        }
        elseif($data['pay_method'] == 7) {
            if(isset($data['ref_id_error_back'])) { //Authorizeエラーから再度支払い方法を選択し直した時
                $actionUrl = url('shop/amznpay-retry');
            }
            else {
                $actionUrl = url('shop/amznpay');
            }
        }
        else {
            $actionUrl = url('shop/thankyou');
        }
        

/*        
//        $payCode = 0;
//        if($data['pay_method'] == 1) { //クレカ
//        	$payCode = '10000-0000-00000-00000-00000-00000-00000';
//        }
//        elseif($data['pay_method'] == 2) { //コンビニ
//        	$payCode = '00100-0000-00000-00000-00000-00000-00000';
//        }
//        elseif($data['pay_method'] == 3) { // ネットバンク
//        	if($data['net_bank'] == 1) {
//            	$payCode = '00010-0000-00000-00000-00000-00000-00000';
//            }
//            elseif($data['net_bank'] == 2) {
//            	$payCode = '00001-0000-00000-00000-00000-00000-00000';
//            }
//            elseif($data['net_bank'] == 3) {
//            	$payCode = '00000-0000-00000-00100-00000-00000-00000';
//            }
//        }
//        elseif($data['pay_method'] == 4) { //後払い
//            $payCode = '00000-0000-00000-00010-00000-00000-00000';
//        }
//        elseif($data['pay_method'] == 4) { //代引き
//            $payCode = '10000-0000-00000-00000-00000-00000-00000'; // ???
//        }
*/
        
        //クレカ情報
        $cardInfo = array();
        if($data['pay_method'] == 1) {
            $cardInfo['cardno'] = $data['cardno'];
            $cardInfo['securitycode'] = $data['securitycode'];
            $cardInfo['expire_year'] = $data['expire_year'];
            $cardInfo['expire_month'] = $data['expire_month'];
            //$cardInfo['holdername'] = $data['holdername'];
            //$cardInfo['tokennumber'] = $data['tokennumber'];
        }
        
        //$settles['ShopID'] = 'tshop00036826';
        //$settles['ShopPass'] = 'bgx3a3xf';
        
        $settles['OrderID'] = $orderNum;
        //$settles['JobCd'] = 'CAPTURE';
        $settles['Amount'] = $totalFee;
        //$settles['CardSeq'] = $data['card_seq'];
        //$settles['ItemCode'] = $number; //省略推奨
/*        
//        $settles['user_id'] = $user_id;
//        $settles['user_name'] = $user_name;
//        $settles['user_mail_add'] = $user_email;
//        
//        $settles['item_name'] = $title;
//        
//        $settles['st_code'] = $payCode;
//        $settles['mission_code'] = 1;
//        $settles['item_price'] = $totalFee;
//        $settles['process_code'] = 1;
//        $settles['memo1'] = '';
//        $settles['xml'] = 1; //1回postで送信し、結果がxmlで返り、正常ならepsilonへリダイレクトするという仕様
//        $settles['lang_id'] = 'ja';
//        //$settles['page_type'] = 12;
////        $settles['version'] = 2;
//        $settles['character_code'] = 'UTF8';
*/        
        //注文番号のsession入れ
        session(['all.order_number'=>$settles['OrderID']]);
        
        $payMethod = $this->payMethod;
        $pmChild = $this->payMethodChild;
        
        $userArr = '';
        if(Auth::check()) {
        	$userArr = $this->user->find(Auth::id());
        }
        else {
        	$userArr = $data['user'];
        }
        
        
        $metaTitle = 'ご注文内容の確認' . '｜植木買うならグリーンロケット';
        
        
        return view('cart.confirm', ['data'=>$data, 'userArr'=>$userArr, 'itemData'=>$itemData, 'regist'=>$regist, 'totalFee'=>$totalFee, 'allPrice'=>$allPrice, 'settles'=>$settles, 'payMethod'=>$payMethod, 'pmChild'=>$pmChild, 'deliFee'=>$deliFee, 'codFee'=>$codFee, 'usePoint'=>$usePoint, 'addPoint'=>$addPoint, 'seinouSundayAllPrice'=>$seinouSundayAllPrice, 'seinouHuzaiAllPrice'=>$seinouHuzaiAllPrice,  'actionUrl'=>$actionUrl, 'cardInfo'=>$cardInfo, 'isAmznPay'=>$isAmznPay, 'metaTitle'=>$metaTitle])->withErrors($errors);
    }
    
    
    public function postForm(Request $request)
    {
//       print_r($request->all());
//       exit;
//         print_r(session('all'));   
//         exit;

		
		//カードトークン取得でエラーが返った時 or 決済実行でカード情報エラーの時 getで?carderr=122を付ける
        $cardErrors = array();
        //$refId = null;
        
        if($request->has('carderr') && $request->input('carderr')) {
        	
            if($request->input('carderr') == 1000) { //決済を実行してカードに問題がある時ここにエラーコード1000でリダイレクトさせている
            	$errInfo = session()->has('ErrInfo') ? session('ErrInfo') : '';
                $errText = 'カード情報が正しくないか、お取り扱いができません。';
                //Local時のみエラーコード
                if(Ctm::isEnv('local')) {
                	$errText .= $errInfo;
                }
            	
                $cardErrors['carderr'] = $errText;
            }
            else {
        		$cardErrors['carderr'] = 'カード情報が正しくありません。';
            }
        }
        
        // AmznPayでのエラー処理
        if($request->has('amznerr')) {
            
            if($request->input('amznerr') == 1000) { //amznPayを実行して問題がある時ここにエラーコード1000でリダイレクトさせている
                $errInfo = session()->has('ErrInfo') ? session('ErrInfo') : '';
                $refId = session()->has('refIdFromAuth') ? session('refIdFromAuth') : '';
                
                $errText = $refId != '' ?
                    'Amazonでのお支払い方法に問題があるようです。' :
                    'Amazonご登録情報の取得に失敗しました。' ;
                
                $errText .= '<a href="/shop/cart" class="text-primary">カートに戻り</a>再度やり直すか、別のお支払い方法を選択して下さい。';
                
                //Local時のみエラーコード
                if(Ctm::isEnv('local')) {
                    $errText .= $errInfo . $refId;
                }
                
                $cardErrors['carderr'] = $errText;
            }
            else {
                $cardErrors['carderr'] = 'Amazonの情報が正しくありません。';
            }
        }
        
        //商品金額(同梱包割引・不在置き計算済み) x 個数　の金額を入れる箱 ==============
        $allPrice = 0;
        // ==============================
        
      	if($request->has('from_cart') || session()->has('from_login')) { //cartからpostで来た時 or loginから認証後redirectで来た時
       		
            if($request->has('from_cart')) {
                $data = $request->all();
            }
            elseif(session()->has('from_login')) {
                $data = session('cart_data_from_login');
                
                //ログインによりセットしたSessionは消す
                $request->session()->forget('from_login');
                $request->session()->forget('cart_data_from_login');
            }
            else {
                abort(404);
            }
            
            //AmazonPayの有無
            $isAmznPay = isset($data['is_amzn_pay']) ? $data['is_amzn_pay'] : 0;
            $accessToken = $data['access_token'];
            //orderRefferenceIdはフォーム表示時にjsで取得されるのでここでは無し
            
            /* registのボタン分けを無くした
            $regist = $request->has('regist_on') ? 1 : 0;
         	$request->session()->put('all.regist', $regist); //session入れ
          	*/
            
//            print_r(session('item.data'));
//            exit;
            
            //場所を下記foreachの下に移動：session(['all.from_cart'=>$request->input('from_cart')]); //session入れ
            $soldOutAr = array();
            
           	foreach($data['last_item_count'] as $key => $val) {   
            	$request->session()->put('item.data.'.$key.'.item_count', $val); //session入れ 
             
             	//個数 * 値段の再計算（再計算を押さずに購入手続きした時）
              	$itemId = $data['last_item_id'][$key];
            
                $itemObj = $this->item->find($itemId);
                
                if(! $itemObj->stock) {
                    $soldOutAr['last_item_count.'.$key][] = '売切れ商品です。カートから削除して進んで下さい。';
                }
                
                //同梱包値引きSwitchをitemObjに入れる
                if(session('item.data.'.$key.'.is_once_down') !== null) {
                    $itemObj->is_once_down = session('item.data.'.$key.'.is_once_down');
                }
                
                //西濃不在置きSwitchをitemObjに入れる
                if(session('item.data.'.$key.'.is_huzaioki') !== null) {
                    $itemObj->is_huzaioki = session('item.data.'.$key.'.is_huzaioki');
                }
                
                $lastPrice = $this->getItemPrice($itemObj); //セールならセール金額　通常なら通常金額 1円の時のSale計算は矛盾が出るので除外
//                echo $lastPrice;
//                exit;
                
                $lastPrice = $lastPrice * $val;
                
            	$request->session()->put('item.data.'.$key.'.item_total_price', $lastPrice); //session入れ
             
                $allPrice += $lastPrice;
            }
            
            if(count($soldOutAr) > 0) {
                //print_r($soldOutAr); exit;
                return redirect('shop/cart')->withErrors($soldOutAr)->withInput();
            }
            
            //form_cartのsession入れ 一度はpostを通っていることを判別する
            //session(['all.from_cart'=>$request->input('from_cart')]); //session入れ
            
             //session入れ ---------
            session([
                'all.from_cart' => 1,
                'all.is_amzn_pay' => $isAmznPay,
                'all.access_token' => $accessToken,
                //orderRefferenceIdはフォーム表示時にjsで取得されるのでここでは無し
            ]);
            //all priceのsession入れ
            $request->session()->put('all.all_price', $allPrice);
       	}
        else { //getの時（他ページからの移動）
        	//if($request->session()->has('all.regist')) {
         	//	$regist = session('all.regist');
         	//}
            
            if(! $request->session()->has('all.from_cart')) {
           		abort(404);
           	}
            else {
                $allPrice = session('all.all_price');
                $isAmznPay = session('all.is_amzn_pay');
            }
        }
        
     
     	//PayMethod
      	$payMethod = $this->payMethod->where('active', 1)->get();
        
        //PayMethodChild
      	$pmChilds = $this->payMethodChild->all();
       
       	//Prefecture
        $prefs = $this->prefecture->all();      
       	
        //User   
        $userObj = null;
        $regCardDatas = array();
        $regCardErrors = null;
        
        $deliFee = null;
        $prefName = null;
        
        //itemDataをSessionから取得
        $sesItems = session('item.data');
                
        if(Auth::check()) {
        	$userObj = $this->user->find(Auth::id());
         
            //送料関連 -----------------------------------
            $itemObjs = array();
            foreach($sesItems as $sesItem) {
                $i = $this->item->find($sesItem['item_id']);
                $i->count = $sesItem['item_count'];
                $itemObjs[] = $i;
            }
            
            $prefId = $this->prefecture->where('name', $userObj->prefecture)->first()->id;
            $df = new Delifee($itemObjs, $prefId);
            
            $deliFee = $df->getDelifee();
            $prefName = $userObj->prefecture;


            //クレカ関連 ----------------------------------
            //クレカ参照
            if(isset($userObj->member_id) && $userObj->card_regist_count) {
            	//論理モード: 削除カード取得されない 同じカード番号でも新規登録になる　
                //物理モード: 削除カードも取得される　同じカード番号なら更新になる（期限など）
                $cardDatas = [
                    'SiteID' => $this->gmoId['siteId'],
                    'SitePass' => $this->gmoId['sitePass'],
                    'MemberID' => $userObj->member_id,
                    'SeqMode' => 0, //shopping中はCardSeqがずれることはないので論理で
                ];
                
                $cardResponse = Ctm::cUrlFunc("SearchCard.idPass", $cardDatas);
                
//                echo $cardResponse;
//                exit;
            	
                //正常：CardSeq=0|1|2|3|4&DefaultFlag=0|0|0|0|0&CardName=||||&CardNo=*************111|*************111|*************111|*************111|*************111&Expire=1905|1904|1908|1907|1910&HolderName=||||&DeleteFlag=0|0|0|0|0
                $cardArr = explode('&', $cardResponse);
                                
                foreach($cardArr as $res) {
                    $arr = explode('=', $res);
                    $regCardDatas[$arr[0]] = explode('|', $arr[1]);
                }
                
//                print_r($regCardDatas);
//                exit;
                
                //$userRegResponse Error処理をここに ***********
                //Local時のみエラーコード
                if(array_key_exists('ErrCode', $regCardDatas)) {
                	$regCardErrors = '[5101-';
                    $regCardErrors .= implode('|', $regCardDatas['ErrInfo']);
                	$regCardErrors .= ']';
                }
				
//                print_r($regCardErrors);
//                exit;
                
            }
            
        } 
        

        //上記でセットしたsession->item.dataから各種情報を取得する ---------------------------------------------------
        
        //代引きが可能かどうかを判定してboolを渡す 代引き可／不可混在の場合は「可能」となり、不在置きが一つでもあれば「不可」となる。
        $codCheck = 0;
        
        foreach($sesItems as $item) {
        	$cod = $this->item->find($item['item_id'])->cod;
            
            if(isset($item['is_huzaioki']) && $item['is_huzaioki']) {
                $codCheck = 0;
                break;
            }
            elseif($cod) {
          		$codCheck = 1;
            	//break;
          	}
        }
        
        //時間指定の選択肢　西濃運輸の不在置きチェック
        $dgGroup = array();
        $seinouHuzaiSes = array();
        $seinouNoHuzaiSes = array();
        
        foreach($sesItems as $item) {
            //if(isset($item->is_huzaioki)) echo $item->is_huzaioki;
            
        	$dgId = $this->item->find($item['item_id'])->dg_id;
            
            // Seinou Correct **************************
            //西濃なら
            //if($dgId == $this->seinouObj->id) {
            if(isset($item['is_huzaioki'])) { //不在置き可不可商品なら、1 or 0がセットされている
                if($item['is_huzaioki'])
                    $seinouHuzaiSes[] = $item;
                else
                    $seinouNoHuzaiSes[] = $item;
            	//$dgSeinou[] = $item['item_id'];
            }
            else {
                //時間指定可能なら
                if($this->dg->find($dgId)->is_time) {
                    $dgGroup[$dgId][] = $item['item_id'];
                }
                else {
                    $dgGroup[0][] = $item['item_id'];
                }
            }
            
        }
        
//        print_r($dgGroup);
//        exit;

		$metaTitle = 'ご注文情報の入力' . '｜植木買うならグリーンロケット';
     
     	return view('cart.form', ['allPrice'=>$allPrice, 'payMethod'=>$payMethod, 'pmChilds'=>$pmChilds, 'prefs'=>$prefs, 'userObj'=>$userObj, 'deliFee'=>$deliFee, 'prefName'=>$prefName, 'codCheck'=>$codCheck, 'dgGroup'=>$dgGroup, 'seinouHuzaiSes'=>$seinouHuzaiSes, 'seinouNoHuzaiSes'=>$seinouNoHuzaiSes, 'regCardDatas'=>$regCardDatas, 'regCardErrors'=>$regCardErrors, 'cardErrors'=>$cardErrors, 'isAmznPay'=>$isAmznPay, 'metaTitle'=>$metaTitle]);
    }
    
    
    public function postCart(Request $request)
    {
        $itemData = array();
        $itemIds = null;
        $allPrice = 0;
        $prefs = $this->prefecture->all();
        
        /* ********************************
            => 更新ボタン（青）、送料計算ボタン、ログインして進むボタン（黒）のpostはここを通る(ここに戻る)
            => 購入手続きへ進むボタンのみ<button>にform-action=""を設定してshop/formへ進むようにしている
        ************************************
        */
        
        // 送料計算ボタンの時のバリデーション $request->has('delifee_calc')は現在使用していない ---------------------
        //現在、バリデーションは使用しないことにした。未選択であれば「含まれておりません」となる。
        /*
        if($request->has('re_calc') ) { // && ! $request->input('pref_id')
        	//return redirect('shop/cart')->withErrors(['pref_id'=>'選択して下さい'])->withInput();
        	$rules = [
                'pref_id' => [
                    function($attribute, $value, $fail) use($request) {
                        if(! $value) {
                            return $fail('選択して下さい');
                        }
                    },
                ],
            ];
        
        	$this->validate($request, $rules);
        }
        */
        
        //ログインして手続きへ　黒ボタンから来た時 input dataは渡せないのでSessionに入れる -----------------------------
        if($request->has('from_login')) {
            $data = $request->all();
            $request->session()->put('cart_data_from_login', $data);

            return redirect('login?to_cart=1');
        }
        

//        echo date('Y/m/d', '2018-04-01 12:57:30');
//        exit;
//        $request->session()->forget('item.data');
//        $request->session()->forget('all');
        
        if($request->has('from_item')) { //singleからのpostの時、request dataをsessionに入れる
            $datas = $request->all();
            
//            print_r($datas);
//            exit;
            
            foreach($datas['item_id'] as $k => $v) {
            	
                if($datas['item_count'][$k] == 0) {
                	continue;
                }
                
            	$data = array();
                
            	$data['_token'] = $datas['_token'];
                $data['item_count'] = $datas['item_count'][$k];
                $data['from_item'] = $datas['from_item'];
            	$data['item_id'] = $v;
                $data['uri'] = $datas['uri'];
                
                if(isset($datas['is_huzaioki']))
                    $data['is_huzaioki'] = $datas['is_huzaioki'];
            
                //---------------------------------------------
                if($request->session()->has('item.data')) { //一度カートに入れ、別商品を再度カートに入れた時
                    
                    if(! in_array($data, session('item.data'))) {
    //                     print_r($data);
//                         print_r(session('item.data'));
//                        exit;
                        
                        //同梱包値引きの判別 既にある商品と比較する
                        $thisI = $this->item->find($data['item_id']); //postで入ってきた商品
                        $ss = session('item.data'); //カートにある既存商品

                        foreach($ss as $ssKey => $ssVal) {
                            $i = $this->item->find($ssVal['item_id']); //既にカートにある商品
                            
                            //if(! isset($thisI->sale_price)) { //sale_priceが一番優先なのでこれがセットされていない時に進行
                                //['consignor_id'=>$item->consignor_id, 'is_once'=>1, 'is_once_recom'=>0]
                                if($thisI->consignor_id == $i->consignor_id  && $thisI->is_once && $i->is_once) { /* && isset($thisI->once_price) */ //既にカートにある商品にonce_priceが設定されていなくてもdg_idが同じであれば進める
                                    
                                    if(! isset($ssVal['is_once_down']) || ! $ssVal['is_once_down']) {
                                        $randomNum = Ctm::getOrderNum(5);
                                        $ss[$ssKey]['is_once_down'] = $randomNum;
                                    }
                                    else {
                                        $randomNum = $ss[$ssKey]['is_once_down'];
                                    }
                                    
                                    $data['is_once_down'] = $randomNum;
                                    
//                                    if(! isset($i->sale_price) && $i->is_once && isset($i->once_price)) {
//                                        $ss[$ssKey]['is_once_down'] = $ssVal['item_id'];
//                                    }
                                    
                                    break;
                                }
                            //}
                        }
                        
                        $ss[] = $data;
                        $request->session()->put('item.data', $ss); //session入れ
                        
                        
//                        print_r(session('item.data')); //session item.data.0.is_once_down
//                        exit;
                        
                        //ORG ---
                        //$request->session()->push('item.data', $data);
   
                     }   
                }
                else { //初カートの時
    //                echo "bbb";
    //                print_r(session('item.data'));
    //                exit;
                    $request->session()->push('item.data', $data); //session入れ
                }
            
            }
            
//            print_r(session('item.data'));
//            exit;
            
            $request->session()->put('org_url', $datas['uri']);
        }

        
        $submit = 0;
        $reCalc = 0;
        $deliFee = null;
        $prefId = null;
            
        //再計算の時
        if($request->has('re_calc') /*|| $request->has('delifee_calc') */|| $request->has('del_item_key')) {
            $data = $request->all();
            $submit = 1;
            $prefId = isset($data['pref_id']) ? $data['pref_id'] : 0;
            //print_r($secData);
            //exit;
        }
        elseif(Auth::check()) { //ログイン済みの時（送料表示のため）
            $u = $this->user->find(Auth::id());
            $prefId = $this->prefecture->where('name', $u->prefecture)->first()->id;
        }
        
        //削除の時
        if($request->has('del_item_key')) {
            
            $delKey = $data['del_item_key'];
        	
            //if(isset($data['last_item_count'][$data['del_item_key']])) {
        		unset($data['last_item_count'][$delKey]);
            	$data['last_item_count'] = array_values($data['last_item_count']);
            //}
            
            
            $delItem = session('item.data.'. $delKey);
            $isOnceNum = isset($delItem['is_once_down']) ? $delItem['is_once_down'] : null; //削除するis_once_downのrandom keyを取得
            
            //del itemをsesionから消す
            $request->session()->forget('item.data.'. $delKey);
            
            //同梱包割引確認 =========
            if(isset($isOnceNum)) {
                $restSess = session('item.data');
                $restKeyArr = array();
                
                //is_once_downのrandom keyが同じであればその対象のitemのkeyを配列に入れる
                foreach($restSess as $restKey => $restItem) {
                    if(isset($restItem['is_once_down']) && $restItem['is_once_down'] == $isOnceNum) {
                        $restKeyArr[] = $restKey;
                    }
                }
                
                if(count($restKeyArr) == 1) { //残りが1つの時のみが同梱包割引解除になるので、その残りのitemからsession->forget()する
                    $request->session()->forget('item.data.'. $restKeyArr[0] . '.is_once_down');
                }
                
                //print_r($data);
                //print_r(session('item.data'));
                //exit;
            }
            //同梱包割引確認 END =========
            
            
            //sesionから消す
            //$request->session()->forget('item.data.'. $delKey);
            
            //Keyの連番を振り直してsessionに入れ直す session入れ
            $reData = array_merge(session('item.data'));
            $request->session()->put('item.data', $reData);
        }
        
        //itemのsessionがある時　なければスルーして$itemDataを空で渡す sessionがない時->カートが空の時だったか
        if( $request->session()->has('item.data') ) {
            $itemSes = session('item.data');
//            print_r($itemSes);
//             exit;
                
            //sessionからobjectを取得して配列に入れる
            foreach($itemSes as $key => $val) {
                $obj = $this->item->find($val['item_id']);
                
                if(isset($val['is_once_down']))
                    $obj['is_once_down'] = $val['is_once_down'];
                
                
                if(isset($val['is_huzaioki']))
                    $obj['is_huzaioki'] = $val['is_huzaioki'];
                
                if($submit) { //再計算の時
                	$obj['count'] = $data['last_item_count'][$key];
                    $request->session()->put('item.data.'.$key.'.item_count', $obj['count']);
                }
                else {
                	$obj['count'] = $val['item_count'];	
                } 
                
                //値段 * 個数
                $itemPrice = $this->getItemPrice($obj); //セールならセール金額　is_once_downなら同梱包金額 通常なら通常金額
                $obj['total_price'] = $itemPrice * $obj['count'];
                //$request->session()->put('item.data.'.$key.'.item_total_price', $obj['item_total_price']); //session入れ
                /*
                $isSale = $this->setting->get()->first()->is_sale;
                
                if(isset($obj->sale_price)) {
                	$total = Ctm::getPriceWithTax($obj->sale_price);
                }
                else {
                    if($isSale) {
                        $total = Ctm::getSalePriceWithTax($obj->price);
                    }
                    else {
                        $total = Ctm::getPriceWithTax($obj->price);
                    }
                }
                */
                
                //itemIds 下段 最近チェックしたアイテムの取得に使用する
                $itemIds[] = $obj->id;
				
                //合計金額を算出
				$allPrice += $obj['total_price'];		
                
                //下段送料とViewへ渡す用に使用する
                $itemData[] = $obj;       
            }
            /************
            $request->session()->put('all.all_price', $allPrice);
            *************/
            
            //$itemDataはitemのobjに[count]が入ったものの配列
            
            
			// 送料計算 ===========================
            if($prefId && (isset($data['re_calc']) || Auth::check()) ) {

                if(isset($data['re_calc'])) {
                    $reCalc = 1;
                }
                
                $df = new Delifee($itemData, $prefId);
                
                //配送先都道府県への配送が可能かどうかを確認 -------------------------
                $errorArr = $df->checkIsDelivery();
               
                if(count($errorArr) > 0) { //配送不可ならリダイレクト リダイレクトを別クラスに入れるとおかしくなるのでここに
                    return redirect('shop/cart')->withErrors($errorArr)->withInput();
                }
                
                $deliFee = $df->getDelifee();
  
            }
            
                        
            //合計金額を算出
//            $priceArr = collect($itemData)->map(function($item) use($allPrice) {
//                return $item->total_price; 
//            })->all();
//            
//            $allPrice = array_sum($priceArr);
        }
        
        
        //Recent Cookie 最近チェックしたアイテム　最近見た CacheではなくCookieなので注意===================
        $cookieArr = array();
        $cookieItems = null;
        
        if(isset($itemIds)) { //itemIdsがnullの時=>カートに商品がない時
            $getNum = Ctm::isAgent('sp') ? 6 : 4;
            $chunkNum = Ctm::isAgent('sp') ? $getNum/2 : $getNum;
            
            $whereArr = ['open_status'=>1, ['pot_type', '<', 3]];
            
            //カートに入るのは子ポットID、Coookieにあるのは親IDなので、$itemIdsを親ポットIDにしてセットし直す
            foreach($itemIds as $idKey => $itemId) {
                $i = $this->item->find($itemId);
                if($i->pot_type == 3) {
                    $itemIds[$idKey] = $i->pot_parent_id;
                }
            }
            
            //Cookie取得
            $cookieIds = Cookie::get('item_ids');
           
            if(isset($cookieIds) && $cookieIds != '') {
               $cookieArr = explode(',', $cookieIds); //orderByRowに渡すものはString
               $cookieItems = $this->item->whereIn('id', $cookieArr)->where($whereArr)->whereNotIn('id', $itemIds)->orderByRaw("FIELD(id, $cookieIds)")->take($getNum)->get()->chunk($chunkNum);
            }
        }
        
        $metaTitle = '買い物カゴの確認' . '｜植木買うならグリーンロケット'; 
        
        return view('cart.index', ['itemData'=>$itemData, 'allPrice'=>$allPrice, 'uri'=>session('org_url'), 'prefs'=>$prefs, 'prefId'=>$prefId, 'deliFee'=>$deliFee, 'reCalc'=>$reCalc, 'cookieItems'=>$cookieItems, 'metaTitle'=>$metaTitle]);

/*
        //$tax_per = $this->set->tax_per;
//        print_r($itemSes);
//        exit;
//        
//        if ($request->session()->exists('item_data')) {
//            $itemSes = session('item_data');
//                
//        }
//           $request->session()->put('item_data', $data);
//        $ses = $request->session()->all();
//        
//        print_r($ses);
//        exit;

//         if($request->has('regist_on') || $request->has('regist_off')) {
//             $regist = $request->has('regist_on') ? 1 : 0;
//              $payMethod = $this->payMethod->all();   
//             return view('cart.form', ['itemData'=>$itemData, 'regist'=>$regist, 'allPrice'=>$allPrice, 'payMethod'=>$payMethod, ]);
//         }
//         else {
//            return view('cart.index', ['itemData'=>$itemData, 'allPrice'=>$allPrice, 'uri'=>session('org_url') ]);
//        }
        
//        //status
//        if(isset($data['open_status'])) { //非公開On
//            $data['open_status'] = 0;
//        }
//        else {
//            $data['open_status'] = 1;
//        }
//        
//        //stock_show
//        $data['stock_show'] = isset($data['stock_show']) ? 1 : 0;
//        
//        
//        if($editId) { //update（編集）の時
//            $status = '商品が更新されました！';
//            $item = $this->item->find($editId);
//        }
//        else { //新規追加の時
//            $status = '商品が追加されました！';
//            //$data['model_id'] = 1;
//            
//            $item = $this->item;
//        }
//        
//        $item->fill($data);
//        $item->save();
//        $itemId = $item->id;
//        
////        print_r($data['main_img']);
////        exit;
//        
//        //Main-img
//        if(isset($data['main_img'])) {
//                
//            //$filename = $request->file('main_img')->getClientOriginalName();
//            $filename = $data['main_img']->getClientOriginalName();
//            $filename = str_replace(' ', '_', $filename);
//            
//            //$aId = $editId ? $editId : $rand;
//            //$pre = time() . '-';
//            $filename = 'item/' . $itemId . '/thumbnail/' . $filename;
//            //if (App::environment('local'))
//            $path = $data['main_img']->storeAs('public', $filename);
//            //else
//            //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
//            //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
//            
//            $item->main_img = $path;
//            $item->save();
//        }
//        
//        //Spare-img
//        if(isset($data['spare_img'])) {
//            $spares = $data['spare_img'];
//            
////            print_r($spares);
////            exit;
//            
//            foreach($spares as $key => $spare) {
//                if($spare != '') {
//            
//                    $filename = $spare->getClientOriginalName();
//                    $filename = str_replace(' ', '_', $filename);
//                    
//                    //$aId = $editId ? $editId : $rand;
//                    //$pre = time() . '-';
//                    $filename = 'item/' . $itemId . '/thumbnail/' . $filename;
//                    //if (App::environment('local'))
//                    $path = $spare->storeAs('public', $filename);
//                    //else
//                    //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
//                    //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
//                    
//                    //$item->spare_img .'_'. $ii = $path;
//                    $item['spare_img_'. $key] = $path;
//                    $item->save();
//                }
//
//            }
//        }
//        
//        //spare画像の削除
//        if(isset($data['del_spareimg'])) {
//            $dels = $data['del_spareimg'];     
//             
//              foreach($dels as $key => $del) {
//                   if($del) {
//                     $imgName = $item['spare_img_'. $key];
//                       if($imgName != '') {
//                         Storage::delete($imgName);
//                     }
//                    
//                       $item['spare_img_'. $key] = '';
//                    $item->save();
//                 }   
//           }
//        }
//        
//
    
        //return view('cart.index', ['data'=>$data ]);
*/

    }
    


    public function postScript(Request $request)
    {
        $cate_id = $request->input('selectValue');
        
//        $allTags = $this->tag->get()->map(function($item){
//            return $item->name;
//        })->all();
        
        $subCates = $this->categorySecond->where(['parent_id'=>$cate_id, ])->get()->map(function($obj) {
            return [ $obj->id => $obj->name ];
        })->all();
        
         $array = [1, 11, 12, 13, 14, 15];
         
        return response()->json(array('subCates'=> $subCates)/*, 200*/); //200を指定も出来るが自動で200が返される  
          //return view('dashboard.script.index', ['val'=>$val]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return redirect('dashboard/items/'.$id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $name = $this->category->find($id)->name;
        
        $atcls = $this->item->where('cate_id', $id)->get()->map(function($item){
            $item->cate_id = 0;
            $item->save();
        });
        
        $cateDel = $this->category->destroy($id);
        
        $status = $cateDel ? '商品「'.$name.'」が削除されました' : '商品「'.$name.'」が削除出来ませんでした';
        
        return redirect('dashboard/items')->with('status', $status);
    }
}
