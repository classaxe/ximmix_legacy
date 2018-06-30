/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for ecl plugin dialog.
 *
 */

'use strict';

function ecl_nameable(obj){
  var element = obj.getInputElement().$;
  var option = element.options[element.selectedIndex];
  var nameable = (option.className.indexOf('nameable')!==-1 ? true : false);
  var tag_instance = obj.getDialog().getContentElement('info', 'tag_instance').getInputElement().$
  tag_instance.disabled=!nameable;
  tag_instance.style.backgroundColor=(nameable ? 'rgb(255, 255, 255)' : 'rgb(224, 224, 224)');
  tag_instance.style.color=(nameable ? 'rgb(0, 0, 0)' : 'rgb(64, 64, 64)');

}

CKEDITOR.dialog.add(
  'ecl',
  function( editor ) {
    var lang = editor.lang.ecl,
    generalLabel = editor.lang.common.generalTab,
    validNameRegex = /^[^\[\]\:]*$/;
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
            id:         'tag_type',
            type:       'select',
            style:      'width: 100%;',
            items:      [],
            label:      lang.type,
            onChange: function(){ ecl_nameable(this); },
            setup: function( widget ) {
              var data, i, options, tags;
              data = CKEDITOR.ajax.load('../../../?mode=fck&submode=ecl');
              tags = eval('('+data+')');
              options = [''];
              for(i=0; i<tags.length; i++){
                options.push(
                  "<option"+
                  (tags[i][2]=='1' || tags[i][1].substr(0,2)=='[E' ?
                    " class='" +
                    (tags[i][2]=='1' ? 'nameable' : '') +
                    (tags[i][1].substr(0,2)=='[E' ? ' email' : '') +
                    "'"
                  :
                    ""
                  ) +
                  (tags[i][0]==widget.data.tag_t ? " selected='selected'" : '') +
                  " value=\""+tags[i][0]+"\""+
                  " title=\"[ECL]"+tags[i][0]+"[/ECL]\""+
                  ">"+
                  tags[i][1]+
                  "</option>\n"
                );
              }
              var id = this.getInputElement();
              id.addClass('cke_selector_ecl');
              id.setHtml(options.join(''));
            },
            commit: function( widget ) {
              var element = this.getInputElement().$;
              var option = element.options[element.selectedIndex];
              var nameable = (option.className.indexOf('nameable')!==-1 ? true : false);
              widget.setData( 'tag_t', this.getValue() );
              widget.setData( 'tag_nameable', (nameable ? 1 : 0) );
            }
          },
          {
            id:         'tag_instance',
            type:       'text',
            style:      'width: 100%;',
            label:      lang.instance,
            required:   false,
            validate:   CKEDITOR.dialog.validate.regex( validNameRegex, lang.invalidName ),
            setup: function( widget ) {
              this.setValue( widget.data.tag_i );
            },
            commit: function( widget ) {
              widget.setData( 'tag_i', (widget.data.tag_nameable ? this.getValue() : '') );
            }
          }
        ]
      }]
    };
  }
);