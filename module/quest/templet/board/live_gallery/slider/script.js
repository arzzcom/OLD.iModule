(function($) {
	$.fn.parallaxSlider = function(options) {
		var opts = $.extend({}, $.fn.parallaxSlider.defaults, options);
		return this.each(function() {
			var $slider_container 	= $(this),
			o 				= $.meta ? $.extend({}, opts, $slider_container.data()) : opts;
			
			//the main slider
			var $slider_slider		= $('.slider_slider',$slider_container),
			//the elements in the slider
			$elems			= $slider_slider.children(),
			//total number of elements
			total_elems		= $elems.length,
			//the navigation buttons
			$slider_next		= $('.slider_next',$slider_container),
			$slider_prev		= $('.slider_prev',$slider_container),
			//current image
			current			= 0,
			//the thumbs container
			$slider_thumbnails = $('.slider_thumbnails',$slider_container),
			//the thumbs
			$thumbs			= $slider_thumbnails.children(),
			//the interval for the autoplay mode
			slideshow,
			//the loading image
			$slider_loading	= $('.slider_loading',$slider_container),
			$slider_slider_wrapper = $('.slider_slider_wrapper',$slider_container);
				
			//first preload all the images
			var loaded		= 0,
			$images		= $slider_slider_wrapper.find('img');
			
			$images.each(function(){
				var $img	= $(this);
				$('<img/>').load(function(){
					++loaded;
					
					if(loaded	== total_elems*2){
						$slider_loading.hide();
						$slider_slider_wrapper.show();

						$slider_thumbnails.css({
							'width'			: $(slider_container).width() + 'px',
							'margin-left' 	: -$(slider_container).width()/2 + 'px',
						});
						var spaces	= $(slider_container).width()/(total_elems+1);
						
						$elems.find('img').each(function() {
							var width = $(this).width();
							var height = $(this).height(); 
							if (width + 100 > $slider_container.width()) {
								width = $slider_container.width()-100;
								height = $(this).height()*width/$(this).width();
							}
							$(this).css('width',width+'px');
							$(this).css('height',height+'px');
							
							if (height + 100 > $slider_container.height()) {
								$slider_container.css('height',height+100);
							}
							
							$(this).css('margin-top',(($slider_container.height()-height)/2-20));
						});
						
						
						$slider_slider.width($slider_container.width()*total_elems + 'px');
						$elems.width($slider_container.width() + 'px');
						$slider_next.css({'right':'10px','top':($slider_container.height()/2-40)+'px'});
						$slider_prev.css({'left':'10px','top':($slider_container.height()/2-40)+'px'});
						$slider_thumbnails.css('top',($slider_container.height()-100)+'px');
						
						
						$thumbs.each(function(i){
							var $this 	= $(this);
							var left	= spaces*(i+1) - $this.width()/2;
							$this.css('left',left+'px');
							
							if(o.thumbRotation){
								var angle 	= Math.floor(Math.random()*41)-20;
								$this.css({
									'-moz-transform'	: 'rotate('+ angle +'deg)',
									'-webkit-transform'	: 'rotate('+ angle +'deg)',
									'transform'			: 'rotate('+ angle +'deg)',
								});
							}
							//hovering the thumbs animates them up and down
							$this.bind('mouseenter',function(){
								$(this).stop().animate({top:'-10px'},100);
							}).bind('mouseleave',function(){
								$(this).stop().animate({top:'0px'},100);
							});
						});
							
						//make the first thumb be selected
						highlight($thumbs.eq(0));
							
						//slide when clicking the navigation buttons
						$slider_next.bind('click',function(){
							++current;
							if(current >= total_elems)
								if(o.circular)
									current = 0;
							else{
								--current;
								return false;
							}
							highlight($thumbs.eq(current));
							slide(current,
							$slider_slider,
							o.speed,
							o.easing,
							o.easingBg);
						});
						$slider_prev.bind('click',function(){
							--current;
							if(current < 0)
								if(o.circular)
									current = total_elems - 1;
							else{
								++current;
								return false;
							}
							highlight($thumbs.eq(current));
							slide(current,
							$slider_slider,
							o.speed,
							o.easing,
							o.easingBg);
						});
				
						/*
						clicking a thumb will slide to the respective image
						 */
						$thumbs.bind('click',function(){
							var $thumb	= $(this);
							highlight($thumb);
							//if autoplay interrupt when user clicks
							if(o.auto)
								clearInterval(slideshow);
							current 	= $thumb.index();
							slide(current,
							$slider_slider,
							o.speed,
							o.easing,
							o.easingBg);
						});
				
					
				
						/*
						activate the autoplay mode if
						that option was specified
						 */
						if(o.auto != 0){
							o.circular	= true;
							slideshow	= setInterval(function(){
								$slider_next.trigger('click');
							},o.auto);
						}
					}
				}).error(function(){
					alert('here')
				}).attr('src',$img.attr('src'));
			});
				
				
				
		});
	};
	
	var slide			= function(current,
	$slider_slider,
	speed,
	easing,
	easingBg){
		var slide_to	= parseInt(-$(slider_container).width() * current);
		$slider_slider.stop().animate({
			left	: slide_to + 'px'
		},speed, easing);
	}
	
	var highlight		= function($elem){
		$elem.siblings().removeClass('selected');
		$elem.addClass('selected');
	}
	
	$.fn.parallaxSlider.defaults = {
		auto			: 0,	//how many seconds to periodically slide the content.
		speed			: 1000,//speed of each slide animation
		easing			: 'jswing',//easing effect for the slide animation
		easingBg		: 'jswing',//easing effect for the background animation
		circular		: true,//circular slider
		thumbRotation	: true//the thumbs will be randomly rotated
	};
	//easeInOutExpo,easeInBack
})(jQuery);

$(function() {
	var $slider_container	= $('#slider_container');
	$slider_container.parallaxSlider();
});