/* config.js */
params.ContaienerW = imageW + backMargins.left + backMargins.right;
params.PageBgColor = params.PageBgColor||"#d7d7d7";
slideshow_css = '$CssPath$style.css';
params.addCss='@import url("http://fonts.googleapis.com/css?family=Source+Sans+Pro&subset=latin,latin-ext");';

params.Border = parseInt(params.noFrame)? "none": "2px solid #FFFFFF";

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