<?php

namespace App\Http\Controllers\DashBoard;

use App\Admin;
use App\Item;
use App\Setting;
use App\Category;
use App\CategorySecond;
use App\Post;
use App\PostRelation;
use App\PostCategory;
use App\PostCategorySecond;
use App\Tag;
use App\PostTagRelation;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Storage;
use Ctm;

class PostController extends Controller
{
    public function __construct(Admin $admin, Item $item, Setting $setting, Category $cate, CategorySecond $subCate, Post $post, PostRelation $postRel, PostCategory $postCate, PostCategorySecond $postCateSec, Tag $tag, PostTagRelation $postTagRel)
    {
        
        $this -> middleware('adminauth');
        //$this -> middleware('log', ['only' => ['getIndex']]);
        
        $this -> admin = $admin;
        $this-> item = $item;
        $this->set =  $setting->first();
        $this->category = $cate;
        $this->categorySecond = $subCate;
        
        $this->post = $post;
        $this->postRel = $postRel;
        $this->postCate = $postCate;
        $this->postCateSec = $postCateSec;
        $this->tag = $tag;
        $this->postTagRel = $postTagRel;
        
        $this->perPage = 20;

    }
    
    public function index()
    {
    	if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) 
        	return view('errors.dashboard');
        
            
        $postRels = $this->postRel->orderBy('id', 'desc')->get();
        
//        foreach($postRels as $k => $postRel) {
//        	$post = $this->post->where(['rel_id'=>$postRel->id, 'is_section'=>1, 'sort_num'=>0])->first();
//            $postRel->big_title = $post->title;
//            $postRels[$k] = $postRel;
//        }
        
        
        return view('dashboard.post.index', ['postRels'=>$postRels, ]);
    }

    
    public function show($id)
    {
    	if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) 
        	return view('errors.dashboard');
        
    
        $postRel = $this->postRel->find($id);
        
        $postSubCates = $this->postCateSec->where('parent_id', $postRel->cate_id)->get();

        $relArr = ['p'=>array()/*, 'b'=>array(), 'c'=>array()*/];
        
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


		//Cate
		$postCates = $this->postCate->all();
        
        $itemCates = $this->category->all();
        $itemSubCates = $this->categorySecond->all();
        
        //Tag
        $tagNames = $this->postTagRel->where(['postrel_id'=>$id])->orderBy('sort_num', 'asc')->get()->map(function($obj) {
            return $this->tag->find($obj->tag_id)->name;
        })->all();
        
        $allTags = $this->tag->get()->map(function($tag){
        	return $tag->name;
        })->all();
        
        
        $blockCount = [
        	'p' => $this->set->post_block,
//            'b' => $this->set->snap_block_b,
//            'c' => $this->set->snap_block_c,
        ];
        
        //$icons = $this->icon->all();
        
        return view('dashboard.post.form', ['postRel'=>$postRel, 'relArr'=>$relArr, 'postCates'=>$postCates, 'postSubCates'=>$postSubCates, 'itemCates'=>$itemCates, 'itemSubCates'=>$itemSubCates, 'tagNames'=>$tagNames, 'allTags'=>$allTags, 'blockCount'=>$blockCount, 'id'=>$id, 'edit'=>$edit]);
    }
   
    public function create()
    {
    	if(Ctm::isEnv('product') || Ctm::isEnv('alpha')) 
        	return view('errors.dashboard');
        
        
		$id = 0;
        $edit = 0;
        
        $relArr = ['p'=>array()/*, 'b'=>array(), 'c'=>array()*/];
        
        $blockCount = [
        	'p' => $this->set->post_block,
//            'b' => $this->set->snap_block_b,
//            'c' => $this->set->snap_block_c,
        ];
        
        $primaryCount = $this->set->snap_primary;
        $imgCount = $this->set->snap_secondary;
        
        
        $postCates = $this->postCate->all();
        
        $itemCates = $this->category->all();
        $itemSubCates = $this->categorySecond->all();
        
        $allTags = $this->tag->get()->map(function($tag){
        	return $tag->name;
        })->all();
        
        
//        $users = $this->user->where('active',1)->get();
        return view('dashboard.post.form', ['primaryCount'=>$primaryCount, 'imgCount'=>$imgCount, 'relArr'=>$relArr, 'blockCount'=>$blockCount, 'postCates'=>$postCates, 'itemCates'=>$itemCates, 'itemSubCates'=>$itemSubCates, 'allTags'=>$allTags, 'edit'=>$edit, 'id'=>$id, ]);
    }
    
    
    public function store(Request $request)
    {
        $editId = $request->input('edit_id');
        
        //echo $request->input('item_cate_id') .'/'. $request->input('item_subcate_id'); exit;
//        if(! $request->has('item_subcate_id')) {
//        	$request->input('item_subcate_id') = 0;
//        }
        
    	$rules = [
//        	'number' => 'required|unique:items,number,'.$editId,
            'block.p.section.title' => 'required|max:255',
          	'cate_id' => 'required',
            'item_subcate_id' => function($attribute, $value, $fail) use($request) {
                if($request->input('item_cate_id') && ! $value) {
                    return $fail('「商品 親カテゴリー」指定時は「商品 子カテゴリー」も選択して下さい。');
                } 
            },
            
            's_word' => [
            	'nullable',
            	'max:255',
//                function($attribute, $value, $fail) {
//                    if (strpos($value, '、') !== false || strpos($value, ',') !== false) {
//                        return $fail('「検索ワード」にカンマがあります。');
//                    }
//                }
            ],
            
            'item_ids' => [
            	'nullable',
            	'max:255',
                function($attribute, $value, $fail) {
                    if (strpos($value, '、') !== false) {
                        return $fail('「商品ID」に全角のカンマがあります。');
                    }
                    elseif (strpos($value, ' ') !== false || strpos($value, ' ') !== false) {
                        return $fail('「商品ID」にスペースがあります。');
                    }
                    else {
                    	$nums = explode(',', $value);
                        foreach($nums as $num) {
                        	if(! is_numeric($num)) {
                            	return $fail('「商品ID」に全角の文字があります。');
                            }
                        }
                    }
                }
            ],
            
            
            
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
         	'block.p.section.title.required' => '「大タイトル」は必須です。',
            'cate_id.required' => '「記事カテゴリー」を選択して下さい。',
            'item_subcate_id.required_unless' => '「親カテゴリー」指定時は「子カテゴリー」も選択して下さい。',
        ];
        
        $this->validate($request, $rules, $messages);
        
        $data = $request->all();
        
        
        //status
        $data['open_status'] = isset($data['open_status']) ? 0 : 1;
        $data['is_index'] = isset($data['is_index']) ? 1 : 0;
        
        //big title
		$data['big_title'] = $data['block']['p']['section']['title'];
        
        $postRel = $this->postRel->updateOrCreate(
        	['id'=>$editId],
            $data
        );
        
        $postRelId = $postRel->id;
        
        //Main-img
        if(isset($data['del_mainimg']) && $data['del_mainimg']) { //削除チェックの時
            if($postRel->thumb_path !== null && $postRel->thumb_path != '') {
                Storage::delete($postRel->thumb_path); //Storageはpublicフォルダのあるところをルートとしてみる
                $postRel->thumb_path = null;
                $postRel->save();
            }
        }
        else {
            if(isset($data['thumb_path'])) {
                //$filename = $request->file('main_img')->getClientOriginalName();
                $filename = $data['thumb_path']->getClientOriginalName();
                $filename = str_replace(' ', '_', $filename);
                
                //$pre = time() . '-';
                $filename = 'post/' . $postRelId . '/thumb/'/* . $pre*/ . $filename;
                $path = $data['thumb_path']->storeAs('public', $filename);
                //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
                //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
                
                $postRel->thumb_path = $path;
                $postRel->save();
            }
        }
        
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
                	
               
               if($isMidSection) {
                    //大タイトルと中タイトルの区分けは、どちらもis_sectionは1、大タイトルはsort_numが必ず0、中タイトルは1以上
                    
                    $nn = 0;
                    //$nnn = 0;
                    
                    foreach($vals as $k => $val) {
                    	
                        //MidTitleに連動するblockを取得する
                        $contPostArr = $blockArr[$k];
                        
                        $isIntro = $contPostArr['is_intro'] ? 1 : 0;
                        
                         
                        $midTitlePost = $this->post->updateOrCreate(
                            [
                                'id' => $val['rel_id'],
                            ],
                            [
                                'rel_id'=> $postRelId, 
                                'block'=> $blockKey,
                                'url'=> null,
                                'title'=> $val['title'],
                                'detail'=> null,
                                'is_section'=> 1,
                                'is_intro' => $isIntro,
                                //'is_mid_section'=> null,
                                'sort_num'=> $nn+1, //sort_numが0の時は大タイトルのみなので、ここでは必ず0を入れないこと
                            ]
                        );
                        
                        
                        
                        if(isset($contPostArr['del_block']) && $contPostArr['del_block'] && $contPostArr['rel_id']) { //block削除の時
                            $postDel = $this->post->find($contPostArr['rel_id']);
                            
                            if(isset($postDel->img_path)) {
                                Storage::delete($postDel->img_path);
                            }
                            
                            $postDel->delete();
                            $midTitlePost->delete(); //MidTitleに連動するblockが削除指定なら、上記1度登録したmidTitleも合わせて削除しないとmid_title_idが合わなくなる

                        }
                        else {
                            //h2タイトルのIDを後でcontents用postにセットするのでそれ用の配列をここで作成する。
                            if(isset($midTitlePost->title)) { //$val['title'] != ''
                                $midTitleId = $midTitlePost->id;
                            }
                            
                            //contPost登録
                            $contPost = $this->post->updateOrCreate(
                                [
                                    'id' => $contPostArr['rel_id'],
                                ],
                                [
                                    'rel_id'=> $postRelId, 
                                    'block'=> $blockKey,
                                    'url'=> $contPostArr['url'],
                                    'title'=> $contPostArr['title'],
                                    'detail'=> $contPostArr['detail'],
                                    'is_section'=> 0,
                                    'is_intro' => $isIntro,
                                    'sort_num'=> $nn+1,
                                    'mid_title_id' => $midTitleId,
                                ]
                            );
                            
                            //$nnn++;
                                                            
                            //画像UP
                            if(isset($contPostArr['del_img']) && $contPostArr['del_img']) { //削除チェックの時
                                if(isset($contPost->img_path)) {
                                    Storage::delete($contPost->img_path); //Storageはpublicフォルダのあるところをルートとしてみる
                                    
                                    $contPost->img_path = null;
                                    $contPost->save();
                                }
                            }
                            else {
                                if(isset($contPostArr['img'])) {
                                
                                    $filename = $contPostArr['img']->getClientOriginalName();
                                    $filename = str_replace(' ', '_', $filename);
                                    
                                    $fNameArr = explode('.', $filename);
                                    $filename = $fNameArr[0] . '-' . time() . '.' . array_pop($fNameArr); //array_pop 配列最後（拡張子を取得） end()でも可。mt_rand(0, 99999)
                                    
                                    $filename = 'post/' . $postRelId . '/' . $blockKey . '/' . $filename;

                                    //new File()は画像情報を取得するためのもの。 new File('aaa.jpg')とすると、$vals['img'] or $request->file('img')と同じものになる
                                    
                                    //$path = $vals['img']->store($dirName); //ファイル名が自動生成される
                                    //Storage::putFile($dirName, $vals['img']); //上と同じ
                                    
                                    //$path = $vals['img']->storeAs($dirName, 'abc'); //ファイル名を独自指定(拡張子が付かない)
                                    $path = $contPostArr['img']->storeAs('public', $filename);
                                    
                                    //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
                                    //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
                                                            
                                    $contPost->img_path = $path;
                                    $contPost->save();
                                }
                            }
                        
                        }
                        
                        
                        $nn++;
                    }
                }
                    
                    /*
                    else {
                    	
//                        print_r($midTitleArr);
//                        exit;
                    
                        //relationのidをinput-hiddenに設定し（0ならcreate）$vals['rel_id']でupdateOrCreateする方法もあり
                        $contPost = $this->post->updateOrCreate(
                            [
                                'id' => $vals['rel_id'],
                            ],
                            [
                                'rel_id'=> $postRelId, 
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
                    */

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
                    
                    /*
                    if(isset($vals['del_img']) && $vals['del_img']) { //削除チェックの時
                    	if(isset($contPost->img_path)) {
                            Storage::delete($contPost->img_path); //Storageはpublicフォルダのあるところをルートとしてみる
                            
                            $contPost->img_path = null;
                            $contPost->save();
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
                            
                            $filename = 'post/' . $postRelId . '/' . $blockKey . '/' . $filename;

                            //new File()は画像情報を取得するためのもの。 new File('aaa.jpg')とすると、$vals['img'] or $request->file('img')と同じものになる
                            
                            //$path = $vals['img']->store($dirName); //ファイル名が自動生成される
                            //Storage::putFile($dirName, $vals['img']); //上と同じ
                            
                            //$path = $vals['img']->storeAs($dirName, 'abc'); //ファイル名を独自指定(拡張子が付かない)
                            $path = $vals['img']->storeAs('public', $filename);
                            
                            //$path = Storage::disk('s3')->putFileAs($filename, $request->file('thumbnail'), 'public');
                            //$path = $request->file('thumbnail')->storeAs('', $filename, 's3');
                                                    
                            $contPost->img_path = $path;
                            $contPost->save();
                        }
                    }
                    
                    
                    */
                    
                    //if(! $isSection) $num++;

            } //foreach
            
        } //foreach
        
        
        // ***** h2タイトルのIDをここでセットする。$midTitleArrは保存中に取得するh2タイトルのIDを保存した配列 *******
//        $this->post->where(['rel_id'=>$postRelId, 'is_section'=>0])->get()->map(function($contPost) use($midTitleArr){
//            $contPost->update([
//            	'mid_title_id' => $midTitleArr[$contPost->sort_num],
//            ]);
//        });
        
        
        //最後のmid_titleに対してブロックがあるかどうかを判別する
        //最後のmidTitlePostを取得
        $lastMidPost = $this->post->where(['rel_id'=>$postRelId, 'is_section'=>1])->where('sort_num', '>', 0)->whereNotNull('title')->orderBy('sort_num', 'desc')->first();

		//取得したmidTitlePostのsort_num以上のcontentsPostを取得
		$checkPosts = $this->post->where(['rel_id'=>$postRelId, 'is_section'=>0])->where('sort_num', '>=', $lastMidPost->sort_num)->get();
        
        $res = 0;
        foreach($checkPosts as $checkPost) {
        	if(isset($checkPost->img_path) || isset($checkPost->title) || isset($checkPost->detail)) {
            	$res = 1;
                break;
            }
        }
        
        if(! $res) {
        	$status .= '<br><span class="text-danger">確認して下さい！ 最後の中タイトルに対してブロックが未入力のようです。</span>';
        }
	
		
        
        //タグのsave動作
        if(isset($data['tags'])) {
            
            $tagArr = $data['tags'];
            
            //タグ削除の動作
            if(isset($editId)) { //編集時のみ削除されたタグを消す
            	//現在あるtagRelを取得
                $tagRelIds = $this->postTagRel->where('postrel_id', $postRelId)->get()->map(function($tagRelObj){
                    return $tagRelObj->tag_id;
                })->all();
                
                //入力されたtagのidを取得（新規のものは取得されない->する必要がない）
                $tagIds = $this->tag->whereIn('name', $tagArr)->get()->map(function($tagObj){
                    return $tagObj->id;
                })->all();
                
                //配列同士を比較(重複しないものは$tagRelIdsからreturnされる->これらが削除対象となる)
                $tagDiffs = array_diff($tagRelIds, $tagIds);
                
                //削除対象となったものを削除する
                if(count($tagDiffs) > 0) {
                    foreach($tagDiffs as $valTagId) {
                        $this->postTagRel->where(['postrel_id'=>$postRelId, 'tag_id'=>$valTagId])->first()->delete();
                    }
                }
            }
            
        	$num = 1;
            
            foreach($tagArr as $tag) {
                
                //Tagセット
                $setTag = Tag::firstOrCreate(['name'=>$tag]); //既存を取得 or なければ作成
                
                if(!$setTag->slug) { //新規作成時slugは一旦NULLでcreateされるので、その後idをセットする
                    $setTag->slug = $setTag->id;
                    $setTag->save();
                }
                
                $tagId = $setTag->id;
                $tagName = $tag;


                //tagIdがRelationになければセット ->firstOrCreate() ->updateOrCreate()
                $this->postTagRel->updateOrCreate(
                    ['tag_id'=>$tagId, 'postrel_id'=>$postRelId],
                    ['sort_num'=>$num]
                );

				$num++;
                
                //tagIdを配列に入れる　削除確認用
                //$tagIds[] = $tagId;
            }
        
        	/*
            //編集時のみ削除されたタグを消す
            if(isset($editId)) {
                //元々relationにあったtagがなくなった場合：今回取得したtagIdの中にrelationのtagIdがない場合をin_arrayにて確認
                $tagRels = $this->tagRelation->where('item_id', $itemId)->get();
                
                foreach($tagRels as $tagRel) {
                    if(! in_array($tagRel->tag_id, $tagIds)) {
                        $tagRel->delete();
                    }
                }
            }
            */
        }
        else { 
        	if(isset($editId)) {
        		$tagRels = $this->postTagRel->where('postrel_id', $postRelId)->delete();
            }
        }
        
        
        
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
