jQuery(document).ready(function($) {

$('input#clear-cache').click(function() {
	$('form#slideshow-settings #submit').trigger('click');
});
	
});