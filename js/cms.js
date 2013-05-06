//$(document).ready(function() {
$('.datepicker').datepicker();	
$('.editor').wysihtml5({
	"font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
	"emphasis": true, //Italics, bold, etc. Default true
	"lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
	"html": true, //Button which allows you to edit the generated HTML. Default false
	"link": true, //Button to insert a link. Default true
	"image": false, //Button to insert an image. Default true,
	"color": false //Button to change color of font  
});

function addImageResize() {
	var table = $('.image-resize').closest('table');
	//console.log("'get all rows with class of 'image-resize'");
	var resizes = $('.image-resize');
	//console.log("clone first resize, insert at top");
	$(resizes[0]).clone().appendTo(table);
	//console.log("reset");
	$('.image-resize:nth-child('+(resizes.length+2)+') input[type=text]').val('');
	$('.image-resize:nth-child('+(resizes.length+2)+') input[type=checkbox]').removeAttr('checked');	
	$('.image-resize:nth-child('+(resizes.length+2)+') select').val('');
	
	//console.log('added item #'+(resizes.length+1));
	
	//$('.image-resize:nth-child('+(resizes.length+2)+') select').attr('name','whoanelly');
	
	updateImageResizeNames();
}

function removeImageResize(target) {
	var conf = confirm('Are you SURE you want to delete this image size?');
	if (conf) {
		$(target).closest('.image-resize').remove();
	} 
	
	updateImageResizeNames();
}

function updateImageResizeNames() {
	console.log('updateImageResizeNames');
//	var resizes = $$('.image-resize');
	var resizes = $('.image-resize');
	var i = 0;
	
	// set name to image_folder[i]
	$('.image-resize select').each(function(){
		$(this).attr('name','image_folder['+i+']');
		i++;
	});
	
	i = 0;	
	// set name to image_width[i]
	$('.image-resize input[name*="image_width"]').each(function(){
		$(this).attr('name','image_width['+i+']');
		i++;
	});
	
	i = 0;
	// set name to image_height[i]
	$('.image-resize input[name*="image_height"]').each(function(){
		$(this).attr('name','image_height['+i+']');
		i++;
	});
	
	i = 0;
	// set name to image_crop[i]
	$('.image-resize input[name*="image_crop"]').each(function(){
		$(this).attr('name','image_crop['+i+']');
		i++;
	});
	
	i = 0;
	// set name to image_thumbnail[i]
	$('.image-resize input[name*="image_thumbnail"]').each(function(){
		$(this).attr('name','image_thumbnail['+i+']');
		i++;
	});
	
	
	
//	for(var i=0; i<resizes.length; i++) {
	
//		var folder = resizes[i].select('select')[0];
//		$(folder).setAttribute('name', 'image_folder['+i+']');
//		
//		var inputs = resizes[i].select('input');
//		var width = inputs[0];
//		$(width).setAttribute('name', 'image_width['+i+']');
//		var height = inputs[1];
//		$(height).setAttribute('name', 'image_height['+i+']');
//		var crop = inputs[2];
//		$(crop).setAttribute('name', 'image_crop['+i+']');
//		var thumbnail = inputs[3];
//		$(thumbnail).setAttribute('name', 'image_thumbnail['+i+']');
//		
//	}
	
}


//});