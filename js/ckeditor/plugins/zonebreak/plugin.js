// 1.0.2
/* First line must show version number - update as builds change

Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license

Version History:
  1.0.2 (2015-02-02)
    1) Now with unix-style line endings

*/

'use strict';

( function() {
  CKEDITOR.plugins.add(
    'zonebreak', {
    requires: 'fakeobjects',
    lang: 'en',
    icons: 'zonebreak',
    hidpi: true,
    init: function( editor ){
      editor.ui.addButton(
        'ZoneBreak', {
          label: 'Insert Zone Break',
          command: 'zonebreak',
          toolbar: 'links,100'
        }
      );
      editor.addCommand( 'zonebreak', {
        exec: function() {
          var images = editor.document.getElementsByTag( 'img' );
          for ( var i = 0, len = images.count() ; i < len ; i++ ){
            var img = images.getItem( i );
            if ( img.hasClass( 'cke_zone_break' ) ) {
              img.remove();
            }
          }
          insertComment('zonebreak','cke_zone_break','[zone break]');
        }
      }
      );
      function insertComment(text,css,tag) {
        if ( !CKEDITOR.dom.comment.prototype.getAttribute ) {
          CKEDITOR.dom.comment.prototype.getAttribute = function() { return ''; };
          CKEDITOR.dom.comment.prototype.attributes = { align : '' };
        }
        var fakeElement = editor.createFakeElement(
          new CKEDITOR.dom.comment( text ),
          css,
          tag
        );
        var range = editor.getSelection().getRanges()[0],
        elementsPath = new CKEDITOR.dom.elementPath( range.getCommonAncestor( true ) ),
        element = ( elementsPath.block && elementsPath.block.getParent() ) || elementsPath.blockLimit, hasMoved;
        while ( element && element.getName() != 'body' ) {
          range.moveToPosition( element, CKEDITOR.POSITION_AFTER_END );
          hasMoved = 1;
          element = element.getParent();
        }
        if (!hasMoved ){
          range.splitBlock( 'p' );
        }
        range.insertNode( fakeElement );
        var next = fakeElement;
        while ( ( next = next.getNext() ) && !range.moveToElementEditStart( next ) ){

        }
        range.select();
      }
    },
    afterInit : function( editor ) {
      editor.dataProcessor.dataFilter.addRules( {
        comment: function( value ){
          if ( !CKEDITOR.htmlParser.comment.prototype.getAttribute ) {
            CKEDITOR.htmlParser.comment.prototype.getAttribute = function() { return ''; };
            CKEDITOR.htmlParser.comment.prototype.attributes = { align : '' };
          }
          if ( value == 'zonebreak' ) {
            return editor.createFakeParserElement(
              new CKEDITOR.htmlParser.comment( 'zonebreak' ),
              'cke_zone_break',
              '[zone break]'
            );
          }
          return value;
        }
      });
    }
  });
})();