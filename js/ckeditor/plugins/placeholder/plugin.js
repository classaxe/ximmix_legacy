
/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview The "placeholder" plugin.
 *
 */

'use strict';

( function() {
  CKEDITOR.plugins.add(
    'placeholder', {
      requires: 'dialog,widget',
      lang:     'ar,bg,ca,cs,cy,da,de,el,en,en-gb,eo,es,et,eu,fa,fi,fr,fr-ca,gl,he,hr,hu,id,it,ja,km,ko,ku,lv,nb,nl,no,pl,pt,pt-br,ru,si,sk,sl,sq,sv,th,tr,ug,uk,vi,zh,zh-cn', // %REMOVE_LINE_CORE%
      icons:    'placeholder',
      hidpi:    true,
      onLoad: function() {
        CKEDITOR.addCss( '.cke_placeholder{background-color:#f8f}' );
      },
      init: function( editor ) {
        var lang = editor.lang.placeholder;
        CKEDITOR.dialog.add(
          'placeholder',
          this.path + 'dialogs/placeholder.js'
        );
        editor.widgets.add(
          'placeholder', {
            dialog:     'placeholder',
            draggable:  false,
            pathName:   lang.pathName,
            template:   '<span class="cke_placeholder">[[]]</span>',
            downcast: function() {
              return new CKEDITOR.htmlParser.text( '[[' + this.data.name + ']]' );
            },
            init: function() {
              this.setData( 'name', this.element.getText().slice( 2, -2 ) );
            },
            data: function( data ) {
              this.element.setText( '[[' + this.data.name + ']]' );
            }
          }
        );
        if ( editor.addMenuItems ){
          editor.addMenuGroup( 'placeholder', 20 );
          editor.addMenuItems( {
            placeholder: {
              label:    lang.edit,
              command:  'placeholder',
              group:    'placeholder',
              order:    1,
              icon:     'placeholder'
            }
          } );
          if ( editor.contextMenu ){
            editor.contextMenu.addListener(
              function( element, selection ){
                if ( !element || !element.getChild || !element.getChild(0).getAttribute || element.getChild(0).getAttribute('data-widget')!=='placeholder'){
                  return null;
                }
                return { placeholder : CKEDITOR.TRISTATE_OFF };
              }
            );
          }
        }
        editor.ui.addButton && editor.ui.addButton(
          'CreatePlaceholder', {
            label:      lang.toolbar,
            command:    'placeholder',
            toolbar:    'insert,5',
            icon:       'placeholder'
          }
        );
      },

      afterInit: function( editor ) {
        var placeholderReplaceRegex = /\[\[([^\[\]])+\]\]/g;
        editor.dataProcessor.dataFilter.addRules( {
          text: function( text, node ) {
            var dtd = node.parent && CKEDITOR.dtd[ node.parent.name ];
            // Skip the case when placeholder is in elements like <title> or <textarea>
            // but upcast placeholder in custom elements (no DTD).
            if ( dtd && !dtd.span ){
              return;
            }
            return text.replace(
              placeholderReplaceRegex,
              function( match ) {
                var widgetWrapper, innerElement;
                innerElement = new CKEDITOR.htmlParser.element(
                  'span', {
                    'class': 'cke_placeholder'
                  }
                );
                innerElement.add(
                  new CKEDITOR.htmlParser.text( match )
                );
                widgetWrapper = editor.widgets.wrapElement(
                  innerElement,
                  'placeholder'
                );
                return widgetWrapper.getOuterHtml();
              }
            );
          }
        } );
      }
  } );
} )();
