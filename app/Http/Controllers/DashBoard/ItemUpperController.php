<?php

namespace App\Http\Controllers\DashBoard;

use App\Admin;
use App\Item;
use App\Category;
use App\CategorySecond;
use App\Tag;
use App\TagRelation;
use App\ItemUpper;
use App\ItemUpperRelation;


//use App\Consignor;
//use App\DeliveryGroup;
//use App\DeliveryGroupRelation;
use App\ItemImage;
use App\Setting;
use App\ItemStockChange;
//use App\Icon;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Storage;

class ItemUpperController extends Controller
{
    
    public function __construct(Admin $admin, Item $item, Tag $tag, Category $category, CategorySecond $categorySecond, TagRelation $tagRelation, ItemUpper $itemUpper, ItemUpperRelation $itemUpperRel, ItemImage $itemImg, Setting $setting, ItemStockChange $itemSc)
    {
        
        $this -> middleware('adminauth');
        //$this -> middleware('log', ['only' => ['getIndex']]);
        
        $this -> admin = $admin;
        $this-> item = $item;
        $this->category = $category;
        $this->categorySecond = $categorySecond;
        $this -> tag = $tag;
        $this->tagRelation = $tagRelation;
        
        $this->itemUpper = $itemUpper;
        $this->itemUpperRel = $itemUpperRel;
        
        $this->itemImg = $itemImg;
        
        $this->set = Setting::get()->first();
        
        $this->itemSc = $itemSc;
        
        $this->perPage = 20;
        
        // URLの生成
        //$url = route('dashboard');
        
        /* ************************************** */
        //env()ヘルパー：環境変数（$_SERVER）の値を取得 .env内の値も$_SERVERに入る
    }
    
    
    
    public function getItemUpper($itemId, Request $request)
    {


    }
    
    public function postItemUpper(Request $request)
    {
    	if($request->has('iId')) {
        	$itemId = $request->input('iId');
            
            
        }
        else {
        	
        }
        
        
        
    }
    
    
    public function index()
    {
        //
    }

    public function show($id, Request $request)
    {
        if(! $request->has('type')) {
        	
        	return view('dashboard.item.formUpper')->withErrors();    
            
        }

        $type = $request->input('type');
        
        if($type == 'item') {
        	$orgObj = $this->item->find($id);
        }
        elseif($type == 'cate') {
        	$orgObj = $this->category->find($id);
        }
        elseif($type == 'subcate') {
        	$orgObj = $this->categorySecond->find($id);
        }
        elseif($type == 'tag') {
        	$orgObj = $this->tag->find($id);
        }
        
        $upper = $this->itemUpper->where(['type_code'=>$type, 'parent_id'=>$id])->first();
        //$upperRel = null;
        $relArr = ['a'=>array(), 'b'=>array(), 'c'=>array()];
        
        if(isset($upper) && $upper !== null) { //編集
        	$edit = 1;
        	
            $upperRels = $this->itemUpperRel->where(['upper_id'=>$upper->id])->orderBy('sort_num')->get()/*->keyBy('block')*/;
            
            if($upperRels->isNotEmpty()) {
            	$relArr = array();
                foreach($upperRels as $upperRel) {
                	if($upperRel->is_section) {
                    	$relArr[$upperRel->block]['section'] = $upperRel;
                    }
                    else {
                    	$relArr[$upperRel->block][] = $upperRel;
                    }
                }
            }
            
        }
        else { //新規作成
        	$edit = 0;
        }
        
        
        $blockCount = [
        	'a' => $this->set->snap_block_a,
            'b' => $this->set->snap_block_b,
            'c' => $this->set->snap_block_c,
        ];
        
        //$icons = $this->icon->all();
        
        return view('dashboard.item.formUpper', ['orgObj'=>$orgObj, 'type'=>$type, 'upper'=>$upper, 'relArr'=>$relArr, 'blockCount'=>$blockCount, 'id'=>$id, 'edit'=>$edit]);
    }
    
    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        $editId = $request->input('edit_id');
        $type = $request->input('type');
        
    	$rules = [
//        	'number' => 'required|unique:items,number,'.$editId,
//            'title' => 'required|max:255',
//            'cate_id' => 'required',
//            //'dg_id' => 'required',

//            
//            'factor' => 'required|numeric',
//            
//            'price' => 'required|numeric',
//            'cost_price' => 'nullable|numeric',
//            'sale_price' => 'nullable|numeric',
//            'stock' => 'nullable|numeric',
//            'stock_reset_month' => [
//                function($attribute, $value, $fail) use($request) {
//                    if($value == '') {
//                        if($request->input('stock_type') == 1) {
//                            return $fail('「在庫入荷月」を指定して下さい。');
//                        } 
//                    }
//                    elseif(! is_numeric($value)) {
//                    	return $fail('「在庫入荷月」は半角数字を入力して下さい。');
//                    }
//                    elseif ($value < 1 || $value > 12) {
//                        return $fail('「在庫入荷月」は正しい月を入力して下さい。');
//                    }
//                },
//            ],
//            'stock_reset_count' => 'nullable|numeric',
//            'point_back' => 'nullable|numeric',
//            
//            'pot_parent_id' =>'required_with:is_potset|nullable|numeric',
//            'pot_count' =>'required_with:is_potset|nullable|numeric',
            
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
        
//        print_r($data['icons']);
//		echo implode(',', $data['icons']);
//        exit;
        
        //status
        $data['open_status'] = isset($data['open_status']) ? 0 : 1;
        
        
        $itemUpper = $this->itemUpper->updateOrCreate(
        	['parent_id'=>$editId, 'type_code'=>$type],
        	['open_status'=>$data['open_status']]
        );
        
//        print_r($data);
//        exit;

		foreach($data['block'] as $blockKey => $blockArr) {
        	$num = 0;
            
            foreach($blockArr as $key => $vals) {
                
				if($key === 'section') { //大タイトルの時
                	
                	$this->itemUpperRel->updateOrCreate(
                        ['id'=>$vals['rel_id'], 'upper_id'=>$itemUpper->id, 'block'=>$blockKey, 'is_section'=>1],
                        [  
                            'title'=> $vals['title'],
                            //'detail'=> $vals['detail'],
                            'sort_num'=> 0,
                        ]
                    );
                
                }
                else { //blockの時
                    $upperRel = $this->itemUpperRel->updateOrCreate(
                        ['id'=>$vals['rel_id'], 'upper_id'=>$itemUpper->id, 'block'=>$blockKey, 'is_section'=>0],
                        [
                            'title'=> $vals['title'],
                            'detail'=> $vals['detail'],
                            'sort_num'=> $num+1,
                        ]
                    );
                    
                    
                    if(isset($vals['del_img']) && $vals['del_img']) { //削除チェックの時
                        Storage::delete('public/'. $upperRel->img_path); //Storageはpublicフォルダのあるところをルートとしてみる
                        
                        $upperRel->img_path = null;
                        $upperRel->save();
                    }
                    else {
                        if(isset($vals['img'])) {
                        
                            $filename = $vals['img']->getClientOriginalName();
                            $filename = str_replace(' ', '_', $filename);
                            
                            //$aId = $editId ? $editId : $rand;
                            //$pre = time() . '-';
                            $filename = 'upper/' . $type . '/' . $editId . '/' . $blockKey . '/' . $filename;
                            //if (App::environment('local'))
                            $path = $vals['img']->storeAs('public', $filename);
                            //else
                            //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
                            //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
                        
                            //$data['model_thumb'] = $filename;
                            
                            $upperRel->img_path = $filename;
                            $upperRel->save();
                        }
                    }
                	
                    $num++;
                }
                
                

                

            }
            
        }
		
                
        
        if($editId) { //update（編集）の時
            $status = '上部コンテンツが更新されました！';
            //$itemUpper = $this->itemUpper->where(['parent_id'=>$editId, 'type_code'=>$type])->first();
            //echo date('Y-m-d H:i:s', time());

            
            
        }
        else { //新規追加の時
            $status = '上部コンテンツが追加されました！';            
            
        }
        

        
        
        
        
        return redirect('dashboard/upper/'. $editId . '?type=' . $type)->with('status', $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    

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
