/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for video plugin dialog.
 *
 */

'use strict';

CKEDITOR.dialog.add(
  'video',
  function( editor ) {
    var lang = editor.lang.video,
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
              "<p>"+lang.info_2+"<\/p>"+
              "<\/div>"
          },
          {
            type:       'hbox',
            widths:     [ 300, 110 ],
            align:      'right',
            children:   [
              {
                id:         'txtFLV',
                type:       'text',
                label:      lang.flv_file,
                required:   true,
                onChange:   function(){
                  var dialog = this.getDialog();
                  var newUrl = this.getValue();
                },
                setup: function( widget ) {
                  this.setValue( widget.data.tag_flv );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_flv', this.getValue() );
                },
                validate : CKEDITOR.dialog.validate.notEmpty( lang.flv_missing )
              },
              {
                type:           'button',
                id:             'browse',
                style:          'display:inline-block;margin-top:12px;',
                align:          'center',
                label:          editor.lang.common.browseServer,
                hidden:         true,
                filebrowser:    'info:txtFLV'
              }
            ]
          },
          {
            type:       'hbox',
            widths:     [ 300, 110 ],
            align:      'right',
            children:   [
              {
                id:         'txtJPG',
                type:       'text',
                label:      lang.jpg_file,
                required:   false,
                onChange:   function(){
                  var dialog = this.getDialog();
                  var newUrl = this.getValue();
                },
                setup: function( widget ) {
                  this.setValue( widget.data.tag_jpg );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_jpg', this.getValue() );
                }
              },
              {
                type:           'button',
                id:             'browse',
                style:          'display:inline-block;margin-top:12px;',
                align:          'center',
                label:          editor.lang.common.browseServer,
                hidden:         true,
                filebrowser:    'info:txtJPG'
              }
            ]
          },
          {
            type:       'hbox',
            widths:     [ '50%', '50%' ],
            style:      'width: 300px',
            align:      'left',
            children:   [
              {
                id:         'txtWidth',
                type:       'text',
                style:      'width: 50px',
                label:      lang.width,
                required:   false,
                setup: function( widget ) {
                  this.setValue( widget.data.tag_width );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_width', this.getValue() );
                },
                validate : CKEDITOR.dialog.validate.integer( lang.width_invalid )
              },
              {
                id:         'txtHeight',
                style:      'width: 50px',
                type:       'text',
                label:      lang.height,
                required:   false,
                setup: function( widget ) {
                  this.setValue( widget.data.tag_height );
                },
                commit: function( widget ) {
                  widget.setData( 'tag_height', this.getValue() );
                },
                validate : CKEDITOR.dialog.validate.integer( lang.height_invalid )
              }
            ]
          }
        ]
      }]
    };
  }
);