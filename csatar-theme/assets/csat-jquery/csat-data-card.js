$( document ).ready(function() {
	console.log($(document).width());
	if ($(document).width()>767){
		$(".collapse-on-sm").removeClass("collapse"); 
	} 
});