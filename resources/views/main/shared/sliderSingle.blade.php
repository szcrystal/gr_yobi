<?php
// for SinglePage => Slick Slider
// Why Unmerge ??
// abcde
?>

<div class="slider-wrap">

    <div class="slider slider-single">
        <span>ABCDE</span>
        <div class="position-relative">

            <?php 
                $mainCaption = isset($item->main_caption) ? $item->main_caption : ''; 
            
                $imgFormat = '<img class="d-block w-100" src="%s" alt="%s">';
                $imgTag = sprintf($imgFormat , Storage::url($item->main_img), $item->title);
            ?>
                
            @if($mainCaption != '')
                <div class="s-caption">
                    {{ $mainCaption }}
                </div>
            @endif
            
            @if(! Ctm::isAgent('sp'))
                <a href="{{ Storage::url($item->main_img) }}" data-lightbox="{{ $item->number }}" data-title="{{ $mainCaption }}">
                    {!! $imgTag !!}
                </a>
            @else
                {!! $imgTag !!}
            @endif
           
        </div>
        
        @if(isset($imgsPri))
            @foreach($imgsPri as $itemImg)
            
                @if($itemImg->img_path !== null )
                    <div class="position-relative">
                        
                        <?php 
                            $caption = isset($itemImg->caption) ? $itemImg->caption : '';
                            $imgTag = sprintf($imgFormat , Storage::url($itemImg->img_path), $caption);
                        ?>
                        
                        @if($caption != '')
                            <div class="s-caption">
                                {{ $caption }}
                            </div>
                        @endif
                        
                    
                        @if(! Ctm::isAgent('sp'))
                            <a href="{{ Storage::url($itemImg->img_path)}}" data-lightbox="{{ $item->number }}" data-title="{{ $caption }}">
                                {!! $imgTag !!}
                            </a>
                        @else
                            {!! $imgTag !!}
                        @endif
                        
                    </div>
                                    
                @endif

            @endforeach
        @endif
        
    </div>

	@if(isset($imgsPri))
        <div class="clearfix">
            <ul class="carousel-indicators">
            
                <li class="slider-item active">
                    <img class="img-fluid" src="{{ Storage::url($item->main_img) }}" alt="">
                </li>
                
                @foreach($imgsPri as $img)
                    @if($img->img_path !== null )
                        <li class="slider-item">
                            <img class="img-fluid" src="{{ Storage::url($img->img_path) }}" alt="">
                        </li>
                    @endif
                @endforeach
            
            </ul>
        </div>
    @endif

</div>
