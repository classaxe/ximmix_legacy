/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for ecl plugin dialog.
 *
 */

'use strict';

CKEDITOR.dialog.add(
  'placeholder',
  function( editor ) {
    var lang = editor.lang.placeholder,
    generalLabel = editor.lang.common.generalTab,
    validNameRegex = /^[^\[\]\<\>]+$/;
    return {
      title:        lang.title,
      minWidth:     300,
      minHeight:    80,
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
            id:         'name',
            type:       'text',
            style:      'width: 100%;',
            label:      lang.name,
            required:   true,
            validate:   CKEDITOR.dialog.validate.regex( validNameRegex, lang.invalidName ),
            setup: function( widget ) {
              this.setValue( widget.data.name );
            },
            commit: function( widget ) {
              widget.setData( 'name', this.getValue() );
            }
          }
        ]
      }]
    };
  }
);