<?php
// for Single Slider Bootstrap
// draggableが効かない
?>


<div id="carouselExampleIndicators" class="carousel slide" data-ride="false" data-interval="false">

    <div class="carousel-inner">
    <div class="carousel-item active">
        
        <?php $mainCaption = ''; ?>
        
        @if(isset($item->main_caption))
            <?php $mainCaption = $item->main_caption; ?>
            <div class="carousel-caption d-block">
                {{ $mainCaption }}
            </div>
        @endif
        
        @if(! Ctm::isAgent('sp'))
        <a href="{{ Storage::url($item->main_img) }}" data-lightbox="{{ $item->number }}" data-title="{{ $mainCaption }}">
        @endif
       
            <img class="d-block w-100" src="{{ Storage::url($item->main_img) }}" alt="First slide">
        
        @if(! Ctm::isAgent('sp'))
        </a>
        @endif
    </div>

    @foreach($imgsPri as $itemImg)
        
        <?php $caption = ''; ?>
        
        @if($itemImg->img_path !== null )
            <div class="carousel-item">
                @if(isset($itemImg->caption))
                    <?php $caption = $itemImg->caption; ?>
                    <div class="carousel-caption d-block">
                        {{ $caption }}
                    </div>
                @endif
                
                @if(! Ctm::isAgent('sp'))
                <a href="{{ Storage::url($itemImg->img_path)}}" data-lightbox="{{ $item->number }}" data-title="{{ $caption }}">
                @endif
                
                    <img class="d-block w-100" src="{{ Storage::url($itemImg->img_path)}}" alt="Sub slide">
                
                @if(! Ctm::isAgent('sp'))
                </a>
                @endif
            </div>
        @endif
    @endforeach



    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"><i class="fal fa-angle-left"></i></span>
    <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"><i class="fal fa-angle-right"></i></span>
    <span class="sr-only">Next</span>
    </a>

    </div>

    <ol class="carousel-indicators clearfix">
    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active">
        <img class="img-fluid" src="{{ Storage::url($item->main_img) }}" alt="slide">
    </li>

    <?php 
        $count = count($imgsPri);
        $n = 1;
    ?>

    @foreach($imgsPri as $img)
        @if($img->img_path !== null )
            <li data-target="#carouselExampleIndicators" data-slide-to="{{$n}}">
                <img class="img-fluid" src="{{ Storage::url($img->img_path)}}" alt="slide">
            </li>
            
            <?php $n++; ?>
        @endif
    @endforeach
    </ol>
</div>
    
    