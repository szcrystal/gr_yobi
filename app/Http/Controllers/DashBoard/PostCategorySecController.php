<?php

namespace App\Http\Controllers\DashBoard;

use App\PostCategory;
use App\PostCategorySecond;
use App\Setting;

    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostCategorySecController extends Controller
{
    public function __construct(PostCategory $postCate, PostCategorySecond $postCateSec, Setting $setting)
    {
        $this -> middleware(['adminauth', 'role:isAdmin']);
        
        $this->postCate = $postCate;
        $this->postCateSec = $postCateSec;
        
        $this->setting = $setting;
        $this->set = $this->setting->first();
        
        $this->perPage = 30;
    }
    
    public function index()
    {
        //$cates = Category::orderBy('id', 'desc')->paginate($this->perPage);
        $subCates = $this->postCateSec->orderBy('id', 'desc')->get();
        
        return view('dashboard.postCategorySec.index', compact('subCates') );
    }

    public function show($id)
    {
    	$cates = $this->postCate->all();
        $subCate = $this->postCateSec->find($id);
        
//        $snaps = $this->itemImg->where(['item_id'=>$id, 'type'=>3])->get();
//        $imgCount = $this->setting->get()->first()->snap_category;
        
        $edit = $id;
        
        return view('dashboard.postCategorySec.form', compact('cates', 'subCate', 'id', 'edit') );
    }
    
    
    public function create()
    {
        //$imgCount = $this->setting->get()->first()->snap_category;
        $cates = $this->postCate->all();
        
        //$postSubCates = 
        
        return view('dashboard.postCategorySec.form', compact('cates'));
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
            'name' => 'required|unique:post_category_seconds,name,'.$editId.'|max:255',
            'slug' => 'required|alpha_dash|unique:post_category_seconds,slug,'.$editId.'|max:255', /* 注意:unique */
        ];
        
        $messages = [
            'name.required' => '「記事 子カテゴリー名」は必須です。',
            'name.unique' => '「記事 子カテゴリー名」が既に存在します。',
        ];
        
        $this->validate($request, $rules, $messages);
        
        $data = $request->all();
        
        $data['is_top'] = isset($data['is_top']) ? 1 : 0;

        if($editId) { //update（編集）の時
            $status = '記事 子カテゴリーが更新されました！';
            $cateModel = $this->postCateSec->find($editId);
        }
        else { //新規追加の時
            $status = '記事 子カテゴリーが追加されました！';
            $data['view_count'] = 0;
            $cateModel = $this->postCateSec;
        }
        
        $cateModel->fill($data); //モデルにセット
        $cateModel->save(); //モデルからsave
        
        $cateId = $cateModel->id;
        
        //for top-img =========================================
        if(isset($data['top_img_path'])) {
                
            //$filename = $request->file('main_img')->getClientOriginalName();
            $filename = $data['top_img_path']->getClientOriginalName();
            $filename = str_replace(' ', '_', $filename);
            
            $fNameArr = explode('.', $filename);
            $filename = $fNameArr[0] . '-' . mt_rand(0, 99999) . '.' . array_pop($fNameArr); //array_pop 配列最後（拡張子を取得） end()でも可
            
            //$aId = $editId ? $editId : $rand;
            //$pre = time() . '-';
            $filename = 'post/category_sec/' . $cateId . '/recom/' . $filename;
            //if (App::environment('local'))
            $path = $data['top_img_path']->storeAs('public', $filename);
            //else
            //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
            //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
            
            $cateModel->top_img_path = $path;
            $cateModel->save();
        }

        
        //Snap Save ==================================================
        if(isset($data['snap_count'])) {
        
            foreach($data['snap_count'] as $count) {
            
                /*
                    type:1->item spare
                    type:2->item snap(contents)
                    type:3->category
                    type:4->sub category
                    type:5->tag                              
                */         
     
                if(isset($data['del_snap'][$count]) && $data['del_snap'][$count]) { //削除チェックの時
                    //echo $count . '/' .$data['del_snap'][$count];
                    //exit;
                    
                    $snapModel = $this->itemImg->where(['item_id'=>$cateId, 'type'=>3, 'number'=>$count+1])->first();
                    
                    if($snapModel !== null) {
                        Storage::delete('public/'.$snapModel->img_path); //Storageはpublicフォルダのあるところをルートとしてみる（storage/app直下）
                        $snapModel ->delete();
                    }
                
                }
                else {
                    if(isset($data['snap_thumb'][$count])) {
                        
                        $snapImg = $this->itemImg->updateOrCreate(
                            ['item_id'=>$cateId, 'type'=>3, 'number'=>$count+1],
                            [
                                'item_id'=>$cateId,
                                //'snap_path' =>'',
                                'type' => 3,
                                'number'=> $count+1,
                            ]
                        );

                        $filename = $data['snap_thumb'][$count]->getClientOriginalName();
                        $filename = str_replace(' ', '_', $filename);
                        
                        //$aId = $editId ? $editId : $rand;
                        //$pre = time() . '-';
                        $filename = 'category_sec/' . $cateId . '/snap/'/* . $pre*/ . $filename;
                        //if (App::environment('local'))
                        $path = $data['snap_thumb'][$count]->storeAs('public', $filename);
                        //else
                        //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
                        //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
                    
                        //$data['model_thumb'] = $filename;
                        
                        $snapImg->img_path = $filename;
                        $snapImg->save();
                    }
                }
                
            } //foreach
        
            $num = 1;
            $snaps = $this->itemImg->where(['item_id'=>$cateId, 'type'=>3])->get();
    //            $snaps = $this->modelSnap->where(['model_id'=>$modelId])->get()->map(function($obj) use($num){
    //                
    //                return true;
    //            });
            
            //Snapのナンバーを振り直す
            foreach($snaps as $snap) {
                $snap->number = $num;
                $snap->save();
                $num++;
            }
        
        }
        //Snap END ===========================================

        return redirect('dashboard/post-categories/sec/'.$cateId)->with('status', $status);
    }
    
    
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
        $name = $this->postCate->find($id)->name;
        
        $atcls = $this->item->where('subcate_id', $id)->get()->map(function($obj){
            $obj->subcate_id = null;
            $obj->save();
        });
        
        $cateDel = $this->cateSec->destroy($id);
        
        //if(Storage::exists('public/subcate/'. $id)) {
            Storage::deleteDirectory('public/subcate/'. $id); //存在しなければスルーされるようだ
        //}
        
        
        $status = 'カテゴリー「' . $name . '」';
        $status .= $cateDel ? 'が削除されました' : 'が削除出来ませんでした。';
        
        return redirect('dashboard/post-categories/sub')->with('status', $status);
    }
}
