// 1.0.142
/* First line must show version number - update as builds change

Version History:
  1.0.142 (2015-02-02)
    1) Now with unix-style line endings

  (Older version history in member.txt)
*/

// ************************************
// * External References (for JSLint) *
// ************************************
/*jslint browser: true, evil: true, maxerr: 200 */

// Browser:
/*global alert, confirm, document, prompt, window, Option */

// Inline code:
/*global base_url, currency_symbol, global_active_btns, option_separator,
  pwd_len_min, rating_blocks, system_family, valid_prefix */

// Inline code when needed:
/*global destID, pr_destID, cp_params, config_rows */

// functions.js:
/*global isNS4, isNS6, isW3C, isIE, isIE4, isIE5, isIE55, isIE6, isIE7, isIE8, isIE_lt6, isIE_lt7, isIE_lt8,
  addEvent, ajax_post, attach_field_behaviour, combo_selector_set,
  cursorGetSelectionEnd, cursorGetSelectionStart, cursorIsAtEnd, cursorIsAtStart, cursorSetPosition,
  geid, geid_set, geid_val,
  popWin, popup_dialog, popWin_post, radio_group_get, radio_group_set, setDisplay, show_section, two_dp
*/
// js_context: cm_html is sometimes changed here
/*global cm_html: true */

// ckeditor code
/*global CKEDITOR */

sajax.config_display = function(result, div, build_version) {
  var assoc = [];
  var out = [];
  var db_cs_target = '', classes_cs_target='', libraries_cs_actual='', reports_cs_actual='';
  for(var i=1; i<=result[0]; i++) {
    if (result[i].category=='config') {
      assoc[result[i].title] = result[i].content;
    }
    if (result[i].category=='config' && result[i].title=='classes_cs_target') {
      classes_cs_target = result[i].content;
    }
    if (result[i].category=='config' && result[i].title=='db_cs_target') {
      db_cs_target = result[i].content;
    }
    if (result[i].category=='config' && result[i].title=='libraries_cs_target') {
      libraries_cs_target = result[i].content;
    }
    if (result[i].category=='config' && result[i].title=='reports_cs_target') {
      reports_cs_target = result[i].content;
    }
  }
  if (result[0]>2) {
    // do first - uses data that is about to be changed
    assoc.build_version_good =  (assoc.build_version==build_version ? true : false);
    assoc.classes_cs_actual =   sajax.config_display_helper(assoc.classes_cs_status,    assoc.classes_cs_actual,    classes_cs_target);
    assoc.db_cs_actual =        sajax.config_display_helper(assoc.db_cs_status,         assoc.db_cs_actual,         db_cs_target);
    assoc.libraries_cs_actual = sajax.config_display_helper(assoc.libraries_cs_status,  assoc.libraries_cs_actual,  libraries_cs_target);
    assoc.reports_cs_actual =   sajax.config_display_helper(assoc.reports_cs_status,    assoc.reports_cs_actual,    reports_cs_target);
    // Do second - changes data we just used
    assoc.akismet_key_status =  sajax.config_display_helper(assoc.akismet_key_status);
    assoc.bugtracker_status =   sajax.config_display_helper(assoc.bugtracker_status);
    assoc.build_version =       "<a href=\"#\" onclick=\"version('"+assoc.build_version+"');return false;\"><b>"+assoc.build_version+"</b></a>";
    assoc.classes_cs_status =   sajax.config_display_helper(assoc.classes_cs_status);
    assoc.db_cs_status =        sajax.config_display_helper(assoc.db_cs_status);
    assoc.google_key_status =   sajax.config_display_helper(assoc.google_key_status);
    assoc.heartbeat_status =    sajax.config_display_helper(assoc.heartbeat_status);
    assoc.config_status =       sajax.config_display_helper(assoc.config_status);
    assoc.htaccess_status =     sajax.config_display_helper(assoc.htaccess_status);
    assoc.libraries_cs_status = sajax.config_display_helper(assoc.libraries_cs_status);
    assoc.reports_cs_status =   sajax.config_display_helper(assoc.reports_cs_status);
  }
  out.push("<table cellpadding='2' cellspacing='0' class='report'"+(assoc.build_version_good ? " style='background:#e8ffe8'" : ""));
  assoc.URL = "<a href=\"javascript:void popWin('"+assoc.URL+"','"+assoc.URL.replace(/[.\/\:]+/g,'_')+"','scrollbars=1,resizable=1,status=0',800,600,'centre');\"><b>"+assoc.URL+"</b></a>";
  for (i=0; i < config_rows.length; i++) {
    out.push("<tr>");
    out.push((config_rows[i]=='URL' ? "<td style='background-color: #e8e8e8;border-left:none'>" : "<td style='border-left:none'>"));
    out.push((typeof assoc[config_rows[i]] !='undefined' && assoc[config_rows[i]]!='' ? assoc[config_rows[i]] : '&nbsp;'));
    out.push("</td></tr>");
  }
  out.push("</table>\n");
  geid(div).innerHTML = out.join('');
}

sajax.config_display_helper = function(status, actual, target){
  return (
    status=='Pass' ?
      "<span style='color:#008000'><b>" + (typeof actual!='undefined' ? actual : status) + "</b></span>"
    :
      "<span style='color:#FF0000'><b>" + (typeof actual!='undefined' ? actual : status) + "</b></span>"+
      (typeof target!='undefined' ?
        " <span style='color:#ff8000' title='Expected value'>(Exp " + target + ")</span>"
       :
         ""
      )
    );
}


// ************************************
// * Context Menu Support             *
// ************************************
// Loosely based on code from http://luke.breuer.com/tutorial/js_contextmenu.htm
var _CM =               { type:'' };
var _CM_ID =            [];
var _CM_text =          [];
var _replaceContext =   false;  // replace the system context menu?
var _mouseOverContext = false;  // is the mouse over the context menu?
var _contextActive =    false;

function CM_InitAttachDetect(obj){
  addEvent(obj, 'mouseover', function(e){ _mouseOverContext=true; });
  addEvent(obj, 'mouseout',  function(e){ _mouseOverContext=false; });
}

function CM_InitAttachHighlight(obj){
  addEvent(obj, 'mouseover', function(e){ obj.style.backgroundColor='#ffff80';});
  addEvent(obj, 'mouseout',  function(e){ obj.style.backgroundColor='';});
}

function CM_visible(menu){
  if(!geid(menu)){
    alert(menu+" isn't defined here");
    return;
  }
  return geid(menu).style.display!=='none';
}

function CM_label(ID,text,disabled){
  var obj;
  text =     (typeof text!='undefined' ? text : '');
  disabled = (typeof disabled!='undefined' ? disabled : 0);
  obj = $J('#'+ID);
  if (obj && typeof obj[0]!='undefined'){
    obj[0].innerHTML=(disabled ? "<span class='disabled'>"+text+"</span>": text);
  }
}

function CM_show(ID,show){
  var obj;
  obj = $J('#'+ID);
  if (obj && typeof obj[0]!='undefined'){
    obj[0].style.display=(show ? '': 'none');
  }
}

function CM_CloseContext() {
  _mouseOverContext = false;
  _contextActive = false;
  CM_HideContext();
}

function CM_MouseDown(event) {
  if (_mouseOverContext) {
    return;
  }
  if (!event) { event = window.event; }
  if (event.button === 2 && _CM.type!=='') {
    _replaceContext = true;
    _contextActive = true;
  }
  else if (!_mouseOverContext){
    CM_CloseContext();
  }
}

function CM_InitContext() {
  var elements = geid('CM').getElementsByTagName('div');
  var rexp = new RegExp('\\bcontext_menu\\b');
  for (var i=0; i<elements.length; i++) {
    if (rexp.test(elements[i].className)) {
      CM_InitAttachDetect(elements[i]);
    }
  }
  rexp = new RegExp('\\baction\\b');
  for (i=0; i<elements.length; i++) {
    if (rexp.test(elements[i].className)) {
      CM_InitAttachHighlight(elements[i]);
    }
  }
  document.body.onmousedown = CM_MouseDown;
  document.body.oncontextmenu = CM_ContextShow;
  _contextActive = false;
  CM_CloseContext();
}

function CM_load(){
  geid('CM').innerHTML = cm_html.replace(
    /\[x\]/g,
    "      <div class='cm_close' onclick='CM_CloseContext();'><img src='"+base_url+"img/spacer' class='icons' style='height:10px;width:10px;background-position:-2590px 0px;' alt='[Close context menu]' /></div>"
  );
  CM_InitContext();
}

function CM_Navbutton_Over(
  bID,bStyleID,bcanAddSubnav,bSuiteName,bStyleName
){
  var btn, bHasSubmenu, bSuiteID;
  if (CM_visible('CM_navbutton')) {
    return;
  }
  btn = geid('btn_'+bID).childNodes[0].childNodes[0];
  bSuiteID = geid('btn_'+bID).parentNode.id.substr(4);
  bHasSubmenu = geid('btn_'+bID).getElementsByTagName('UL').length>0 ? 1 : 0;
  _CM.type='navbutton';
  _CM.navbuttonID=bID;
  _CM.navsuiteID=bSuiteID;
  _CM.navstyleID=bStyleID;
  _CM.hasSubmenu=bHasSubmenu;
  _CM.canAddSubnav=bcanAddSubnav;
  _CM.navbuttonText='&quot;'+btn.alt+'&quot;';
  _CM.navsuiteName='&quot;'+bSuiteName+'&quot;';
  _CM.navstyleName='&quot;'+bStyleName+'&quot;';
}

function CM_SDMenu_Over(
  bID,bText,bHasSubmenu,bSubNavStyleID, bSuiteID,bSuiteName,bStyleID, bStyleName
){
  var bHasSubmenu;
  if (CM_visible('CM_navbutton')) {
    return;
  }
  _CM.type='navbutton';
  _CM.navbuttonID=bID;
  _CM.navsuiteID=bSuiteID;
  _CM.navstyleID=bStyleID;
  _CM.navbuttonText='&quot;'+bText+'&quot;';
  _CM.hasSubmenu=bHasSubmenu;
  _CM.canAddSubnav=bSubNavStyleID;
  _CM.navsuiteName='&quot;'+bSuiteName+'&quot;';
  _CM.navstyleName='&quot;'+bStyleName+'&quot;';
}

function community_dropbox_check(url){
  $J.ajax({
    data:       {check_dropbox: 1},
    dataType:   'json',
    type:       'POST',
    url:        url
  })
  .done(function(result){
    for(var i=0; i<result.length; i++){
      switch(result[i]['s']){
        case 0:
          css = 'grey';
        break;
        case 1:
          css = 'green';
        break;
        case 2:
          css = 'red';
        break;
      }
      img = $J('#dropbox_status_'+result[i]['i'])[0];
      img.setAttribute('src',base_url+'img/spacer');
      img.setAttribute('class',css);
      img.setAttribute('title',result[i]['t']);
    }
  });
}


// **************************************
// * Component Parameter Matrix Support *
// **************************************
var div_container;

function cp_popup(){
  if (document.documentMode!='undefined' && document.documentMode==7){
    alert('IMPORTANT NOTICE:\n\nComponent Controls do NOT work with IE8 when running in compatibility mode.\nPlease take your web browser out of compatibity mode and try again.');
    return false;
  }
  var h = "<div id='cp_config' class='cp_config'></div>\n";
  popup_dialog("Component Settings",h,'760',420,'','','', '');
}

function cp_matrix(id,args){
  this.id =	          id;
  this.args =         args;
  this.mainInstance = this;
  this.draw();
  this.activate();
}

function cp_matrix_dosave(id){
  var i, td, value;
  var args = cp_params[id];
  var out =  {'site':[],'layout':[],'item':[]};
  var tbl =  geid('_cp_config');
  var tds =  tbl.getElementsByTagName('td');
  var columns = (cp_params[id].item===false ? 2 : 3);
  for (i=0; i<tds.length; i+=columns){
    if (columns>0){
      td = tds[i];
      value = (td.childNodes.length ? td.childNodes[0].data : '');
      out.site.push(args.ident+args.params[i/columns]+'='+value);
    }
    if (columns>1){
      td = tds[i+1];
      value = (td.childNodes.length ? td.childNodes[0].data : '');
      out.layout.push(args.ident+args.params[i/columns]+'='+value);
    }
    if (columns>2){
      td = tds[i+2];
      value = (td.childNodes.length ? td.childNodes[0].data : '');
      out.item.push(args.ident+args.params[i/columns]+'='+value);
    }
  }
  geid_set('targetValue',$J.toJSON(out));
  geid_set('command','set_parameters');
  geid('form').submit();
}

cp_matrix.prototype.draw = function(){
  var i, div, info_icon, p, inp, tbl, tbody, td, th, thead, tr;
  div_container = geid(this.id);
  p =   document.createElement('p');
  inp = document.createElement('input');
  inp.setAttribute('type','hidden');
  inp.setAttribute('id','cp_matrix_result');
  inp.setAttribute('name','cp_matrix_result');
  p.appendChild(inp);
  inp = document.createElement('input');
  inp.setAttribute('type','hidden');
  inp.setAttribute('id','cp_matrix_id');
  inp.setAttribute('value',this.args.id);
  p.appendChild(inp);
  p.appendChild(document.createTextNode(this.args.ident));
  div_container.appendChild(p);
  div =	document.createElement('div');
  div.setAttribute('class','cp_config_inner');
  div_container.appendChild(div);
  tbl = document.createElement('table');
  tbl.setAttribute('border',1);
  tbl.setAttribute('cellpadding',0);
  tbl.setAttribute('cellspacing',0);
  tbl.id = '_'+this.id;
  div.appendChild(tbl);
  thead = document.createElement('thead');
  tbl.appendChild(thead);
  tr = document.createElement('tr');
  thead.appendChild(tr);
  for (i=0; i<this.args.headings.length; i++){
    th = document.createElement('th');
    tr.appendChild(th);
    th.appendChild(document.createTextNode(this.args.headings[i]));
  }
  tbody = document.createElement('tbody');
  tbl.appendChild(tbody);
  for (i=0; i<this.args.params.length; i++){
    tr = document.createElement('tr');
    tbody.appendChild(tr);
    th = document.createElement('th');
    th.setAttribute('width','150px');
    info_icon = document.createElement('img');
    info_icon.title=this.args.hints[i];
    info_icon.src='/img/spacer';
    info_icon.className='icons';
    info_icon.style.padding='0';
    info_icon.style.margin='2px';
    info_icon.style.width='11px';
    info_icon.style.height='11px';
    info_icon.style.backgroundPosition='-2600px 0px';
    th.appendChild(info_icon);
    th.appendChild(document.createTextNode(this.args.params[i]));
    tr.appendChild(th);
    th = document.createElement('th');
    th.appendChild(document.createTextNode(this.args.defaults[i]));
    tr.appendChild(th);
    td = document.createElement('td');
    td.id = '_'+this.id+'_c1_r'+(i+1);
    td.appendChild(document.createTextNode(this.args.site[i]));
    tr.appendChild(td);
    td = document.createElement('td');
    td.id = '_'+this.id+'_c2_r'+(i+1);
    td.appendChild(document.createTextNode(this.args.layout[i]));
    tr.appendChild(td);
    if (this.args.item===false){
      th = document.createElement('th');
      th.id = '_'+this.id+'_c3_r'+(i+1);
      th.appendChild(document.createTextNode(''));
      tr.appendChild(th);
    }
    else {
      td = document.createElement('td');
      td.id = '_'+this.id+'_c3_r'+(i+1);
      td.appendChild(document.createTextNode(this.args.item[i]));
      tr.appendChild(td);
    }
  }
  div =   document.createElement('div');
  div.style.textAlign='center';
  inp = document.createElement('input');
  inp.setAttribute('type','button');
  inp.setAttribute('id','cp_matrix_cancel');
  inp.setAttribute('value','Cancel');
  inp.setAttribute('onclick',"hidePopWin(null)");
  inp.style.width='60px';
  div.appendChild(inp);
  inp = document.createElement('input');
  inp.setAttribute('type','button');
  inp.setAttribute('id','cp_matrix_save');
  inp.setAttribute('value','Save');
  inp.setAttribute('onclick','cp_matrix_dosave('+this.args.id+');');
  inp.style.width='60px';
  div.appendChild(inp);
  div_container.appendChild(div);
};

cp_matrix.prototype.activate = function(){
  var tbl = geid('_'+this.id);
  for (var i=0; i<tbl.childNodes.length; i++) { 
    var section = tbl.childNodes[i];
    if (section.nodeName=='TBODY'){
      for (var j=0; j<section.childNodes.length; j++){
        var row = section.childNodes[j];
        if (row.nodeName=='TR'){
          for (var k=0; k<row.childNodes.length; k++){
            if (row.childNodes[k].nodeName=='TD'){
              var cell = row.childNodes[k];
              cell.onclick = this.fn_cell_click(this.mainInstance,cell.id);
            }
          }
        }
      }
    }
  }
};

cp_matrix.prototype.fn_input_blur = function(mainInstance,id){
  return function(e){
    var _value = (this.value ? this.value : '');
    var _valNode = document.createTextNode(_value);
    var cell = this.parentNode;
    cell.removeChild(this);
    cell.appendChild(_valNode);
    cell.style.backgroundColor='';
    cell.style.width = '';
    cell.onclick = mainInstance.fn_cell_click(mainInstance,id);
  };
};

cp_matrix.prototype.fn_input_keydown = function(mainInstance,id){
  return function(e){
    var keynum, newpos, direction;
    e = e || window.event;
    keynum = (e.keyCode ? e.keyCode : e.which);
    switch(keynum){
      case 9: // tab key
        if (e.preventDefault){
          e.preventDefault();
        }
        direction = (e.shiftKey ? 'l' : 'r');
        newpos = mainInstance.get_new_pos(mainInstance,id,direction);
        this.blur();
        geid(newpos).onclick();
        return false;
      case 13: // return
        newpos = mainInstance.get_new_pos(mainInstance,id,'r');
        this.blur();
        geid(newpos).onclick();
        return false;
      case 27: // escape
        this.value = mainInstance.old_value;
        this.blur();
        return false;
      case 37: // left key
        if (this.value=='' || cursorIsAtStart(this)){
          newpos = mainInstance.get_new_pos(mainInstance,id,'l');
          this.blur();
          geid(newpos).onclick();
          return false;
        }
      break;
      case 38: // up key
        newpos = mainInstance.get_new_pos(mainInstance,id,'u');
        this.blur();
        geid(newpos).onclick();
        return false;
      case 39: // right key
        if (this.value=='' || cursorIsAtEnd(this)){
          newpos = mainInstance.get_new_pos(mainInstance,id,'r');
          this.blur();
          geid(newpos).onclick();
          return false;
        }
      break;
      case 40: // down key
        newpos = mainInstance.get_new_pos(mainInstance,id,'d');
        this.blur();
        geid(newpos).onclick();
        return false;
    }
    return true;
  };
};

cp_matrix.prototype.get_pos = function(mainInstance,id){
  var pos, prefix;
  prefix = '_'+mainInstance.id+'_c';
  pos =    id.substr(prefix.length).split('_r');
  return {
    'x': parseInt(pos[0],10),
    'y': parseInt(pos[1],10)
  };
};

cp_matrix.prototype.get_new_pos = function(mainInstance,id,direction){
  var pos = this.get_pos(mainInstance,id);
  var x = pos.x;
  var y = pos.y;
  var prefix = '_'+mainInstance.id+'_c';
  var columns =   mainInstance.args.headings.length-2;
  var rows =   mainInstance.args.params.length;
  switch(direction){
    case 'l':
      if (x==1 && y==1){
        return prefix+(columns)+'_r'+rows;
      }
      if (x==1){
        return prefix+(columns)+'_r'+(y-1);
      }
      return prefix+(x-1)+'_r'+y;
    case 'r':
      if (x<columns){
        return prefix+(x+1)+'_r'+y;
      }
      if (y<rows){
        return prefix+'1_r'+(y+1);
      }
      return prefix+'1_r1';
    case 'u':
      if (y==1){
        return prefix+x+'_r'+rows;
      }
      return prefix+x+'_r'+(y-1);
    case 'd':
      if (y==rows){
        return prefix+x+'_r1';
      }
      return prefix+x+'_r'+(y+1);
  }
  return false;
};

cp_matrix.prototype.fn_cell_click = function(mainInstance,id){
  return function(e) {
    this.onclick = null;
    var width=this.clientWidth;
    this.style.backgroundColor='#ffeeee';
    var value = (this.childNodes.length ? this.childNodes[0].data : '');
    mainInstance.old_value = value;
    if (this.childNodes.length){
      this.removeChild(this.childNodes[0]);
    }
    this.style.backgroundColor='#e0e0ff';
    var input = document.createElement('input');
    this.appendChild(input);
    input.style.backgroundColor='#e0e0ff';
    if (isIE_lt8){
      input.style.height='8pt';
      input.style.lineHeight='8pt';
    }
    input.setAttribute('value',value);
    input.style.width = (width-4)+"px";
    input.onblur = mainInstance.fn_input_blur(mainInstance,id);
    input.onkeydown = mainInstance.fn_input_keydown(mainInstance,id);
    input.focus();
    input.select();
  };
};


// ************************************
// * Email Wizard support             *
// ************************************
function emailwizard_preview(){
  var h, i, content, content_2, postvars;
  h = "<div id='emailwizard_preview'></div>";
  popup_dialog("Email Preview",h,'800',420,'Close','','');
  postvars = '';
  for(i=1; i<=geid_val('content_zone_count');i++){
    content = encodeURIComponent(CKEDITOR.instances['content_zone_'+i].getData());
    postvars += 'content_zone_'+i+'='+content+'&';
  }
  postvars +=
    'mailtemplateID='+geid_val('mailtemplateID')+'&'+
    'mailidentityID='+geid_val('mailidentityID')+'&'+
    'groupID='+geid_val('groupID')+'&'+
    'subject='+geid_val('subject')+'&'+
    'submode=preview&rnd='+Math.random();
  ajax_post(base_url+'report/email_wizard','emailwizard_preview',postvars);
}
function emailwizard_cancel(){
  if(confirm('Are you sure?\nAll changes will be lost')){
    window.location=base_url+'report/email_wizard';
  }
}
function emailwizard_queue(){
  if(confirm('Prepare a new Email Job for later delivery?')){
    geid_set('submode','queue');
    geid('form').submit();
  }
}
function emailwizard_send(){
  if(confirm('Queue this message for delivery right now?')){
    geid_set('submode','send');
    geid('form').submit();
  }
}

function gallery_album_sortable_setup(name,parentID,url,onChange_fn){
  var args, data, fn, i, item, obj;
  data = window[name+'_image_list'];
  for(var i=0; i<data.length; i++){
    if (obj = geid(name+"_"+data[i].ID)){
      item = data[i];
      gallery_album_sortable_setup_onmouseover(obj,item,name);
      gallery_album_sortable_setup_onmouseout(obj);
    }
  }
  args = {
    opacity: 0.9,
    update: function(event, ui){
      var items = $J(this).sortable('toArray');
      $J.each(items,function(index,item){items[index] = item.split('_').pop();});
      seq = items.toString();
      window[name+'_sequence']=seq;
      $J.post(
        url,{
          submode:      'gallery_sequence',
          source:       name,
          targetID:     parentID,
          targetValue:  seq
        }
      );
    }
  }
  if (typeof onChange_fn=='function'){
    args.change = onChange_fn;
  }
  $J('#'+name+'_images').sortable(args);
}

function gallery_album_sortable_setup_onmouseover(obj, item, name){
  var fn = function(){
    if(!CM_visible('CM_gallery_image')){
       obj.style.backgroundColor='#ffff80';
        var cm =  (item.subtype.replace('-','_')!=='' ? item.subtype.replace('-','_') : 'gallery_image');
       _CM.category = item.category;
       _CM.enabled = item.enabled;
       _CM.source = name;
       _CM.type=cm;
       _CM.ID=item.ID;
       _CM_text[0]="&quot;"+item.title+"&quot;";
       _CM_ID[2]=item.parentID;
       _CM_text[2]="&quot;"+item.parentTitle+"&quot;";
    }
  };
  addEvent(obj,'mouseover',fn);
}

function gallery_album_sortable_setup_onmouseout(obj){
  var fn = function(){
    obj.style.backgroundColor='';
    _CM.type='';
  };
  addEvent(obj,'mouseout',fn);
}

function gallery_image_edit_click(obj,params,title_h,caption_h){
  var type = obj.id.substr(0,1);
  switch(type){
    case 'c':
      obj.outerHTML=
        "<textarea id='"+obj.id+"' name='"+obj.id+"' style='width:95%;height:"+(caption_h-6)+"px;'"+
        " onkeydown=\"return gallery_image_edit_keydown(this,'"+params+"','"+title_h+"','"+caption_h+"')\""+
        " onblur=\"gallery_image_edit_blur(this,'"+params+"','"+title_h+"','"+caption_h+"')\">"+
        obj.innerHTML.replace(/\n/g,'').replace(/<br>|<br \/>/g,'\n').replace(/^\s\s*/, '').replace(/\s\s*$/, '')+
        "</textarea>";
    break;
    case 't':
      obj.outerHTML=
        "<textarea id='"+obj.id+"' name='"+obj.id+"' style='width:95%;height:"+(title_h-6)+"px;'"+
        " onkeydown=\"return gallery_image_edit_keydown(this,'"+params+"')\""+
        " onblur=\"gallery_image_edit_blur(this,'"+params+"','"+title_h+"','"+caption_h+"')\">"+
        obj.innerHTML.replace(/\n/g,'').replace(/<br>|<br \/>/g,'\n').replace(/^\s\s*/, '').replace(/\s\s*$/, '')+
        "</textarea>";
    break;
  }
  $J('#'+obj.id).focus().select();
}

function gallery_image_edit_keydown(obj,params){
  var e, i, id, images, keynum, type;
  e = window.event;
  id = parseFloat(obj.id.substr(2));
  type = obj.id.substr(0,1);
  keynum = (e.keyCode ? e.keyCode : e.which);
  switch(keynum){
    case 9: // tab key
      if (e.preventDefault){
        e.preventDefault();
      }
      obj.blur();
      return false;
    case 27: // escape
      for(i=0; i<window[params].images.length; i++){
        if (window[params].images[i].ID===id){
          obj.innerHTML =
            window[params].images[i][(type==='t' ? 'title' : 'content')].replace(/\r\n/g,'');
          obj.blur();
          return false;
        }
      }
  }
  return true;
}

function gallery_image_edit_blur(obj,params,title_h,caption_h){
  window.focus();
  var type = obj.id.substr(0,1);
  var value = (obj.value!='Edit...' ? obj.value.replace(/\n/g,'<br />') : '');
  var postvars = {
    submode:        'gallery_image_set_'+(type==='t' ? 'title' : 'content'),
    source:         window[params].safeID,
    targetValue:    value,
    targetID:       obj.id.substr(2)
  }
  $J.ajax({
    data: postvars,
    type: 'POST',
    url:  window[params].url
  })
  switch(type){
    case 'c':
      obj.outerHTML=
        "<span class='"+window[params].ident+"_content' id='"+obj.id+"'"+
        " onclick=\"gallery_image_edit_click(this,'"+params+"','"+title_h+"','"+caption_h+"')\">"+
        (value ? value : 'Edit...')+
        "</span>";
    break;
    case 't':
      obj.outerHTML=
        "<span class='"+window[params].ident+"_title' id='"+obj.id+"'"+
        " onclick=\"gallery_image_edit_click(this,'"+params+"','"+title_h+"','"+caption_h+"')\">"+
        (value ? value : 'Edit...')+
        "</span>";
    break;
  }
}



// ************************************
// * Groups support                   *
// ************************************
function add_to_group(targetReportID,popup_w,popup_h) {
  geid_set('filterValue',geid_val('filterValue_'+targetReportID));
  geid_set('targetID',row_select_list(targetReportID));
  geid_set('targetReportID',targetReportID);
  popWin_post(base_url+'_admin_add_people_to_group?print=2',popup_w,popup_h);
}


function group_add_people_group_select(field_name,width) {
  var group = combo_selector_set(field_name,width);
  setDisplay('step_3',group!=='');
}

function group_selector_advanced_click(current_vals,span_arr) {
  if (
    typeof current_vals.permEMAILRECIPIENT=='undefined' ||
    current_vals.permEMAILRECIPIENT!=radio_group_get('permEMAILRECIPIENT') ||
    current_vals.permVIEWER!=radio_group_get('permVIEWER') ||
    current_vals.permEDITOR!=radio_group_get('permEDITOR')
  ){
    current_vals.permEMAILRECIPIENT=radio_group_get('permEMAILRECIPIENT');
    current_vals.permVIEWER=radio_group_get('permVIEWER');
    current_vals.permEDITOR=radio_group_get('permEDITOR');
    if (
      current_vals.permEMAILRECIPIENT=='1' &&
      current_vals.permVIEWER==='' &&
      current_vals.permEDITOR===''){
        radio_group_set('basic_perms','email');
    }
    else if (
      current_vals.permEMAILRECIPIENT==='' &&
      current_vals.permVIEWER=='1' &&
      current_vals.permEDITOR===''){
        radio_group_set('basic_perms','viewer');
    }
    else if (
      current_vals.permEMAILRECIPIENT==='1' &&
      current_vals.permVIEWER=='1' &&
      current_vals.permEDITOR===''){
        radio_group_set('basic_perms','email_viewer');
    }
    else {
      radio_group_set('basic_perms','x');
    }
  }
}

function group_selector_basic_click(current_vals,span_arr) {
  var val = radio_group_get('basic_perms');
  if (typeof current_vals.basic=='undefined' || current_vals.basic !== val) {
    current_vals.basic = val;
    switch(val) {
      case 'email':
        radio_group_set('permEDITOR','');
        radio_group_set('permEMAILRECIPIENT','1');
        radio_group_set('permVIEWER','');
      break;
      case 'viewer':
        radio_group_set('permEDITOR','');
        radio_group_set('permEMAILRECIPIENT','');
        radio_group_set('permVIEWER','1');
      break;
      case 'email_viewer':
        radio_group_set('permEDITOR','');
        radio_group_set('permEMAILRECIPIENT','1');
        radio_group_set('permVIEWER','1');
      break;
      case 'x':
        show_section(span_arr,'advanced','#f0f0ff','#c0c0f0');
        group_selector_advanced_click(current_vals,span_arr);
        return false;
    }
  }
  return true;
}

function popup_group_assign(item_type,itemID) {
  popWin(
    base_url+
    'details/group_assign/' + itemID + '/?target_value=' + item_type,
    'assign_groups','status=1, scrollbars=1,resizable=1',720,400,1);
}

function popup_page_create(layoutID,width,height){
  popWin(
    base_url+
    'details/pages/?layoutID=' + layoutID,
    '','scrollbars=1,resizable=1',width,height);
}

function cp_callback(field,value) {
  if (!window.opener) {
    return false;
  }
  if (value!==false) {
    window.opener.geid('img_swatch_'+field).src=base_url+'img/color/'+value;
    window.opener.geid(field).value=value;
    if (window.opener.geid(field).onchange){window.opener.geid(field).onchange();}
  }
  window.opener.geid(field).style.border='1px solid #c0c0c0';
  window.opener.geid(field).style.background='#ffffff';
  return true;
}

function onclick_alldayevent(checked){
  var ete, ets;
  ete = geid('effective_time_end');
  ets = geid('effective_time_start');
  if (checked){
    ete.style.background = '#e0e0e0';
    ete.disabled = true;
    ets.style.background = '#e0e0e0';
    ets.disabled = true;
  }
  else{
    ete.style.background = '#ffffff';
    ete.disabled = false;
    ets.style.background = '#ffffff';
    ets.disabled = false;
  }
}

function merge_profiles(targetID){
  var h = "<div id='popup_form' style='padding:4px;'>Loading...</div>";
  popup_dialog('Merge Profiles',h,1000,400,'','');
  var post_vars = "command=merge_profiles&ajax=1&targetID="+targetID;
  popup_layer_submit(base_url,post_vars);
  return false;
}

function merge_profiles_process(targetID){
  var h = "<div id='popup_form' style='padding:4px;'>Loading...</div>";
  popup_dialog('Merge Profiles',h,1000,400,'','');
  var post_vars = "command=merge_profiles&submode=merge&ajax=1&targetID="+targetID;
  popup_layer_submit(base_url,post_vars);
  return false;
}

function merge_profiles_select_destination(ID){
  $('#targetValue').val(ID);
  $('#merge_profiles_row_'+ID).siblings().prop({title:'This profile will be merged into the selected one'});
  $('#merge_profiles_row_'+ID).siblings().css({color:'#080'});
  $('#merge_profiles_row_'+ID).prop({title:'Other profiles will be merged into this one'});
  $('#merge_profiles_row_'+ID).css({color:'#f00'});
  $('#merge_profiles_submit').prop({disabled:false});
}

function repeat_settings_dialog(targetID){
  var h = "<div id='popup_form' style='padding:4px;'>Loading...</div>";
  popup_dialog('Repeated Event Settings',h,650,400,'','');
  var post_vars = "ajax=1&targetID="+targetID;
  popup_layer_submit(base_url+'?command=recurrence_settings',post_vars);
  return false;
}

function repeat_settings_mode(date){
  var div, i, old_mode, new_mode, settings, show;
  settings = ['','daily','weekly','monthly','yearly'];
  new_mode =  geid_val('recur_mode');
  old_mode =    "";
  for (i=0; i<settings.length; i++){
    div = geid('repeat_settings_'+settings[i]);
    if (div && typeof div.style.display!=='undefined' && div.style.display===''){
      old_mode = settings[i];
    };
  }
  show = (new_mode!=='' ? true : false);
  div = geid('repeat_settings_range');
  setDisplay(div,show);
  if (new_mode==old_mode){
    return;
  }
  // reset all values to neutral
  radio_group_set('recur_daily_mode','day');
  radio_group_set('recur_monthly_mode','day');
  radio_group_set('recur_yearly_mode','on');
  radio_group_set('recur_range_mode','endafter');
  geid_set('recur_daily_interval','1');
  geid_set('recur_weekly_interval','1');
  field_csv_set('recur_weekly_days_csv','','Sun,Mon,Tue,Wed,Thu,Fri,Sat');
  geid_set('recur_monthly_interval','1');
  geid_set('recur_monthly_interval2','1');
  geid_set('recur_yearly_mm','1');
  geid_set('recur_yearly_mm2','1');
  geid_set('recur_yearly_interval','1');
  geid_set('recur_yearly_interval2','1');
  if(date){
    var date_arr =  date.split('-');
    var d =         new Date(date_arr[0],date_arr[1]-1,date_arr[2]);
    var days =      'Sun,Mon,Tue,Wed,Thu,Fri,Sat';
    var days_arr =  days.split(',');
    var dd =        days_arr[d.getDay()];
    switch(new_mode){
      case '':
      break;
      case 'daily':
      break;
      case 'weekly':
        field_csv_set('recur_weekly_days_csv',dd,days);
      break;
      case 'monthly':
        geid_set('recur_monthly_day',dd);
        geid_set('recur_monthly_nth',''+Math.ceil(parseFloat(date_arr[2])/7));
        geid_set('recur_monthly_dd',''+parseFloat(date_arr[2]));
      break;
      case 'yearly':
        geid_set('recur_yearly_mm',''+parseFloat(date_arr[1]));
        geid_set('recur_yearly_dd',''+parseFloat(date_arr[2]));
        geid_set('recur_yearly_nth',''+Math.ceil(parseFloat(date_arr[2])/7));
        geid_set('recur_yearly_day',dd);
        geid_set('recur_yearly_mm2',''+parseFloat(date_arr[1]));
      break;
    }
  }
  for (i=0; i<settings.length; i++){
    div = 'repeat_settings_'+settings[i];
    show = settings[i]==new_mode;
    setDisplay(div,show);
  }
  status_message_hide('form_status_recurrence_settings_msg');
  return false;
}

function repeat_settings_onsubmit(ID){
  geid('recurrence_form_cancel').disabled=true;
  geid('recurrence_form_submit').disabled=true;
  var post_vars =
    "ajax=1"+
    "&submode=repeat_settings_submit&targetID="+ID;
  popup_layer_submit(base_url+'?command=recurrence_settings',post_vars);
}

// ************************************
// * Order Item Refunds               *
// ************************************
function order_issue_credit_memo(orderID,ref_items_arr){
  var h, j, dialogTitle, id;
  var val_arr = [];
  for (var i=0; i<ref_items_arr.length; i++) {
    id = ref_items_arr[i];
    val_arr[val_arr.length] = id + "=" + geid_val('ref_'+id+'_nra');
  }
  var suggestedTotal = geid('ref_suggested_total').innerHTML;
  var actualTotal = geid('ref_actual_total').value;
  if (suggestedTotal != actualTotal) {
    dialogTitle = "WARNING";
    h = "<div style='padding:4px;'>Suggested Refund: " + suggestedTotal + "<br />differs from<br />Actual Refund: " + actualTotal + "<br /><br />Press OK to issue this Credit Memo anyway</div>";
  }
  else {
    dialogTitle = "Credit Memo";
    h = "<div style='padding:4px;'>Issue Credit Memo for this order?</div>";
  }
  j =
    "geid('command').value='order_issue_credit_memo';"+
    "geid('targetValue').value='"+
    orderID+"|"+
    val_arr.join(',')+"|"+
    geid_val('ref_actual_total')+"|"+
    geid_val('ref_notes_customer').replace(/\'/g,'\\\'').replace(/\"/g,'&quot;')+
    "';"+
    "geid('form').submit();";
  popup_dialog(dialogTitle,h,260,260,'OK','Cancel',j);
}

function order_item_refund_flag_set(page,orderID,orderItemID,available){
  var h, j;
  h =
    "<div style='padding:4px;'>Please confirm refund request:"+
    (available > 1 ?
      "<div style='padding-top:10px;'>"+
      "<table class='minimal' summary='Grid layout for popup form'>\n"+
      "  <tr>\n"+
      "    <td style='width:40px;'><input tabindex='1' type='text' id='qty' class='formField txt_r' style='width:30px;' value='"+available+"' /></td>\n"+
      "    <td>Quantity to refund</td>\n"+
      "  </tr>\n"+
      "</table>\n"+
      "</div>" : "<input type='hidden' id='qty' value='"+available+"' />")+
    "</div></div>";

  j =
    "if (parseInt(geid_val('qty'))<1 || parseInt(geid_val('qty'))>"+available+"){"+
    "geid('qty').value="+available+";return false;}"+
    "window.location='"+base_url+"?page="+page+
    "&ID="+orderID+
    "&print="+geid_val('print')+
    "&command=order_item_refund_flag_set"+
    "&targetID="+orderItemID+
    "&targetValue='+geid_val('qty')+'"+
    "#row_"+orderItemID+"'";
  popup_dialog("Refund Item",h,260,260,'OK','Cancel',j,(available>1 ? 'qty' : ''));
  if (available>1) {
    attach_field_behaviour('qty','int_s');
  }
}

function order_item_refund_calculate(orderItemID,qty,price,taxes,line) {
  var nra =   parseFloat(geid_val('ref_'+orderItemID+'_nra'));
  var diff =  (price-nra);
  var ratio = (diff/price);
  var sub =   price*ratio*qty;
  var div;
  geid('ref_'+orderItemID+'_diff').innerHTML= two_dp(diff);
  geid('ref_'+orderItemID+'_sub').innerHTML = two_dp(sub);
  line = sub;
  for (var i=0; i<taxes.length; i++){
    div = 'ref_'+orderItemID+'_tax_'+taxes[i].idx;
    geid(div).innerHTML = two_dp(ratio*taxes[i].amount);
    line += ratio*taxes[i].amount;
  }
  geid('ref_'+orderItemID+'_line').innerHTML = two_dp(line);
}
function order_items_refund_total(line_arr) {
  if (!line_arr) {
    return;
  }
  var total = 0;
  for (var i=0 ; i<line_arr.length; i++){
    total += parseFloat(geid('ref_'+line_arr[i]+'_line').innerHTML);
  }
  geid('ref_suggested_total').innerHTML = two_dp(total);
}

function order_item_refund_nra_reset(orderItemID,nra){
  geid('ref_'+orderItemID+'_nra').value=nra;
  geid('ref_'+orderItemID+'_nra').onchange();
}
    
function order_item_refund_flag_clear(orderItemID){
  var h, j;
  h = "<div style='padding:4px;'>Cancel pending refund for item?</div>";
  j =
    "geid('command').value='order_item_refund_flag_clear';"+
    "geid('targetID').value='"+orderItemID+"';"+
    "geid('form').submit();";
  popup_dialog("Cancel Refund",h,260,260,'Yes','No',j);
}


function popup_password_change(targetFieldID,targetReportID,targetID){
  var h, j;
  h =
    "<div style='padding:4px;'>Please enter new password"+
    "<div style='padding-top:10px;'>\n"+
    "<table class='minimal' summary='Grid layout for popup form'>\n"+
    "  <tr>\n"+
    "    <td style='width:70px;'>Password</td>\n"+
    "    <td><input type='password' id='pwd1' onkeyup='popup_password_test()' class='formField' style='width:100px;' value='' /></td>\n"+
    "  </tr>\n"+
    "  <tr>\n"+
    "    <td>Confirm</td>\n"+
    "    <td><input type='password' id='pwd2' disabled='disabled' onkeyup='popup_password_test()' class='formField fl' style='width:100px;background-color:#e8e8e8;' value='' /></td>\n"+
    "  </tr>\n"+
    "</table>"+
    "</div></div>\n";
  j = "popup_password_change_set('"+targetFieldID+"','"+targetReportID+"','"+targetID+"')";
  popup_dialog("Password",h,200,260,'OK','Cancel',j, 'pwd1');
  geid('btn_ok').disabled=true;
}

function popup_password_test() {
  var pwd1 = geid_val('pwd1');
  var pwd2 = geid_val('pwd2');
  geid('pwd2').disabled = (pwd1.length<pwd_len_min);
  geid('pwd2').style.backgroundColor = (pwd1.length>=pwd_len_min ? '#ffffff' : '#e8e8e8');
  geid('btn_ok').disabled = !(pwd1.length>=pwd_len_min && pwd1==pwd2);
}

function popup_password_change_set(targetFieldID,targetReportID,targetID) {
  geid('submode').value =        'set_password';
  geid('targetFieldID').value =  targetFieldID;
  geid('targetReportID').value = targetReportID;
  geid('targetID').value =       targetID;
  geid('targetValue').value =    geid_val('pwd2');
  geid('form').submit();
}

function popup_fileviewer() {
  popWin(base_url+'js/ckfinder/ckfinder.html','popup_fileviewer','scrollbars=0,resizable=1,status=0',750,450,'centre');
}

function details(report_name,ID,height,width,reportID,selectID,bulk_update,preset_values){
  var args = [], url, window_name;
  if (typeof reportID=="number") { args.push('reportID='+reportID); }
  if ((typeof selectID=="number" || typeof selectID=="string") && selectID!=='' ) { args.push('selectID='+selectID); }
  if (typeof bulk_update=="number" && bulk_update===1) { args.push('bulk_update='+bulk_update); }
  if (typeof preset_values=="string" && preset_values!=='') { args.push(preset_values); }
  args.push('rnd='+Math.random());
  if (typeof bulk_update=="number" && bulk_update==1){
    geid_set('targetID',ID);
    url = base_url+ 'details/' + report_name + (args.length ? "?" + args.join("&") : "");
    popWin_post(url,width,height);
  }
  else {
    window_name = ('pop_form_'+report_name+'_'+ID).replace(/[ :,\/\-\.]/ig,'');
    url = base_url+ 'details/' + report_name + '/' + ID + (args.length ? "?" + args.join("&") : "");
    popWin(url,window_name,'scrollbars=1,resizable=1',width,height,'centre');
  }
  return;
}

// ************************************
// * 'With Selected' functions        *
// ************************************
function export_sql(report_name,targetID) {
  var url = base_url + 'export/sql/' + report_name + '/?show_fields=1';
  geid_set('targetID',targetID);
  popWin_post(url,800,640);
}

function selected_send_email(targetReportID,popup_w,popup_h) {
  var opts = row_select_list(targetReportID);
  popWin(
    base_url+'_admin_send_email' +
    '?targetReportID='+targetReportID +
    (opts!=="" ? "&targetID="+opts : ""),
    "send_email",'scrollbars=0,resizable=0',popup_w,popup_h,'centre');
}

function selected_operation(form,report_name,reportID,args) {
  var control = 'selected_op_'+reportID;
  var mode =    geid_val(control);
  if (typeof args[mode]!=='number') {
    return;
  }
  var num =      row_select_count(reportID);
  var targetID = row_select_list(reportID);
  var targetFieldID = args[mode];
  switch(mode) {
    case 'selected_add_to_group':
      add_to_group(reportID,580,460);
      geid_set('submode','');
    break;
    case 'selected_delete':
      if (num>0) {
        if (confirm('Delete '+num+' selected record'+(num==1 ? '': 's')+' - are you sure?')) {
          geid_set('submode','delete');
        }
        else{alert('Deletion cancelled');}
      }
      else{alert('No records selected to delete');}
    break;
    case 'selected_empty':
      if (num>0) {
        if (confirm('Empty '+num+' selected group'+(num==1 ? '': 's')+' - are you sure?')) {
          geid_set('submode','empty');
        }
        else{alert('Group Empty cancelled');}
      }
      else {alert('No groups selected to empty');}
    break;
    case 'selected_export_excel':
      if (num>0) {
        export_excel(reportID);
      }
      else {alert('No records to export');}
      geid_set('submode','');
    break;
    case 'selected_export_sql':
      if (num>0) {
        export_sql(report_name,targetID);
      }
      else {alert('No records to export');}
      geid_set('submode','');
    break;
    case 'selected_merge_profiles':
      if (num>1) {
        merge_profiles(targetID);
      }
      else {alert('Select two or more profiles to merge - the first one chosen will be the one any others are merged to.');}
      geid_set('submode','');
    break;
    case 'selected_process_maps':
      if (num>0) {
        if (confirm('Process map lookups for '+num+' entr'+(num==1 ? 'y': 'ies')+' - are you sure?')) {
          geid_set('submode','set_process_maps');
        }
        else{alert('Map processing cancelled');}
      }
      else {alert('No items selected to reprocess');}
    break;
    case 'selected_process_order':
      if (num>0) {
        if (confirm('Process '+num+' selected order'+(num==1 ? '': 's')+' - are you sure?')) {
          geid_set('submode','process_order');
        }
        else{alert('Order processing cancelled');}
      }
      else {alert('No orders selected to process');}
    break;
    case 'selected_send_email':
      if (num>0) {
        selected_send_email(args.selected_send_email,760,500);
      }
      else {alert('No persons to send email to');}
      geid_set(control,'');
      return;
    case 'selected_set_as_approved':
      if (num>0) {
        if (confirm('Set '+num+' comment'+(num==1 ? '': 's')+' as being approved - are you sure?')) {
          geid_set('submode','set_as_approved');
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_as_attended':
      if (num>0) {
        if (confirm('Set '+num+' registrant'+(num==1 ? '': 's')+' as having attended - are you sure?')) {
          geid_set('submode','set_as_attended');
        }
        else{alert('Attendance updates cancelled');}
      }
      else {alert('No registrants selected to mark');}
    break;
    case 'selected_set_as_hidden':
      if (num>0) {
        if (confirm('Set '+num+' comment'+(num==1 ? '': 's')+' as hidden - are you sure?')) {
          geid_set('submode','set_as_hidden');
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_as_member':
      if (num>0) {
        if (confirm('Set '+num+' person'+(num==1 ? '': 's')+' to have Member permissions - are you sure?')) {
          geid_set('submode','set_as_member');
        }
        else{alert('Membership updates cancelled');}
      }
      else {alert('No persons selected to promote');}
    break;
    case 'selected_set_as_spam':
      if (num>0) {
        if (confirm('Set and report '+num+' comment'+(num==1 ? '': 's')+' as spam - are you sure?')) {
          geid_set('submode','set_as_spam');
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_as_unapproved':
      if (num>0) {
        if (confirm('Set '+num+' comment'+(num==1 ? '': 's')+' as being unapproved - are you sure?')) {
          geid_set('submode','set_as_unapproved');
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_email_opt_in':
      if (num>0) {
        if (confirm('Set '+num+' record'+(num==1 ? '': 's')+' as having opted in to receiving emails sent to this group - are you sure?')) {
          var reason = prompt('Please give a reason for this Email opt-in assignment','');
          if (reason!==null && reason!==''){
            geid_set('submode','set_email_opt_in');
            geid_set('targetValue',reason);
          }
          else {
            alert('You must give a valid reason for this change');
          }
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_email_opt_out':
      if (num>0) {
        if (confirm('Set '+num+' record'+(num==1 ? '': 's')+' as having opted out to receiving emails sent to this group - are you sure?')) {
          var reason = prompt('Please give a reason for this Email opt-out assignment','');
          if (reason!==null){
            geid_set('submode','set_email_opt_out');
            geid_set('targetValue',reason);
          }
          else {
            alert('You must give a valid reason for this change');
          }
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_important_on':
      if (num>0) {
        if (confirm('Set '+num+' record'+(num==1 ? '': 's')+' as having high importance - are you sure?')) {
          geid_set('submode','set_important_on');
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_set_important_off':
      if (num>0) {
        if (confirm('Set '+num+' record'+(num==1 ? '': 's')+' as having normal importance - are you sure?')) {
          geid_set('submode','set_important_off');
        }
        else{alert('Updates cancelled');}
      }
      else {alert('No items selected to update');}
    break;
    case 'selected_update':
      if (num>0) {
        details(report_name,targetID,args.popup_size.h,args.popup_size.w,'','',1);
      }
      else {alert('No records selected to update');}
      geid_set('submode','');
    break;
    case 'selected_show_on_map':
      if (num>0) {
        selected_show_on_map(reportID, report_name, args.toolbar, '')
      }
      else {alert('No persons to view email addresses for ');}
      geid_set(control,'');
      return;
    break;
    case 'selected_view_email_addresses':
      if (num>0) {
        selected_view_email_addresses(1, reportID, report_name, args.toolbar, '')
      }
      else {alert('No persons to view email addresses for ');}
      geid_set(control,'');
      return;
    break;
    default:
      alert('Operation '+mode+' is not valid here');
      geid_set('submode','');
    break;
  }
  if (geid_val('submode')!=''){
    geid_set('targetReportID',reportID);
    geid_set('targetFieldID',targetFieldID);
    geid_set('targetID',targetID);
    args.submit_action();
  }
  geid_set(control,'');
}

function selected_show_on_map(reportID, report_name, toolbar, ajax_mode){
  var url = base_url+
    '_map?'+
    'reportID='+reportID+
    '&width=900&height=600'+
    '&ID='+row_select_list(reportID);
  popWin_post(url,900,600);
}


function selected_view_email_addresses(show, reportID, report_name, toolbar, ajax_mode){
  geid_set('targetID',row_select_list(reportID));
  geid_set('targetReportID',reportID);
  geid_set('submode',(show ? 'show_addresses' : ''));
  ajax_report(reportID, report_name, toolbar, ajax_mode);
}

function selected_operation_enable(reportID){
  if (eval("typeof " + "selected_operation_enable_"+reportID + " == 'function'")){
    return eval("selected_operation_enable_"+reportID+"(geid('form'),reportID)");
  }
  alert("No 'with selected' operations available");
  return false;    
}

// ************************************
// * Other functions                  *
// ************************************
function add_note(targetField,targetValue) {
  geid('submode').value='add_note';
  geid('targetField').value=targetField;
  geid('targetValue').value=targetValue;
  geid('form').submit();
}  

function field_csv_set(id,value,options) {
  var i, j, inthere, value_arr;
  geid_set(id,value);
  value_arr =   value.replace(' ','').split(',');
  options_arr = options.replace(' ','').split(',');
  inthere = false;
  for (i=0; i<options_arr.length; i++){
    geid(id+'_'+options_arr[i]).checked = false;
    for (j=0; j<value_arr.length; j++){
      if (options_arr[i]===value_arr[j]){
        geid(id+'_'+options_arr[i]).checked = true;
      }
    }
  }
}

function field_csv_toggle(id,item,state) {
  var value_arr = geid(id).value.split(", ");
  var inthere = false;
  for (var i=0; i<value_arr.length; i++){
    if (!value_arr[i]) {
      value_arr.splice(i,1);
    }
    if (value_arr[i] == item) {
      if (!state){
        value_arr.splice(i,1);
      }
      else {
        inthere = true;
      }
    }
  }
  if (state==1 && !inthere) {
    value_arr[value_arr.length]=item;
  }
  geid(id).value = value_arr.join(', ');
}

function download_order_pdf(orderID,columnID,width,height) {
  var uri =
    window.location.protocol+"//"+window.location.host+
    "/?command=download_order_pdf&orderID="+orderID+"&columnID="+columnID;
  window.location=uri;
}

function download_record_pdf(targetID,columnID,width,height) {
  var uri =
    window.location.protocol+"//"+window.location.host+
    "/?command=download_record_pdf&targetID="+targetID+"&columnID="+columnID;
  window.location=uri;
}

function report(report_name,selectID,height,width,print,preset_values){
  var window_name, args=[];
  window_name = ('pop_report_'+report_name+"_"+selectID).replace(/[ :,\/\-\.]/ig,'');
  if (typeof selectID=="number" || typeof selectID=="string"){ args.push('selectID='+selectID);}
  if (typeof preset_values=="string" && preset_values!=='') { args.push(preset_values); }
  if (typeof print=="number"){ args.push('print='+print);}
  popWin(
    base_url+'report/' + report_name + '/?'+args.join('&'),
    window_name,'scrollbars=1,resizable=1',width,height,'centre');
}

function export_excel(targetReportID) {
  var filterExact, filterField, filterValue, hd, selectID, sortBy, targetID;
  geid_set('filterValue',geid_val('filterValue_'+targetReportID));
  filterValue =	(geid_val('filterValue')!='(Search for ...)' ? geid_val('filterValue') : '');
  filterExact =	(filterValue!=='' ? geid_val('filterExact') : '')
  filterField =	(filterValue!=='' ? geid_val('filterField') : '');
  selectID =	geid_val('selectID');
  sortBy =	    geid_val('sortBy');
  targetID =    row_select_list(targetReportID);
  hd = popWin(
    base_url+'export/excel' +
    '?targetReportID='+targetReportID +
    (targetID ?         '&targetID='+targetID : '') +
    (filterExact!=='' ? '&filterExact='+filterExact : '') +
    (filterField!=='' ? '&filterField='+filterField : '') +
    (filterValue!=='' ? '&filterValue='+filterValue : '') +
    (selectID!=='' ?    '&selectID='+selectID : '') +
    (sortBy!=='' ?      '&sortBy='+sortBy : ''),
    "export_excel",
    'resizable=yes,location=no,menubar=yes,scrollbars=yes,status=yes,toolbar=yes,fullscreen=no,dependent=no',
    760,
    570,
    'centre'
  );
  if (hd!==false && isIE8){
    var regex = new RegExp("ie8_excel_info_shown=1");
    if (!regex.test(document.cookie)) {
      document.cookie = 'ie8_excel_info_shown=1';
      alert("IE8 Users:\n\nIf this operation failed, try the following:\n\nGo to tools/internet options/security/custom level and\ncheck if 'Automatic prompting for file downloads' is disabled.\nEnabling this should fix any problems you may have.");
    }
  }
}

function open_textdoc(theURL) {
  theURL.replace('&amp;','&');
  var list_h = window.open(theURL,'textOutput', 'width=800,height=600,status=1,resizable=1,menubar=1,location=0,toolbar=1,scrollbars=1');
  list_h.focus();
}


function open_excel(theURL) {
  var list_h = window.open(theURL,'excelOutput', 'width=800,height=600,status=1,resizable=1,menubar=1,location=0,toolbar=1,scrollbars=1');
  list_h.focus();
}

function decToHex(dec){
  var hexStr = "0123456789ABCDEF";
  var low = dec % 16;
  var high = (dec - low)/16;
  var hex = "" + hexStr.charAt(high) + hexStr.charAt(low);
  return hex;
}

function popup_table_structure(url,table) {
  popWin(url+'/?command=get_table_structure&targetValue='+table,'','status=1, scrollbars=1,resizable=1',730,800,1);
}

function set_action_operation_options(){
  var child_field, child_value, replaced;
  child_field =  geid('destinationID');
  child_value =  geid_val('destinationID');
  show_popup_please_wait();
  replaced = document.createElement('select');
  replaced.setAttribute('id','destinationID');
  replaced.setAttribute('name','destinationID');
  replaced.setAttribute('class',child_field.getAttribute('class'));
  replaced.setAttribute('style',child_field.getAttribute('style'));
  replaced_option = new Option('Loading available options...',child_value);
  replaced.options.add(replaced_option);
  child_field.parentNode.replaceChild(replaced,child_field);
  child_field =  geid('destinationID');
  window.setTimeout(function(){set_action_operation_options_populate_options()},200);
}

function set_action_operation_options_populate_options(){
  var child_field, child_value, destinationOperation, i, item, obj_option,
      options, options_count, parent_field, parent_value, trigger_field, selected;
  parent_field = geid('destinationOperation');
  child_field =  geid('destinationID');
  parent_value = geid_val('destinationOperation') || 'none';
  child_value =  geid_val('destinationID');
  if (child_value) {
    geid_set('temp_id',child_value);
  }
  options_count = destID[parent_value].length;
  trigger_field = geid('sourceTrigger');
  trigger_value = geid_val('sourceTrigger');
  destinationOperation = geid_val('destinationOperation');
  switch(destinationOperation){
    case 'event_register':
      for(i=0; i<trigger_field.options.length; i++){
        switch(trigger_field.options[i].value){
          case 'product_pay':
          break;
          default:
            trigger_field.options[i].text = '--- '+trigger_field.options[i].text+' ---';
            trigger_field.options[i].style.color = '#808080';
            trigger_field.options[i].style.fontStyle = 'italic';
            trigger_field.options[i].disabled = true;
          break;
        }
      }
      if (trigger_value!='product_pay'){
        geid_set('sourceTrigger','product_pay');
      }
    break;
    default:
      for(i=0; i<trigger_field.options.length; i++){
        if (trigger_field.options[i].text.substr(0,4)=='--- '){
          trigger_field.options[i].text = trigger_field.options[i].text.substr(4,trigger_field.options[i].text.length-8);
          trigger_field.options[i].style.color = '';
          trigger_field.options[i].style.fontStyle = '';
          trigger_field.options[i].disabled = false;
        }
      }
    break;
  }
  var max = 95;
  if (isIE){
    options = [];
    for (i=0; i<options_count; i++) {
      item = destID[parent_value][i];
      selected = item[1]==child_value;
      label = (item[0].length>max ? item[0].substr(0,max)+' ...' : item[0]);
      options[i] =
        "{{option value=\""+item[1]+"\""+
        (selected ? " selected='selected'" : "")+
        " style=\"background-color:"+item[2]+"\""+
        " title=\""+item[0]+"\""+
        "}}"+label+"{{/option}}";
    }
    child_field.innerHTML=options.join('');
    child_field.outerHTML = child_field.outerHTML.replace(/\{\{/g,'<').replace(/\}\}/g,'>');
  }
  else {
    for (i=0; i<options_count; i++) {
      item = destID[parent_value][i];
      selected = item[1]==child_value;
      label = (item[0].length>max ? item[0].substr(0,max)+' ...' : item[0]);
      obj_option = new Option(label,item[1],false,selected);
      obj_option.style.backgroundColor=item[2];
      obj_option.title = item[0];
      child_field.options[i] = obj_option;
    }
  }
  hidePopWin();
}

function set_product_relationship_options(){
  var child_field, child_value, replaced;
  child_field =  geid('related_objectID');
  child_value =  geid_val('related_objectID');
  show_popup_please_wait();
  replaced = document.createElement('select');
  replaced.setAttribute('id','related_objectID');
  replaced.setAttribute('name','related_objectID');
  replaced.setAttribute('class',child_field.getAttribute('class'));
  replaced.setAttribute('style',child_field.getAttribute('style'));
  replaced_option = new Option('Loading available options...',child_value);
  replaced.options.add(replaced_option);
  child_field.parentNode.replaceChild(replaced,child_field);
  child_field =  geid('related_objectID');
  window.setTimeout(function(){set_product_relationship_options_populate_options()},200);
}

function set_product_relationship_options_populate_options(){
  var child_field, child_value, related_object, i, item, obj_option,
      options, options_count, parent_field, parent_value, selected;
  parent_field = geid('related_object');
  child_field =  geid('related_objectID');
  parent_value = (geid_val('related_object') || 'none').replace('-','_');
  child_value =  geid_val('related_objectID');
  if (child_value) {
    geid_set('temp_id',child_value);
  }
  options_count = pr_destID[parent_value].length;
  var max = 95;
  if (isIE){
    options = [];
    for (i=0; i<options_count; i++) {
      item = pr_destID[parent_value][i];
      selected = item[1]==child_value;
      label = (item[0].length>max ? item[0].substr(0,max)+' ...' : item[0]);
      options[i] =
        "{{option value=\""+item[1]+"\""+
        (selected ? " selected='selected'" : "")+
        " style=\"background-color:"+item[2]+"\""+
        " title=\""+item[0]+"\""+
        "}}"+label+"{{/option}}";
    }
    child_field.innerHTML=options.join('');
    child_field.outerHTML = child_field.outerHTML.replace(/\{\{/g,'<').replace(/\}\}/g,'>');
  }
  else {
    for (i=0; i<options_count; i++) {
      item = pr_destID[parent_value][i];
      selected = item[1]==child_value;
      label = (item[0].length>max ? item[0].substr(0,max)+' ...' : item[0]);
      obj_option = new Option(label,item[1],false,selected);
      obj_option.style.backgroundColor=item[2];
      obj_option.title = item[0];
      child_field.options[i] = obj_option;
    }
  }
  hidePopWin();
}


function validate_at_w3c(targetID,width,height) {
  var uri =
    'http://validator.w3.org/check?uri='+
     window.location.protocol+"%2F%2F"+window.location.host+
     '%2F%3Fcommand%3Ddownload_custom_form_xml%26targetID%3D'+targetID;
  popWin(uri,'validate','status=1, scrollbars=1,resizable=1',width,height,1);
}



// ************************************
// * Option Transfer functions        *
// ************************************
// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// NOTICE: You may use this code for any purpose, commercial or
// private, without any further permission from the author. You may
// remove this notice from your final code if you wish, however it is
// appreciated by the author if at least my web site address is kept.
//
// You may *NOT* re-distribute this code in any way except through its
// use. That means, you can include it in your product, or your web
// site, or any other form where the code is actually being used. You
// may not put the plain javascript up on your site for download or
// include it in your javascript libraries for download.
// If you wish to share this code with others, please just point them
// to the URL instead.
// Please DO NOT link directly to my .js files from your site. Copy
// the files to your server and use them there. Thank you.
// ===================================================================

/* SOURCE FILE: selectbox.js */
function hasOptions(obj){if(obj!==null && obj.options!==null){return true;}return false;}
function selectUnselectMatchingOptions(obj,regex,which,only){if(window.RegExp){var selected1, selected2;if(which==="select"){selected1=true;selected2=false;}else if(which==="unselect"){selected1=false;selected2=true;}else{return;}var re = new RegExp(regex);if(!hasOptions(obj)){return;}for(var i=0;i<obj.options.length;i++){if(re.test(obj.options[i].text)){obj.options[i].selected = selected1;}else{if(only===true){obj.options[i].selected = selected2;}}}}}
function selectMatchingOptions(obj,regex){selectUnselectMatchingOptions(obj,regex,"select",false);}
function selectOnlyMatchingOptions(obj,regex){selectUnselectMatchingOptions(obj,regex,"select",true);}
function unSelectMatchingOptions(obj,regex){selectUnselectMatchingOptions(obj,regex,"unselect",false);}
function sortSelect(obj){var o = [],i;if(!hasOptions(obj)){return;}for(i=0;i<obj.options.length;i++){o[o.length] = new Option(obj.options[i].text,obj.options[i].value,obj.options[i].defaultSelected,obj.options[i].selected);if(typeof obj.options[i].className!='undefined'){o[o.length-1].className = obj.options[i].className;}}if(o.length===0){return;}o=o.sort(function(a,b){if((a.text.toLowerCase()+"") <(b.text.toLowerCase()+"")){return -1;}if((a.text.toLowerCase()+"") >(b.text.toLowerCase()+"")){return 1;}return 0;});for(i=0;i<o.length;i++){obj.options[i] = new Option(o[i].text,o[i].value,o[i].defaultSelected,o[i].selected);if(typeof o[i].className !='undefined'){obj.options[i].className = o[i].className;}}}
function selectAllOptions(obj){if(!hasOptions(obj)){return;}for(var i=0;i<obj.options.length;i++){obj.options[i].selected = true;}}
function moveSelectedOptions(from,to){var index,i,o,regex;if(arguments.length>3){regex=arguments[3];if(regex != ""){unSelectMatchingOptions(from,regex);}}if(!hasOptions(from)){return;}for(i=0;i<from.options.length;i++){o=from.options[i];if(o.selected){if(!hasOptions(to)){index = 0;}else{index=to.options.length;}to.options[index] = new Option( o.text, o.value, false, false);if(typeof o.className!='undefined'){to.options[index].className=o.className;}}}for(i=(from.options.length-1);i>=0;i--){o=from.options[i];if(o.selected){from.options[i]=null;}}if((arguments.length<3)||(arguments[2]===true)){sortSelect(from);sortSelect(to);}from.selectedIndex = -1;to.selectedIndex = -1;}
function copySelectedOptions(from,to){var index,i,o,options={};if(hasOptions(to)){for(i=0;i<to.options.length;i++){options[to.options[i].value] = to.options[i].text;}}if(!hasOptions(from)){return;}for(i=0;i<from.options.length;i++){o=from.options[i];if(o.selected){if(options[o.value]===null||options[o.value]==="undefined"||options[o.value]!==o.text){if(!hasOptions(to)){index = 0;}else{index=to.options.length;}to.options[index] = new Option( o.text, o.value, false, false);if(typeof o.className !='undefined'){to.options[index].className=o.className;}}}}if((arguments.length<3)||(arguments[2]===true)){sortSelect(to);}from.selectedIndex = -1;to.selectedIndex = -1;}
function moveAllOptions(from,to){selectAllOptions(from);if(arguments.length===2){moveSelectedOptions(from,to);}else if(arguments.length===3){moveSelectedOptions(from,to,arguments[2]);}else if(arguments.length===4){moveSelectedOptions(from,to,arguments[2],arguments[3]);}}
function copyAllOptions(from,to){selectAllOptions(from);if(arguments.length===2){copySelectedOptions(from,to);}else if(arguments.length===3){copySelectedOptions(from,to,arguments[2]);}}
function swapOptions(obj,i,j){var o = obj.options;var i_selected = o[i].selected;var j_selected = o[j].selected;var temp = new Option(o[i].text,o[i].value,o[i].defaultSelected,o[i].selected);if(typeof o[i].className!='undefined'){temp.className=o[i].className;}var temp2=new Option(o[j].text,o[j].value,o[j].defaultSelected,o[j].selected);if(typeof o[j].className!='undefined'){temp2.className=o[j].className;}o[i]=temp2;o[j]=temp;o[i].selected=j_selected;o[j].selected=i_selected;}
function moveOptionUp(obj){if(!hasOptions(obj)){return;}for(var i=0;i<obj.options.length;i++){if(obj.options[i].selected){if(i!==0&&!obj.options[i-1].selected){swapOptions(obj,i,i-1);obj.options[i-1].selected = true;}}}}
function moveOptionDown(obj){if(!hasOptions(obj)){return;}for(var i=obj.options.length-1;i>=0;i--){if(obj.options[i].selected){if(i !=(obj.options.length-1) && ! obj.options[i+1].selected){swapOptions(obj,i,i+1);obj.options[i+1].selected = true;}}}}
function removeSelectedOptions(from){if(!hasOptions(from)){return;}for(var i=(from.options.length-1);i>=0;i--){var o=from.options[i];if(o.selected){from.options[i] = null;}}from.selectedIndex = -1;}
function removeAllOptions(from){if(!hasOptions(from)){return;}for(var i=(from.options.length-1);i>=0;i--){from.options[i] = null;}from.selectedIndex = -1;}
function addOption(obj,text,value,selected){if(obj!==null&&obj.options!==null){obj.options[obj.options.length]=new Option(text,value,false,selected);}}

/* SOURCE FILE: OptionTransfer.js */
function OT_transferLeft(){moveSelectedOptions(this.right,this.left,this.autoSort,this.staticOptionRegex);this.update();}
function OT_transferRight(){moveSelectedOptions(this.left,this.right,this.autoSort,this.staticOptionRegex);this.update();}
function OT_transferAllLeft(){moveAllOptions(this.right,this.left,this.autoSort,this.staticOptionRegex);this.update();}
function OT_transferAllRight(){moveAllOptions(this.left,this.right,this.autoSort,this.staticOptionRegex);this.update();}
function OT_saveRemovedLeftOptions(f){this.removedLeftField = f;}
function OT_saveRemovedRightOptions(f){this.removedRightField = f;}
function OT_saveAddedLeftOptions(f){this.addedLeftField = f;}
function OT_saveAddedRightOptions(f){this.addedRightField = f;}
function OT_saveNewLeftOptions(f){this.newLeftField = f;}
function OT_saveNewRightOptions(f){this.newRightField = f;}
function OT_join(o,delimiter){var val;var str="";for(val in o){if(o.hasOwnProperty(val)){if(str.length>0){str=str+delimiter;}str=str+val;}}return str;}
function OT_update(){var removedLeft={},removedRight={},addedLeft={},addedRight={},newLeft={},newRight={},i,o;for(i=0;i<this.left.options.length;i++){o=this.left.options[i];newLeft[o.value]=1;if(typeof(this.originalLeftValues[o.value])==="undefined"){addedLeft[o.value]=1;removedRight[o.value]=1;}}for(i=0;i<this.right.options.length;i++){o=this.right.options[i];newRight[o.value]=1;if(typeof(this.originalRightValues[o.value])==="undefined"){addedRight[o.value]=1;removedLeft[o.value]=1;}}if(this.removedLeftField!==null){this.removedLeftField.value = OT_join(removedLeft,this.delimiter);}if(this.removedRightField!==null){this.removedRightField.value = OT_join(removedRight,this.delimiter);}if(this.addedLeftField!==null){this.addedLeftField.value = OT_join(addedLeft,this.delimiter);}if(this.addedRightField!==null){this.addedRightField.value = OT_join(addedRight,this.delimiter);}if(this.newLeftField!==null){this.newLeftField.value = OT_join(newLeft,this.delimiter);}if(this.newRightField!==null){this.newRightField.value = OT_join(newRight,this.delimiter);}}
function OT_setDelimiter(val){this.delimiter=val;}
function OT_setAutoSort(val){this.autoSort=val;}
function OT_setStaticOptionRegex(val){this.staticOptionRegex=val;}
function OT_init(theform){var i;this.form = theform;if(!theform[this.left]){alert("OptionTransfer init(): Left select list does not exist in form!");return false;}if(!theform[this.right]){alert("OptionTransfer init(): Right select list does not exist in form!");return false;};for(i=0;i<theform[this.left].options.length;i++){if(theform[this.left].options[i].value=='dummy-value-for-xhtml-strict'){theform[this.left].options.remove(i);}};for(i=0;i<theform[this.right].options.length;i++){if(theform[this.right].options[i].value=='dummy-value-for-xhtml-strict'){theform[this.right].options.remove(i);}};this.left=theform[this.left];this.right=theform[this.right];for(i=0;i<this.left.options.length;i++){this.originalLeftValues[this.left.options[i].value]=1;}for(i=0;i<this.right.options.length;i++){this.originalRightValues[this.right.options[i].value]=1;}if(this.removedLeftField!==null){this.removedLeftField=theform[this.removedLeftField];}if(this.removedRightField!==null){this.removedRightField=theform[this.removedRightField];}if(this.addedLeftField!==null){this.addedLeftField=theform[this.addedLeftField];}if(this.addedRightField!==null){this.addedRightField=theform[this.addedRightField];}if(this.newLeftField!==null){this.newLeftField=theform[this.newLeftField];}if(this.newRightField!==null){this.newRightField=theform[this.newRightField];}this.update();return true;}
function OptionTransfer(l,r){this.form = null;this.left=l;this.right=r;this.autoSort=true;this.delimiter=",";this.staticOptionRegex = "";this.originalLeftValues = {};this.originalRightValues = {};this.removedLeftField = null;this.removedRightField = null;this.addedLeftField = null;this.addedRightField = null;this.newLeftField = null;this.newRightField = null;this.transferLeft=OT_transferLeft;this.transferRight=OT_transferRight;this.transferAllLeft=OT_transferAllLeft;this.transferAllRight=OT_transferAllRight;this.saveRemovedLeftOptions=OT_saveRemovedLeftOptions;this.saveRemovedRightOptions=OT_saveRemovedRightOptions;this.saveAddedLeftOptions=OT_saveAddedLeftOptions;this.saveAddedRightOptions=OT_saveAddedRightOptions;this.saveNewLeftOptions=OT_saveNewLeftOptions;this.saveNewRightOptions=OT_saveNewRightOptions;this.setDelimiter=OT_setDelimiter;this.setAutoSort=OT_setAutoSort;this.setStaticOptionRegex=OT_setStaticOptionRegex;this.init=OT_init;this.update=OT_update;}

