
@if(count($upperRelArr) > 0)

<?php

    $chunkNum = 0;
    $chunkNumArr = ['a'=>1, 'b'=>3, 'c'=>3];
    
//    print_r($upperRelArr);
//    exit;
    
    $moreClass = '';
?>

@if($upperMore)
    <?php // $moreClass = 'upper-open filter-blur'; ?>
    
    {{--
    <div class="btn btn-block btn-custom upper-tgl">
        詳しく見る <i class="fal fa-angle-down"></i>
    </div>
    --}}
@endif

{{--
<div class="{{ $moreClass }}">
--}}

<div class="upper-wrap">
    
    @foreach($upperRelArr as $blockKey => $upperRels)
        <?php
            //ここでのblockKeyは [a],[b],[c]
            $chunkNum++;
                        
            //echo count($upperRels);
            
            if($upperMore && ($blockKey == 'b' || $blockKey == 'c' )) {
                $moreClass = ' upper-open';
                //tglボタンはこのループの最後（aの時）にセット 133行目あたり
            }
        ?>
        

        <div class="block-wrap{{ $moreClass }}">

            @foreach($upperRels as $key => $upperRel)
            
                <?php
                    //ここでのkeyは [section], [mid_section], [block]
                ?>
                
                @if($key === 'mid_section')
                	<?php continue; ?>
                    
                @elseif($key === 'section')
                    
                    @if(isset($upperRel->title) && $upperRel->title != '')
                        <h3>{!! $upperRel->title !!}</h3>
                    @endif
                    
                @else

                    <div class="{{ $blockKey }}-block-wrap clearfix">

                        <?php
                            //chunkNumはroopでカウント。a->1, b->2, c->3でchunkする
                            //$chunks = array_chunk($upperRel, $chunkNum);
                            
                        	$chunks = array();
                            $chunks = array_chunk($upperRel, $chunkNumArr[$blockKey]);
                            
//                            if($blockKey === 'a') 
//                                $chunks = array_chunk($upperRel, 1);
//                            elseif($blockKey === 'b') 
//                                $chunks = array_chunk($upperRel, 3);
//                            elseif($blockKey === 'c') 
//                                $chunks = array_chunk($upperRel, 3);
                        ?>
                        
                        
                        @foreach($chunks as $chunkKey => $chunk)
                        	
                            @if(isset($upperRels['mid_section'][$chunkKey]) && $upperRels['mid_section'][$chunkKey]->title != '')
                                <h4>{{ $upperRels['mid_section'][$chunkKey]->title }}</h4>
                            @endif
                            
                            
                            <div class="clearfix">
 
                                @foreach($chunk as $uRel)
                                    
                                    @if(!isset($uRel->img_path) && !isset($uRel->title) && !isset($uRel->detail))
                                        <?php
                                        	continue;
                                        ?>
                                    @endif
                                    
                                    
                                    <div class="{{ $blockKey }}-block clearfix ">

                                        @if(isset($uRel->img_path))
                                            <div class="img-wrap">
                                            	@if(isset($uRel->url))
                                                	<a href="{{ $uRel->url }}">
                                                		<img src="{{ Storage::url($uRel->img_path) }}" class="w-100">
                                                    </a>
                                                @else
                                                	<img src="{{ Storage::url($uRel->img_path) }}" class="w-100">
                                                @endif
                                            </div>
                                        @endif
                                        
                                        @if(isset($uRel->title) || isset($uRel->detail))
                                            <div class="detail-wrap">
                                                @if(isset($uRel->title))
                                                    <h5>
                                                    	@if(isset($uRel->url))
                                                			<a href="{{ $uRel->url }}">{{ $uRel->title }}</a>
                                                        @else
                                                        	{{ $uRel->title }}
                                                        @endif
                                                    </h5>
                                                @endif
                                                
                                                @if(isset($uRel->detail))
                                                    <div>
                                                        {!! nl2br($uRel->detail) !!}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        
                                    </div>
                                @endforeach
                                
                            </div>
                        @endforeach
                         
                    </div>
                    
                @endif
                
            @endforeach
            
        </div>
        
        @if($upperMore && $blockKey == 'a')
            <div style="margin-top:-1em;" class="text-right mb-0 mr-3 clearfix">
                <span class="text-linkblue upper-tgl">詳しく見る <i class="fal fa-angle-down"></i></span>
            </div>
        @endif
        
    @endforeach
</div>


@include('main.shared.upperExp', ['orgObj'=>$itemCont])


@endif

