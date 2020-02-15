<?php

namespace App\Http\Controllers\Cart;

use App\Setting;
use App\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Ctm;

use Client; //AmznPay SDK

class PaymentController extends Controller
{
    public function __construct()
    {
        
        $this->setting = new Setting;
        $this->set = $this->setting->first();
        
        $this->user = new User;
        
        //$this->searchWord = $searchWord;
        
        //GMO 決済ID
        $this->gmoId = Ctm::gmoId();
        
        //Amzn 本番／テストどちらも同じ
        $this->amznConfig = [
            'merchant_id' => 'AUT5MRXA61A3P',
            'access_key'  => 'AKIAIULMCJL2WZE3LLAQ',
            'secret_key'  => '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',
            'client_id'   => 'amzn1.application-oa2-client.471a3dc352524c5cb3066ece8967eeb2',
            'region'      => 'jp',
            'currency_code' => 'JPY',
            'sandbox'     => $this->set->is_product ? false : true,
            
            //'mws developer_id' => '879609259100',
            //'mws_access_token' => '3pKDQQL1eRfsZpFM0mTMaYxkLScapMmcOAbYoGr5',
        ];
                    
    }
    
    public function getAmznDetail($referenceId)
    {
        //$config = $this->amznConfig;

        // or, instead of setting the array in the code, you can
        // initialze the Client by specifying a JSON file
        // $config = 'PATH_TO_JSON_FILE';

        // Instantiate the client class with the config type
        $client = new Client($this->amznConfig);
        //$client->setSandbox(true);
        
        // Optional Parameter
        $requestParams = [
            'amazon_order_reference_id' => $referenceId,
            'access_token' => session('all.access_token'),
            //'mws_auth_token' => '3pKDQQL1eRfsZpFM0mTMaYxkLScapMmcOAbYoGr5', // config -> シークレットキーと同じ？ 必須ではないらしい
            //'address_consent_token' => session('all.access_token'),
        ];
        
        //$response = $client->getMerchantAccountStatus($$requestParams);
        $response = $client->getOrderReferenceDetails($requestParams);
        //$userInfo = $client->getUserInfo($requestParams['address_consent_token']); // ここだけ返りは配列
        
//            print_r($userInfo);
//            exit;
        
        $obj = $response->toArray();
//            $obj = simplexml_load_string($response->toXml());
//            $obj = json_decode(json_encode($obj), true);

        
        if(isset($obj['Error'])) {
            //Error処理・・・
//            [Error] => Array
//            (
//                [Type] => Sender
//                [Code] => InvalidParameterValue
//                [Message] => The Value 'null' is invalid for the Parameter 'Amount'
//            )

            return $obj;
            //このタイミングでredirectが効かない
            //return redirect('shop/form?amznerr=1000')->with('ErrInfo', '[amz-'.$obj['ResponseStatus'].'-'. $obj['Error']['Code'].']');
//            print_r($obj);
//            exit;
        }
        
        $addInfo = $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'];
        $userInfo = $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer'];
        
        
        return compact('addInfo', 'userInfo');
        
//
//        $data['destination'] = 1;
//
//        $data['receiver']['post_num'] = $addInfo['PostalCode'];
//        $data['receiver']['prefecture'] = $addInfo['StateOrRegion'];
//        $data['receiver']['address_1'] = $addInfo['AddressLine1'] . $addInfo['AddressLine2'];
//        $data['receiver']['address_2'] = $addInfo['AddressLine3'];
//        $data['receiver']['name'] = $addInfo['Name'];
//        $data['receiver']['tel_num'] = $addInfo['Phone'];
//
//        $data['user']['name'] = $userInfo['Name'];
//        $data['user']['email'] = $userInfo['Email'];
//
//        if($regist) {
//            $data['user']['hurigana'] = '';
//            $data['user']['post_num'] = $addInfo['PostalCode'];
//            $data['user']['prefecture'] = $addInfo['StateOrRegion'];
//            $data['user']['address_1'] = $addInfo['AddressLine1'] . $addInfo['AddressLine2'];
//            $data['user']['address_2'] = $addInfo['AddressLine3'];
//            $data['user']['tel_num'] = $addInfo['Phone'];
//        }
//
//        session([
//            'all.order_reference_id'=>$data['order_reference_id'],
//        ]);
    }
    
    // POSTで送信される url: shop/amznpay
    public function setAmznPay(Request $request)
    {
        if(! $request->session()->has('all')) {
            abort(404);
        }

        // Instantiate the client class with the config type
        $client = new Client($this->amznConfig);
        //$client->setSandbox(true);
        
        $sesAll = session('all');
        
        $orderReferenceId = $sesAll['order_reference_id'];
        $totalFee = $sesAll['total_fee'];
        $orderNumber = $sesAll['order_number'];
        
        // Optional Parameter
//        $requestParams = [
//            'mws_auth_token' => '3pKDQQL1eRfsZpFM0mTMaYxkLScapMmcOAbYoGr5',
//            'amazon_order_reference_id' => $orderReferenceId,
//            'address_consent_token' => session('all.access_token'),
//        ];
        
        //$response = $client->getMerchantAccountStatus($$requestParams);
        //$response = $client->getOrderReferenceDetails($requestParams);
        //return redirect('shop/thankyou');
        
        // setOrderReferenceDetails -------------
        $setParams = [
            'amazon_order_reference_id' => $orderReferenceId,
            'amount' => $totalFee,
            //'charge_amount' => $totalFee,
            //'currency_code' => 'JPY', //configで指定しているので不要
            'seller_order_id' => $orderNumber,
            'store_name' => 'グリーンロケット',
        ];
                
        $response = $client->setOrderReferenceDetails($setParams);
        
        $obj = $response->toArray();
//        $obj = simplexml_load_string($response->toXml());
//        $obj = json_decode(json_encode($obj), true);
        
        if(isset($obj['Error'])) {
            //Error処理・・・
//            [Error] => Array
//            (
//                [Type] => Sender
//                [Code] => InvalidParameterValue
//                [Message] => The Value 'null' is invalid for the Parameter 'Amount'
//            )
            
            return redirect('shop/form?amznerr=1000')->with('ErrInfo', '[amzSet-'.$obj['ResponseStatus'].'-'. $obj['Error']['Code'].']');
//            echo 'setOrder:';
//            print_r($obj);
//            exit;
        }
        
        // confirmOrderReference --------------------------
        $confirmParams = [
            'amazon_order_reference_id' => $orderReferenceId,
        ];
        
        $response = $client->confirmOrderReference($confirmParams);
        $obj = $response->toArray();
        
        if(isset($obj['Error'])) {
        // Error処理・・・
        //    [Error] => Array
        //     (
        //          [Type] => Sender
        //          [Code] => InvalidParameterValue
        //          [Message] => The Value 'null' is invalid for the Parameter 'Amount'
        //      )
            
            //「PaymentMethodNotAllowed」はここのタイミングでエラーになる
            
            return redirect('shop/form?amznerr=1000')->with('ErrInfo', '[amzConfirm-'.$obj['ResponseStatus'].'-'. $obj['Error']['Code'].']');
//            echo 'confirmOrder:';
//            print_r($obj);
//            exit;
        }
        
        // getOrderReferenceDetailsはここでは不要
        /*
        $response = $client->getOrderReferenceDetails($requestParameters);
        $obj = $response->toArray();
        
        if(isset($obj['Error'])) { //stateがopenならの条件もあった方がいいか
            // Error処理・・・
            print_r($obj);
            exit;
        }
        */
                
        // Authorize ======
        $authParams = [
            'amazon_order_reference_id' => $orderReferenceId,
            'authorization_reference_id' => $orderNumber,
            'authorization_amount' => $totalFee,
            //'currency_code' => 'JPY',
            'transaction_timeout' => 0, // 0:同期オーソリ
            //'capture_now' => TRUE,
        ];
        
        $response = $client->authorize($authParams);
        $obj = $response->toArray();
        
//        print_r($obj);
//        exit;
        
        //エラー時、$obj['AuthorizeResult']['AuthorizationDetails']['AuthorizationStatus']['State']=>Declinedで、[ReasonCode] => AmazonRejected/ProcessingFailure/TransactionTimedOut になる
        //成功時、[State]=>Open
        
        $status = $obj['AuthorizeResult']['AuthorizationDetails']['AuthorizationStatus'];
        
        if(isset($obj['Error'])) { //stateがopenならの条件もあった方がいいか
            // Error処理・・・
            return redirect('shop/form?amznerr=1000')->with([
                'ErrInfo' => '[amzAuth-'.$obj['ResponseStatus'].'-'. $obj['Error']['Code'].']',
                //'refId' => $orderReferenceId,
            ]);
            
//            echo 'Authorize:';
//            print_r($obj);
//            exit;
        }
        
        if(isset($status['ReasonCode'])) {
            return redirect('shop/form?amznerr=1000')->with([
                'ErrInfo' => '[amzAuth-'. $status['State'] .'-'. $status['ReasonCode'].']',
                'refId' => $orderReferenceId,
            ]);
        }
        
//        echo "Atuh";
//        print_r($obj);
//        exit;
        
        //        echo $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination']['AddressLine1'];
        //        echo $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer']['Name'];
        //        echo $obj['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer']['Email'];
        //        exit;
        
        //return 1;
        return redirect('shop/thankyou');
    }

    

    //クレカ決済 Confirm上でトークンを取得後ここにPostされる url: shop/paydo
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
                    return redirect('shop/form?carderr=1000')->with('ErrInfo', '[cc-5002-'.$cardRegSuccess['ErrInfo'].']');
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

        /*
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url . "payment/EntryTran.idPass",
            CURLOPT_RETURNTRANSFER => true, //文字列として返す
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($datas),
            CURLOPT_TIMEOUT => 20, // タイムアウト時間
        ];
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        curl_close($ch);
        */
        
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }
}
