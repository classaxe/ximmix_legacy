/* config.js */
params.ContaienerW = imageW + backMargins.left + backMargins.right;
params.ContaienerH = imageH + backMargins.bottom;

if(!parseInt(params.noFrame)){
	params.ShadowW = Math.round(params.ContaienerW*1.4);
	params.ShadowH = Math.round(params.ContaienerH/2.12);
	files.push( { 'src': 'backgnd/'+params.TemplateName+'/style-shadow.css', 'dest': '$CssPath$style.css', 'filters': ['params'] } );
	files.push({ 'src': 'backgnd/'+params.TemplateName+'/shadow.png', 'filters': [ { 'name': 'resize', 'width': params.ShadowW, 'height': params.ShadowH } ] });
}

files.push({ 'src': 'backgnd/'+params.TemplateName+'/bullet.png' });
files.push({ 'src': 'backgnd/'+params.TemplateName+'/arrows.png' });
files.push({ 'src': 'common/index.html', 'filters': ['params'] });


if (params.ShowTooltips){
	params.ThumbWidthHalf = Math.round(params.ThumbWidth/2);
	files.push(	{ 'src': 'backgnd/'+params.TemplateName+'/triangle-'+params.TooltipPos+'.png', dest: '$ImgPath$triangle.png' } );
	files.push( { 'src': 'backgnd/'+params.TemplateName+'/style-tooltip.css', 'dest': '$CssPath$style.css', 'filters': ['params'] } );
}

if (params.Thumbnails){
	//width:135%; /* (tumb_border*2+tumb_margin+tumb_width)*(k_images) / ((98%)*img_width) = (5*2+3+240)*(5)/ (0.98*960 ) = 1265/941 =~ 135% */	
	params.thumbFullWidth  = 5*2+6+parseInt(params.ThumbWidth );
	params.thumbFullHeight = 5*2+6+parseInt(params.ThumbHeight);
}

// call this function at the end of each template
finalize();