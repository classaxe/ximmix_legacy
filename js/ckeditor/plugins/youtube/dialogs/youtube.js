/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for youtube plugin dialog.
 *
 */

'use strict';

CKEDITOR.dialog.add(
  'youtube',
  function( editor ) {
    var lang = editor.lang.youtube,
    generalLabel = editor.lang.common.generalTab;
    return {
      title:        lang.title,
      minWidth:     474,
      minHeight:    120,
      contents:     [{
        id:     'info',
        label:  generalLabel,
        title:  generalLabel,
        elements: [
          {
            type:   'html',
            html:
              "<div id='cke_custom_info'>"+
              "<img class='info_icon' src='\/img\/sysimg\/icon_info.gif' alt='i' \/>"+
              "<p>"+lang.info_1+"<\/p>"+
              "<\/div>"
          },
          {
            id:         'txtMP3',
            type:       'text',
            label:      lang.mp3_file,
            required:   true,
            onChange:   function(){
              var dialog = this.getDialog();
              var newUrl = this.getValue();
            },
            setup: function( widget ) {
              this.setValue( widget.data.tag_path );
            },
            commit: function( widget ) {
              widget.setData( 'tag_path', this.getValue() );
            },
            validate : CKEDITOR.dialog.validate.notEmpty( lang.mp3_missing )
          },
          {
            type:   'hbox',
            widths: [ '30%', '30%', '40%' ],
            style:  'width: 300px',
            align:  'left',
            children : [
              {
                id:     'txtWidth',
                type:   'text',
                style:  'width: 50px',
                label:  lang.width,
                required: false,
                setup: function( widget ) {
                  this.setValue( widget.data.tag_width );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_width', this.getValue() );
                },
                validate : CKEDITOR.dialog.validate.integer( lang.width_invalid )
              },
              {
                id:     'txtHeight',
                style:  'width: 50px',
                type:   'text',
                label:  lang.height,
                required: false,
                setup: function( widget ) {
                  this.setValue( widget.data.tag_height );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_height', this.getValue() );
                },
                validate : CKEDITOR.dialog.validate.integer( lang.height_invalid )
              },
              {
                id:     'txtStart',
                style:  'width: 80px',
                type:   'text',
                label:  lang.start,
                required: false,
                setup: function( widget ) {
                  this.setValue( widget.data.tag_start );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_start', this.getValue() );
                }
              },
            ]
          }
        ]
      }]
    };
  }
);