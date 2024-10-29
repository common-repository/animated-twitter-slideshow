jQuery(document).ready(function($) {

//Grab Vars
var atsSliderDelay = $('#ats-slider').attr('data-delay');
var atsNumSlides = $('#ats-slider').attr('data-num-slides');

//Timer
timer = setInterval( function() { atsMover('up'); }, atsSliderDelay*1000); 
function atsResetSlideTimer(){
	clearInterval(timer);
	timer = setInterval( function() { atsMover('up'); }, atsSliderDelay*1000); 
}

//Navigation
$('#next').on('click',function(e) {
	atsMover('up');	
	});
	
$('#prev').on('click',function(e) {
	atsMover('down');
	});

$('.tweet-counter').each(function(e) {
	$(this).on('click', function() {
		var atsSlideNum = $(this).attr('data-slide');
		atsMover(atsSlideNum);
	});
});

//Touch Navigation
$(".ats-item").touchwipe({
     wipeLeft: function(){ atsMover('up')},
     wipeRight: function(){ atsMover('down')},
     min_move_x: 100,
     min_move_y: 100,
     preventDefaultEvents: true
});

//Mover Function
function atsMover(a){
	$('.ats-item-wrapper').removeClass('active');
	$('.tweet-counter').removeClass('lit');
	var atsCurrentSlideIndex = $('#ats-slider').attr('data-current-index');
	if( a=='up' ){
		if( atsCurrentSlideIndex == atsNumSlides ) {
			atsCurrentSlideIndex = 1;
		}else{
			atsCurrentSlideIndex++;
		}
	} else if( a=='down') {
		if( atsCurrentSlideIndex == 1 ) {
			atsCurrentSlideIndex = atsNumSlides;
		}else{
			atsCurrentSlideIndex--;
		}
	} else {
		atsCurrentSlideIndex = a;
	}
	var marginLeft = '-'+(atsCurrentSlideIndex-1)*100+'%';
	$('#ats-item-'+(atsCurrentSlideIndex)).addClass('active');
	$('#ats-slider').attr('data-current-index',atsCurrentSlideIndex);
	$('.tweet-counter-'+atsCurrentSlideIndex).addClass('lit');
	$('.ats-slider-wrapper').css({'margin-left': marginLeft });
	atsResetSlideTimer();
}

//Chage class based on size
window.addEventListener('resize', function() {
   atsSizeClasser();
});

function atsSizeClasser() {
	//clear class size
	$('#ats-slider').removeClass();
	//get current size
	var atsCurrentWidth = $('#ats-slider').width();
	//add class
	switch(true) {
		case (0 < atsCurrentWidth && atsCurrentWidth <= 480 ) :
		$('#ats-slider').addClass('ats-xsm');
		break;
		case (481 < atsCurrentWidth && atsCurrentWidth <= 768 ) :
		$('#ats-slider').addClass('ats-sml');
		break;
		case ( 769 < atsCurrentWidth && atsCurrentWidth <= 1200 ) :
		$('#ats-slider').addClass('ats-med');
		break;
		case ( 1201 < atsCurrentWidth && atsCurrentWidth <= 1600 ):
		$('#ats-slider').addClass('ats-lrg');
		break;		
	}
	$('.ats-slider-wrapper').addClass('active');
}

//Make tweet text link
$('.tweet-wrapper p').each(function() {
	$(this).on('click', function() {
	var atsTweetLink = $(this).attr('data-link');
	window.open(
  	  atsTweetLink,
  	  '_blank'
  	);
	});
});

$(".tweet-wrapper p a").click(function (event) {
	event.stopPropagation();
});
    
//Preload BG Images and Init
var atsImagesArray = [];
$('.ats-bg-image').each(function() {
	var atsBgImage = $(this).css('background-image').replace('url(','').replace(')','').replace(/\"/gi, "");
	if( atsBgImage !='' ){
		atsImagesArray.push(atsBgImage);
	}
});

var promises = [];
for (var i = 0; i < atsImagesArray.length; i++) {
	(function(url, promise) {
		var atsImg = new Image();
		atsImg.onload = function() {
		promise.resolve();
    };
		atsImg.src = url;
	})(atsImagesArray[i], promises[i] = $.Deferred());
}
$.when.apply($, promises).done(function() {
	//Start Slider
	$('#ats-item-1').addClass('active');
	$('.tweet-counter-1').addClass('lit');
	atsSizeClasser();
	atsResetSlideTimer();
	});


});

