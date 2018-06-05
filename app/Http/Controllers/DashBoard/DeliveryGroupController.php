<?php

namespace App\Http\Controllers\DashBoard;

use App\Admin;
use App\DeliveryGroup;
use App\DeliveryGroupRelation;
use App\Prefecture;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DeliveryGroupController extends Controller
{
    public function __construct(Admin $admin, DeliveryGroup $dg, DeliveryGroupRelation $dgRel, Prefecture $prefecture)
    {
        
        $this -> middleware('adminauth');
        //$this -> middleware('log', ['only' => ['getIndex']]);
        
        $this -> admin = $admin;
        $this-> dg = $dg;
        $this->dgRel = $dgRel;
        $this->prefecture = $prefecture;
        
        
        $this->perPage = 20;
        
        // URLの生成
        //$url = route('dashboard');
        
        /* ************************************** */
        //env()ヘルパー：環境変数（$_SERVER）の値を取得 .env内の値も$_SERVERに入る
    }
    
    
    
    public function index()
    {
        
        $dgs = $this->dg->orderBy('id', 'asc')->paginate($this->perPage);
        
        $dgRels = $this->dgRel;
        
        //$status = $this->articlePost->where(['base_id'=>15])->first()->open_date;
        
        return view('dashboard.dg.index', ['dgs'=>$dgs, 'dgRels'=>$dgRels]);
    }

    public function show($id)
    {
        $dg = $this->dg->find($id);
        //$users = $this->user->where('active',1)->get();
        
//        $tagNames = $this->tagRelation->where(['item_id'=>$id])->get()->map(function($item) {
//            return $this->tag->find($item->tag_id)->name;
//        })->all();
//        
//        $allTags = $this->tag->get()->map(function($item){
//            return $item->name;
//        })->all();
        
        return view('dashboard.dg.form', ['dg'=>$dg, 'id'=>$id, 'edit'=>1]);
    }
   
    public function create()
    {
//        $cates = $this->category->all();
//        $allTags = $this->tag->get()->map(function($item){
//            return $item->name;
//        })->all();
//        $users = $this->user->where('active',1)->get();
        return view('dashboard.dg.form', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $editId = $request->has('edit_id') ? $request->input('edit_id') : 0;
        
        $rules = [
            //'title' => 'required|max:255',
            //'movie_url' => 'required|max:255',
            //'main_img' => 'filenaming',
        ];
        
         $messages = [
             'title.required' => '「商品名」を入力して下さい。',
            'cate_id.required' => '「カテゴリー」を選択して下さい。',
            
            //'post_thumb.filenaming' => '「サムネイル-ファイル名」は半角英数字、及びハイフンとアンダースコアのみにして下さい。',
            //'post_movie.filenaming' => '「動画-ファイル名」は半角英数字、及びハイフンとアンダースコアのみにして下さい。',
            //'slug.unique' => '「スラッグ」が既に存在します。',
        ];
        
        $this->validate($request, $rules, $messages);
        
        $data = $request->all();
        
        //status
        if(isset($data['open_status'])) { //非公開On
            $data['open_status'] = 0;
        }
        else {
            $data['open_status'] = 1;
        }
        
        if($editId) { //update（編集）の時
            $status = '配送区分が更新されました！';
            $dg = $this->dg->find($editId);
        }
        else { //新規追加の時
            $status = '配送区分が追加されました！';
            //$data['model_id'] = 1;
            
            $dg = $this->dg;
        }
        
        $dg->fill($data);
        $dg->save();
        $dgId = $dg->id;
        
        
        return redirect('dashboard/dgs/'. $dgId)->with('status', $status);
    }

    
    public function getFee($dgId)
    {
        $dg = $this->dg->find($dgId);
        
        $prefs = $this->prefecture->all();
        
        $dgRels = $this->dgRel->where(['dg_id'=>$dgId])->get();
        
        
        
        return view('dashboard.dg.formFee', ['dg'=>$dg, 'prefs'=>$prefs, 'dgRels'=>$dgRels, 'id'=>$dgId, 'edit'=>1]);
    }
    
    public function postFee($dgId, Request $request)
    {
        
        $rules = [
            //'title' => 'required|max:255',
            //'movie_url' => 'required|max:255',
            //'main_img' => 'filenaming',
        ];
        
         $messages = [
             'title.required' => '「商品名」を入力して下さい。',
            'cate_id.required' => '「カテゴリー」を選択して下さい。',
            
            //'post_thumb.filenaming' => '「サムネイル-ファイル名」は半角英数字、及びハイフンとアンダースコアのみにして下さい。',
            //'post_movie.filenaming' => '「動画-ファイル名」は半角英数字、及びハイフンとアンダースコアのみにして下さい。',
            //'slug.unique' => '「スラッグ」が既に存在します。',
        ];
        
        $this->validate($request, $rules, $messages);
        
        $data = $request->all();

        foreach($data['pref_id'] as $key => $prefId) {
        
            
            //$feeObj = $this->dgRel->where(['dg_id'=>$dgId, 'pref_id'=>$prefId])->first();
        
            $this->dgRel->updateOrCreate(['dg_id'=>$dgId, 'pref_id'=>$prefId], ['fee'=>$data['fee'][$key]]);
//            if(! isset($feeObj)) {
//                $feeObj = $this->dgRel;    
//             }
//                   
//        $feeObj->fee = $data['fee'][$key];
//                   $feeObj->save();
//        $dg->fill($data);
//        $dg->save();
//        $dgId = $dg->id;
//        
        }
        
        $status = '送料が更新されました！';
        
        return redirect('dashboard/dgs/fee/'. $dgId)->with('status', $status);
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