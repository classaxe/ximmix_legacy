// 1.0.4
/* First line must show version number - update as builds change

Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license

Version History:
  1.0.4 (2015-02-02)
    1) Now with unix-style line endings

*/

'use strict';

( function() {
  CKEDITOR.plugins.add(
    'video', {
      requires: 'dialog,widget',
      lang:     'en',
      icons:    'video',
      hidpi:    true,
      init: function( editor ) {
        var lang = editor.lang.video;
        CKEDITOR.dialog.add(
          'video',
          this.path + 'dialogs/video.js'
        );
        editor.widgets.add(
          'video', {
            dialog:     'video',
            draggable:  false,
            pathName:   lang.pathName,
            template:   '<span class="cke_video">[video: ]</span>',
            downcast:   function() {
              return new CKEDITOR.htmlParser.text(this.data.name);
            },
            init:   function() {
              var i, tag, tag_arr, tag_bits;
              tag = this.element.getText().slice(7,-1).trim();
              tag_arr = tag.split('|');
              this.setData('tag_flv',tag_arr.shift());
              if (tag_arr.length>0){
                this.setData('tag_jpg',tag_arr.shift());
              }
              if (tag_arr.length>0){
                this.setData('tag_width',tag_arr.shift());
              }
              if (tag_arr.length>0){
                this.setData('tag_height',tag_arr.shift());
              }
            },
            data:   function( data ) {
              var tag =
                '[video: ' +
                (this.data.tag_flv ?             this.data.tag_flv : '') +
                (this.data.tag_jpg ?        '|'+ this.data.tag_jpg : '') +
                (this.data.tag_width ?      '|'+ this.data.tag_width : '') +
                (this.data.tag_height ?     '|'+ this.data.tag_height : '') +
                ']';
              this.element.setText(tag);
              this.setData('name', tag );
            }
          }
        );
        if ( editor.addMenuItems ){
          editor.addMenuGroup( 'video', 20 );
          editor.addMenuItems( {
            video: {
              label:    lang.edit,
              command:  'video',
              group:    'video',
              order:    1,
              icon:     'video'
            }
          } );
          if ( editor.contextMenu ){
            editor.contextMenu.addListener(
              function( element, selection ){
                if (
                  !element ||
                  !element.getChild ||
                  !element.getChild(0).getAttribute ||
                  element.getChild(0).getAttribute('data-widget')!=='video'
                ){
                  return null;
                }
                return { video : CKEDITOR.TRISTATE_OFF };
              }
            );
          }
        }
        editor.ui.addButton && editor.ui.addButton(
          'Video', {
            label:      lang.toolbar,
            command:    'video',
            toolbar:    'insert,5',
            icon:       'video'
          }
        );
      },

      afterInit: function( editor ) {
        editor.dataProcessor.dataFilter.addRules( {
          text: function( text, node ) {
            var dtd = node.parent && CKEDITOR.dtd[ node.parent.name ];
            // Skip the case when video is in elements like <title> or <textarea>
            // but upcast video in custom elements (no DTD).
            if ( dtd && !dtd.span ){
              return;
            }
            var regExp = /\[video\:[^\]]+\]/g;
            return text.replace(
              regExp,
              function( match ) {
                var innerElement = new CKEDITOR.htmlParser.element(
                  'span', {
                    'class': 'cke_video'
                  }
                );
                innerElement.add(
                  new CKEDITOR.htmlParser.text( match )
                );
                var widgetWrapper = editor.widgets.wrapElement(
                  innerElement,
                  'video'
                );
                return widgetWrapper.getOuterHtml();
              }
            );
          }
        } );
      }
  } );
} )();
