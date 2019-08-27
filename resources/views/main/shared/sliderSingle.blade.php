<?php
// for Single Slick Slider
?>

<div class="slider-wrap">

    @if(isset($imgsPri))

        <div class="slider slider-single">
            
            <div class="position-relative">
            	
                
                <?php $mainCaption = ''; ?>
                    
                @if(isset($item->main_caption))
                    <?php $mainCaption = $item->main_caption; ?>
                    <div class="s-caption">
                        {{ $mainCaption }}
                    </div>
                @endif
                
                @if(! Ctm::isAgent('sp'))
                <a href="{{ Storage::url($item->main_img) }}" data-lightbox="{{ $item->number }}" data-title="{{ $mainCaption }}">
                @endif
               
                    <img class="d-block w-100" src="{{ Storage::url($item->main_img) }}" alt="{{ $item->title }}">
                
                @if(! Ctm::isAgent('sp'))
                </a>
                @endif
            
            	
            </div>
            
            
            <?php $n = 0; ?>

            @foreach($imgsPri as $itemImg)
            
            	@if($itemImg->img_path !== null )
                <div class="position-relative">
                    
                    <?php $caption = ''; ?>
                    
                    @if(isset($itemImg->caption))
                        <?php $caption = $itemImg->caption; ?>
                        <div class="s-caption">
                            {{ $caption }}
                        </div>
                    @endif
                    
                
                    @if(! Ctm::isAgent('sp'))
                        <a href="{{ Storage::url($itemImg->img_path)}}" data-lightbox="{{ $item->number }}" data-title="{{ $caption }}">
                    @endif
                    
                    <img class="d-block w-100" src="{{ Storage::url($itemImg->img_path) }}" alt="">
                    
                    @if(! Ctm::isAgent('sp'))
                        </a>
                    @endif
                    
                </div>
                
            	<?php $n++; ?>
                
                @endif

            @endforeach
        </div>

    @endif

	
    <div>
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
    

</div>
