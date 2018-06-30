/* config.js */

slideshow_css = '$CssPath$style.css';

if (!parseInt(params.noFrame)){
	// frame border+shadow
	var border = { 'top': 5, 'right': 5, 'bottom': 39, 'left': 5 };
	var ContaienerW = imageW + border.left + border.right;
	var ContaienerH = imageH + border.top + border.bottom;
	params.frameL = Math.round(100*100*border.left/imageW)/100;
	params.frameT = Math.round(100*100*border.top/imageH)/100;
	params.frameW = Math.floor(100*100*(imageW+border.left+border.right)/imageW)/100;
	params.frameH = Math.floor(100*100*(imageH+border.top+border.bottom)/imageH)/100;
	files.push({ 'src': 'backgnd/'+params.TemplateName+'/bg.png',     'filters': [ { 'name': 'resize', 'width': ContaienerW, 'height': ContaienerH, 'margins': border } ] });
	files.push( { 'src': 'backgnd/'+params.TemplateName+'/style-shadow.css', 'dest': slideshow_css, 'filters': ['params'] } );
	params.BulletsBottom = -24;
	params.backMarginsTop    += border.top;
	params.backMarginsBottom += border.bottom;
}
else{
	params.BulletsBottom = 5;
}

params.decorW = params.ImageWidth - 8*2;
params.decorH = params.ImageHeight - 8*2;

files.push({ 'src': 'backgnd/'+params.TemplateName+'/bullet.png' });
files.push({ 'src': 'backgnd/'+params.TemplateName+'/arrows.png' });
files.push({ 'src': 'backgnd/'+params.TemplateName+'/index.html', 'filters': ['params'] });


if (params.ShowTooltips){
	params.ThumbWidthHalf = Math.round(params.ThumbWidth/2);
	files.push(	{ 'src': 'backgnd/'+params.TemplateName+'/triangle-'+params.TooltipPos+'.png', dest: '$ImgPath$triangle.png' } );
	files.push( { 'src': 'backgnd/'+params.TemplateName+'/style-tooltip.css', 'dest': slideshow_css, 'filters': ['params'] } );
}

if (params.Thumbnails){
	//width:135%; /* (tumb_border*2+tumb_margin+tumb_width)*(k_images) / ((98%)*img_width) = (5*2+3+240)*(5)/ (0.98*960 ) = 1265/941 =~ 135% */	
	params.thumbFullWidth  = 5*2+6+parseInt(params.ThumbWidth );
	params.thumbFullHeight = 5*2+6+parseInt(params.ThumbHeight);
}

params.addWidth = 60; // arrow width

// call this function at the end of each template
finalize();