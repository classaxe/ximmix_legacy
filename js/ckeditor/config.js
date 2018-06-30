// 1.0.23
/* First line must show version number - update as builds change

Version History:
  1.0.23 (2015-02-02)
    1) Now with unix-style line endings

  (Older version history in config.txt)

Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

  config['filebrowserBrowseUrl'] =          '/js/ckfinder/ckfinder.html';
  config['filebrowserImageBrowseUrl'] =     config['filebrowserBrowseUrl']+'?type=Image';
  config['filebrowserImageBrowseLinkUrl'] = config['filebrowserBrowseUrl'];
  config['filebrowserFlashBrowseUrl'] =     config['filebrowserBrowseUrl']+'?type=Flash';
  config.enterMode = CKEDITOR.ENTER_BR;
  config.fontSize_sizes = '8pt/8pt;9pt/9pt;10pt/10pt;11pt/11pt;12pt/12pt;14pt/14pt;16pt/16pt;18pt/18pt;20pt/20pt;22pt/22pt;24pt/24pt;26pt/26pt;28pt/28pt;36/36pt;48/48pt;72/72px';
  config.extraPlugins = 'audio,ecl,more,video,youtube,zonebreak';
  config.ignoreEmptyParagraph = true;
  config.fillEmptyBlocks = false;
  config.allowedContent = true;

  config.toolbar_About = [
    { name: 'document',     items : ['Format','Bold','Italic','Strike','Subscript','Superscript','-','NumberedList','BulletedList','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight'] },
    { name: 'insert',       items : ['Link','Unlink'] },
    { name: 'undo',         items : ['Undo','Redo','Source'] },
    { name: 'media',        items : ['About'] }
  ];

  config.toolbar_Basic = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    { name: 'link',		    items : [ 'Link','Unlink' ] },
    { name: 'insert',		items : [ 'SpecialChar'] },
    { name: 'break',		items : [ 'HorizontalRule' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Category = [
    { name: 'document',     items : [ 'Source','Maximize','-','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','PasteText','-','RemoveFormat' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'link',		    items : [ 'Link','Unlink' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image'] },
    { name: 'media',		items : [ 'Audio','Youtube','Video'] },
    { name: 'break',		items : [ 'HorizontalRule' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Community_Basic = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'link',		    items : [ 'Link','Unlink' ] },
    { name: 'insert',		items : [ 'SpecialChar'] },
    { name: 'break',		items : [ 'HorizontalRule','More' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Community_Content = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    { name: 'link',		    items : [ 'Link','Unlink' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image'] },
    { name: 'media',		items : [ 'Audio','Youtube','Video'] },
    { name: 'break',		items : [ 'HorizontalRule','More' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Community_Sponsor_Info = [
    { name: 'document',     items : [ 'Source','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'link',		    items : [ 'Link','Unlink' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image'] },
    { name: 'style',        items : [ 'Format','Font','FontSize'] }
  ];

  config.toolbar_Email_Wizard = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat'] },
    { name: 'search',       items : [ 'Find','Replace' ] },
    '/',
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    { name: 'link',		    items : [ 'Link','Unlink','Anchor' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','Underline','Strike','-','Subscript','Superscript' ] },
    { name: 'break',		items : [ 'HorizontalRule' ] },
    '/',
    { name: 'insert',		items : [ 'SpecialChar','-','Image','Table','-','ECL'] },
    { name: 'colors',       items : [ 'TextColor','BGColor'] },
    { name: 'style',        items : [ 'Format','Font','FontSize'] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Email_Template_HTML = [
    { name: 'document',     items : [ 'Source','Maximize','-','ShowBlocks','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'link',		    items : [ 'Link','Unlink' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image','Table','-','ECL'] },
    { name: 'break',		items : [ 'HorizontalRule' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Email_Template_HTML_Full = [
    { name: 'document',     items : [ 'Source','Maximize','-','ShowBlocks','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'search',       items : [ 'Find','Replace' ] },
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    { name: 'link',		    items : [ 'Link','Unlink','Anchor' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','Underline','Strike','-','Subscript','Superscript' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image','Table','-','ECL'] },
    { name: 'break',		items : [ 'HorizontalRule' ] },
    { name: 'colors',       items : [ 'TextColor','BGColor'] },
    { name: 'style',        items : [ 'Format','Font','FontSize'] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Email_Template_Text = [
    { name: 'document',     items : [ 'Source'] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','ECL'] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Layout = [
    { name: 'document',     items : [ 'Source','Maximize','-','ShowBlocks' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','PasteText' ] },
    { name: 'basicstyle',   items : [ 'Bold','Italic','-','Subscript','Superscript' ] },
    { name: 'link',		    items : [ 'Link','Unlink','Anchor' ] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image','Flash','Table'] },
    { name: 'media',		items : [ 'ECL','Audio','Youtube','Video'] },
    { name: 'break',		items : [ 'HorizontalRule' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Page = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','-','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'search',       items : [ 'Find','Replace' ] },
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    { name: 'link',		    items : [ 'Link','Unlink','Anchor' ] },
    '/',
    { name: 'basicstyle',   items : [ 'Bold','Italic','Underline','Strike','-','Subscript','Superscript' ] },
    { name: 'colors',       items : [ 'TextColor','BGColor'] },
    { name: 'style',        items : [ 'Format','Font','FontSize'] },
    { name: 'insert',		items : [ 'SpecialChar','-','Image','Flash','Table'] },
    { name: 'media',		items : [ 'ECL','Audio','Youtube','Video'] },
    { name: 'break',		items : [ 'HorizontalRule','-','ZoneBreak' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_Posting = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'search',       items : [ 'Find','Replace' ] },
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    '/',
    { name: 'basicstyle',   items : [ 'Bold','Italic','Underline','Strike','-','Subscript','Superscript' ] },
    (cke_posting_fonts ? { name: 'colors',       items : [ 'TextColor','BGColor'] } : ''),
    (cke_posting_fonts ? { name: 'style',        items : [ 'Format','Font','FontSize'] } : ''),
    { name: 'insert',		items : [ 'SpecialChar','-','Image','Flash','Table'] },
    { name: 'link',		    items : [ 'Link','Unlink','Anchor' ] },
    { name: 'media',		items : [ 'ECL','Audio','Youtube','Video'] },
    { name: 'break',		items : [ 'HorizontalRule','More'] },
  ];

  config.toolbar_Public_Submission = [
    { name: 'document',     items : [ 'Source','Maximize','-','Print','Scayt' ] },
    { name: 'undo',         items : [ 'Undo','Redo' ] },
    { name: 'clipboard',    items : [ 'SelectAll','Cut','Copy','Paste','PasteText','PasteFromWord','-','RemoveFormat' ] },
    { name: 'search',       items : [ 'Find','Replace' ] },
    { name: 'paragraph',    items : [ 'JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']},
    { name: 'block',        items : [ 'NumberedList','BulletedList','-','Outdent','Indent']},
    '/',
    { name: 'basicstyle',   items : [ 'Bold','Italic','Underline','Strike','-','Subscript','Superscript' ] },
    { name: 'link',		    items : [ 'Link','Unlink','Anchor' ] },
    { name: 'break',		items : [ 'HorizontalRule','More' ] },
    { name: 'help',		    items : [ 'About'] }
  ];

  config.toolbar_WP = [
    // Only used now in salvationist pages form
    { name: 'document',     items : ['Format','Bold','Italic','Strike','-','NumberedList','BulletedList','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight'] },
    { name: 'insert',       items : ['Link','Unlink','Image','More'] },
    { name: 'undo',         items : ['Undo','Redo','Source'] },
    { name: 'media',        items : ['ECL','Audio','Youtube','Video','-','About'] }
  ];

  config.toolbar_Public_Submission = [
    // Might be used in 'The Auroran'
    ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
    ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
    ['OrderedList','UnorderedList','-','Outdent','Indent','Blockquote'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
    ['Link','Unlink','Anchor'],
    ['Image','Rule','SpecialChar'],
    ['TextColor','BGColor'],
    ['FontFormat','FontName','FontSize']
  ];

  config.toolbar_Article =       config.toolbar_Posting;
  config.toolbar_Event =         config.toolbar_Posting;
  config.toolbar_Gallery_Image = config.toolbar_Posting;
  config.toolbar_Gallery_Album = config.toolbar_Posting;
  config.toolbar_Job =           config.toolbar_Posting;
  config.toolbar_News =          config.toolbar_Posting;
  config.toolbar_Podcast =       config.toolbar_Posting;
  config.toolbar_Podcast_Album = config.toolbar_Posting;
  config.toolbar_Product =       config.toolbar_Posting;
  config.toolbar_Survey =        config.toolbar_Posting;
};
