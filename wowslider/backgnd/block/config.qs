/* config.js */
params.prevCaption = 'prev';
params.nextCaption = 'next';

slideshow_css = '$CssPath$style.css';

files.push({ 'src': 'common/index.html', 'filters': ['params'] });


if (params.ShowTooltips){
	params.ThumbWidthHalf = Math.round(params.ThumbWidth/2);
	files.push( { 'src': 'backgnd/'+params.TemplateName+'/style-tooltip.css', 'dest': slideshow_css, 'filters': ['params'] } );
}

if (params.Thumbnails){
	//width:135%; /* (tumb_border*2+tumb_margin+tumb_width)*(k_images) / ((98%)*img_width) = (5*2+3+240)*(5)/ (0.98*960 ) = 1265/941 =~ 135% */	
	params.thumbFullWidth  = 2*2+6+parseInt(params.ThumbWidth );
	params.thumbFullHeight = 2*2+6+parseInt(params.ThumbHeight);
}

// call this function at the end of each template
finalize();