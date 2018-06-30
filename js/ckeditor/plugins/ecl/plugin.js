// 1.0.10
/* First line must show version number - update as builds change

Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license

Version History:
  1.0.10 (2015-02-02)
    1) Now with unix-style line endings

*/

'use strict';

( function() {
  CKEDITOR.plugins.add(
    'ecl', {
      requires: 'ajax,dialog,widget,xml',
      lang:     'en',
      icons:    'ecl',
      hidpi:    true,
      init: function( editor ) {
        var lang = editor.lang.ecl;
        CKEDITOR.dialog.add(
          'ecl',
          this.path + 'dialogs/ecl.js'
        );
        editor.widgets.add(
          'ecl', {
            dialog:     'ecl',
            draggable:  false,
            pathName:   lang.pathName,
            template:   '<span class="cke_ecl">[ECL][/ECL]</span>',
            downcast:   function() {
              return new CKEDITOR.htmlParser.text(this.data.name);
            },
            init:   function() {
              var tag = this.element.getText().slice( 5, -6);
              var tag_arr = tag.split(':');
              this.setData('tag_t',tag_arr[0]);
              this.setData('tag_i',(tag_arr.length==2 ? tag_arr[1] : ''));
            },
            data:   function( data ) {
              var tag =
                '[ECL]' +
                this.data.tag_t +
                (this.data.tag_i ? ':'+this.data.tag_i : '') +
                '[/ECL]';
              this.element.setText(tag);
              this.setData('name', tag );
            }
          }
        );
        if ( editor.addMenuItems ){
          editor.addMenuGroup( 'ecl', 20 );
          editor.addMenuItems( {
            ecl: {
              label:    lang.edit,
              command:  'ecl',
              group:    'ecl',
              order:    1,
              icon:     'ecl'
            }
          } );
          if ( editor.contextMenu ){
            editor.contextMenu.addListener(
              function( element, selection ){
                if (
                  !element ||
                  !element.getChild ||
                  !element.getChild(0).getAttribute ||
                  element.getChild(0).getAttribute('data-widget')!=='ecl'
                ){
                  return null;
                }
                return { ecl : CKEDITOR.TRISTATE_OFF };
              }
            );
          }
        }
        editor.ui.addButton && editor.ui.addButton(
          'ECL', {
            label:      lang.toolbar,
            command:    'ecl',
            toolbar:    'insert,5',
            icon:       'ecl'
          }
        );
      },

      afterInit: function( editor ) {
        editor.dataProcessor.dataFilter.addRules( {
          text: function( text, node ) {
            var dtd = node.parent && CKEDITOR.dtd[ node.parent.name ];
            // Skip the case when ecl is in elements like <title> or <textarea>
            // but upcast ecl in custom elements (no DTD).
            if ( dtd && !dtd.span ){
              return;
            }
            var regExp = /\[ECL\][^\[]+\[\/ECL\]/g;
            return text.replace(
              regExp,
              function( match ) {
                var innerElement = new CKEDITOR.htmlParser.element(
                  'span', {
                    'class': 'cke_ecl'
                  }
                );
                innerElement.add(
                  new CKEDITOR.htmlParser.text( match )
                );
                var widgetWrapper = editor.widgets.wrapElement(
                  innerElement,
                  'ecl'
                );
                return widgetWrapper.getOuterHtml();
              }
            );
          }
        } );
      }
  } );
} )();
