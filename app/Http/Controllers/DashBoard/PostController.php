<?php

namespace App\Http\Controllers\DashBoard;

use App\Admin;
use App\Item;
use App\Setting;
use App\Post;
use App\PostRelation;
use App\Category;
use App\Tag;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostController extends Controller
{
    public function __construct(Admin $admin, Item $item, Setting $setting, Post $post, PostRelation $postRel, Category $cate, Tag $tag)
    {
        
        $this -> middleware('adminauth');
        //$this -> middleware('log', ['only' => ['getIndex']]);
        
        $this -> admin = $admin;
        $this-> item = $item;
        $this->set =  $setting->first();
        $this->post = $post;
        $this->postRel = $postRel;
        $this->category = $cate;
        $this->tag = $tag;
        
        $this->perPage = 20;

    }
    
    public function index()
    {
        //$itemObjs = Item::orderBy('id', 'desc')->paginate($this->perPage);
        $itemObjs = $this->item->orderBy('id', 'desc')->get();
        
        
        $recentObjs = $this->item->orderBy('updated_at', 'desc')->get()->take(5);
        
        //$status = $this->articlePost->where(['base_id'=>15])->first()->open_date;
        
        
        return view('dashboard.post.index', ['itemObjs'=>$itemObjs, ]);
    }

    
    public function show($id)
    {
        $postRel = $this->postRel->find($id);

        $relArr = ['a'=>array()/*, 'b'=>array(), 'c'=>array()*/];
        
        if(isset($postRel) && $postRel !== null) { //編集
        	$edit = 1;
        	
            $upperRels = $this->post->where(['rel_id'=>$id])->orderBy('sort_num', 'asc')->get()/*->keyBy('block')*/;
            
            if($upperRels->isNotEmpty()) {
            	//$relArr = array();
                foreach($upperRels as $upperRel) {
//                	if($upperRel->is_mid_section) {
//                    	$relArr[$upperRel->block]['mid_section'][] = $upperRel;
//                    }
//                    else {
                        if($upperRel->is_section) {
                        	if($upperRel->sort_num > 0) { //sort_numが1以上なら中タイトル 0は大タイトル(1つのみ)
                            	$relArr[$upperRel->block]['mid_section'][] = $upperRel;
                            }
                            else {
                            	$relArr[$upperRel->block]['section'] = $upperRel; //大タイトルは1つのみなのでpushしない
                            }
                        }
                        else {
                            $relArr[$upperRel->block][] = $upperRel;
                        }
                    //}
                }
            }
            
        }
        else { //新規作成
        	$edit = 0;
        }
        
//        print_r($relArr);
//        exit;


		$cates = $this->category->all();
        
        $allTags = $this->tag->get()->map(function($tag){
        	return $tag->name;
        })->all();
        
        
        $blockCount = [
        	'a' => $this->set->snap_block_a,
//            'b' => $this->set->snap_block_b,
//            'c' => $this->set->snap_block_c,
        ];
        
        //$icons = $this->icon->all();
        
        return view('dashboard.post.form', ['postRel'=>$postRel, 'relArr'=>$relArr, 'cates'=>$cates, 'allTags'=>$allTags, 'blockCount'=>$blockCount, 'id'=>$id, 'edit'=>$edit]);
    }
   
    public function create()
    {
		$id = 0;
        $edit = 0;
        
        $relArr = ['a'=>array()/*, 'b'=>array(), 'c'=>array()*/];
        
        $blockCount = [
        	'a' => $this->set->snap_block_a,
//            'b' => $this->set->snap_block_b,
//            'c' => $this->set->snap_block_c,
        ];
        
        $primaryCount = $this->set->snap_primary;
        $imgCount = $this->set->snap_secondary;
        
        
        $cates = $this->category->all();
        
        $allTags = $this->tag->get()->map(function($tag){
        	return $tag->name;
        })->all();
        
        
//        $users = $this->user->where('active',1)->get();
        return view('dashboard.post.form', ['primaryCount'=>$primaryCount, 'imgCount'=>$imgCount, 'relArr'=>$relArr, 'blockCount'=>$blockCount, 'cates'=>$cates, 'allTags'=>$allTags, 'edit'=>$edit, 'id'=>$id, ]);
    }
    
    
    public function store(Request $request)
    {
        $editId = $request->input('edit_id');
        
    	$rules = [
//        	'number' => 'required|unique:items,number,'.$editId,
            //'block.a.0.title' => 'required|max:255',
          
//            'price' => 'required|numeric',
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

        ];

        
        $messages = [
         	'block.a.0.title.required' => '「商品名」を入力して下さい。',
            'cate_id.required' => '「カテゴリー」を選択して下さい。',
        ];
        
        $this->validate($request, $rules, $messages);
        
        $data = $request->all();
        
        
        //status
        $data['open_status'] = isset($data['open_status']) ? 0 : 1;
        $data['is_index'] = isset($data['is_index']);
        
//        echo $data['open_status'];
//        exit;
        
        $postRel = $this->postRel->updateOrCreate(
        	['id'=>$editId],
            $data
//        	[
//            	'cate_id' => $data['cate_id'],
//            	'open_status' => $data['open_status'],
//                'is_index' => $data['is_index'],
//            ]
        );
        
//        print_r($data['block']);
//        exit;
		
        /*
        //サムネイル紹介用コメント --------------
        $orgObj = null;
        $ups = [
        	'upper_title'=>$data['upper_title'],
            'upper_text'=>$data['upper_text'],
        ];
        
//        if($type == 'item') {
//        	$orgObj = $this->item->find($editId);
//        }
//		elseif($type == 'cate') {
//        	$orgObj = $this->category->find($editId);
//        }
//        elseif($type == 'subcate') {
//        	$orgObj = $this->categorySecond->find($editId);
//        }
//        elseif($type == 'tag') {
//        	$orgObj = $this->tag->find($editId);
//        }
        
        if(isset($orgObj)) {
        	$orgObj->update($ups);
        }
        //サムネイル用コメント END ------------
        */

		$status = '記事が編集されました。';
        
        $midTitleId = 0;
        $midTitleArr = array();

		
        foreach($data['block'] as $blockKey => $blockArr) {
        	
            $num = 0;
            
//            print_r($blockArr);
//            exit;
            
            foreach($blockArr as $key => $vals) {
                
				$isSection = $key === 'section' ? 1 : 0; //大タイトルの時かブロックかを判別する
                $isMidSection = $key === 'mid_section' ? 1 : 0; //中タイトルの時かブロックかを判別する
                
                
                if(isset($vals['del_block']) && $vals['del_block'] && $vals['rel_id']) { //block削除の時
                	$postRel = $this->post->find($vals['rel_id']);
                    
                    if(isset($postRel->img_path)) {
                    	Storage::delete('public/'. $postRel->img_path);
                    }
                    
                    $postRel->delete();
                    
                    //$status .= "\n". '「' . $blockKey . 'ブロック-' . ($vals['count']+1) . '」が削除されました。';
                }
                else {
                	
                	if($isMidSection) {
                    	//大タイトルと中タイトルの区分けは、どちらもis_sectionは1、大タイトルはsort_numが必ず0、中タイトルは1以上
                        
                        $nn = 0;
                        
                    	foreach($vals as $val) {
                            $midTitlePost = $this->post->updateOrCreate(
                                [
                                    'id' => $val['rel_id'],
                                ],
                                [
                                    'rel_id'=> $postRel->id, 
                                    'block'=> $blockKey,
                                    'url'=> null,
                                    'title'=> $val['title'],
                                    'detail'=> null,
                                    'is_section'=> 1,
                                    //'is_mid_section'=> null,
                                    'sort_num'=> $nn+1, //sort_numが0の時は大タイトルのみなので、ここでは必ず0を入れないこと
                                ]
                            );
                        	
                            //h2タイトルのIDを後でcontents用postにセットするのでそれ用の配列をここで作成する。
                            if($val['title'] != '') {
                            	$midTitleId = $midTitlePost->id;
                            }
                            
                            $midTitleArr[$midTitlePost->sort_num] = $midTitleId;
                            
                            $nn++;
                        }
                    }
                    else {
                    	
//                        print_r($midTitleArr);
//                        exit;
                    
                        //relationのidをinput-hiddenに設定し（0ならcreate）$vals['rel_id']でupdateOrCreateする方法もあり
                        $contPost = $this->post->updateOrCreate(
                            [
                                'id' => $vals['rel_id'],
                            ],
                            [
                                'rel_id'=> $postRel->id, 
                                'block'=> $blockKey,
                                'url'=> $isSection ? null : $vals['url'],
                                'title'=> $vals['title'],
                                'detail'=> $isSection ? null : $vals['detail'],
                                'is_section'=> $isSection ? 1 : 0,
                                'sort_num'=> $isSection ? 0 : $num,
                                //'mid_title_id' => $isSection ? null : $midTitleArr[$num+1],
                            ]
                        );
                        
                        $num++;
                    }

					//sort_numでデータを照合してupdateOrCreateする方法もあり
                    /*
                    $upperRel = $this->itemUpperRel->updateOrCreate(
                        [
                            'upper_id'=> $itemUpper->id, 
                            'block'=> $blockKey, 
                            'is_section'=> $isSection ? 1 : 0,
                            'sort_num'=> $isSection ? 0 : $vals['count']+1,
                        ],
                        [
                            'title'=> $vals['title'],
                            'detail'=> $isSection ? null : $vals['detail'],
                            'sort_num'=> $isSection ? 0 : $num+1,
                        ]
                    );
                    */
                    
                    
                    if(isset($vals['del_img']) && $vals['del_img']) { //削除チェックの時
                    	if(isset($postRel->img_path)) {
                            Storage::delete('public/'. $postRel->img_path); //Storageはpublicフォルダのあるところをルートとしてみる
                            
                            $postRel->img_path = null;
                            $postRel->save();
                        }
                    }
                    else {
                        if(isset($vals['img'])) {
                        
                            $filename = $vals['img']->getClientOriginalName();
                            $filename = str_replace(' ', '_', $filename);
                            
                            $fNameArr = explode('.', $filename);
                            $filename = $fNameArr[0] . '-' . mt_rand(0, 99999) . '.' . array_pop($fNameArr); //array_pop 配列最後（拡張子を取得） end()でも可
                            
                            //$pre = time() . '-';
                            //$pre = mt_rand(0, 99999) . '-';
                            $pre = '';
                            
                            $filename = 'post/' . $postRel->id . '/' . $blockKey . '/' . $pre . $filename;
                            //$dirName = 'upper/' . $type . '/' . $editId . '/' . $blockKey;

                            //new File()は画像情報を取得するためのもの。 new File('aaa.jpg')とすると、$vals['img'] or $request->file('img')と同じものになる
                            
                            //$path = $vals['img']->store($dirName); //ファイル名が自動生成される
                            //Storage::putFile($dirName, $vals['img']); //上と同じ
                            
                            //$path = $vals['img']->storeAs($dirName, 'abc'); //ファイル名を独自指定(拡張子が付かない)
                            $path = $vals['img']->storeAs('public', $filename);
                            
                            //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
                            //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
                                                    
                            $contPost->img_path = $filename;
                            $contPost->save();
                        }
                    }
                    
                    //if(! $isSection) $num++;
                }

            } //foreach
            
        } //foreach
        
        
        //h2タイトルのIDをここでセットする。$midTitleArrは保存中に取得する配列
        $this->post->where(['rel_id'=>$postRel->id, 'is_section'=>0])->get()->map(function($contPost) use($midTitleArr){
            $contPost->update([ 'mid_title_id' => $midTitleArr[$contPost->sort_num] ]);
        });
	
//        print_r($midTitleArr);
//        exit;
        
        
        
        return redirect('dashboard/posts/'. $postRel->id)->with('status', $status);
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
