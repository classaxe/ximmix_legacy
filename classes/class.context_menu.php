<?php
define('VERSION_CONTEXT_MENU','1.0.75');
/*
Version History:
  1.0.75 (2014-03-29)
    1) Context_Menu::_cm_community_member() now sets communityID when adding new items

  (Older version history in class.context_menu.txt)
*/

class Context_Menu extends Base{
  public $popup_size_arr = array();
  public $_modules =       array();
  public $_cm_js =         array();
  public $_cm_types =      array();

  private function _cm_article(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_article',
      'icons' =>    array(
        '18|16|2462|Edit Article',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'articles',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[1]);\n"
      ."CM_label('".$CM."3',_CM_text[1]);\n"
      ."CM_label('".$CM."4',_CM_text[1]);\n"
      ."CM_label('".$CM."5',(_CM.important ? 'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_label('".$CM."6',(_CM.shared ?    'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_show ('".$CM."7',_CM_text[3]);\n"
      ."CM_label('".$CM."8','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."9',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
       $CM,
       $this->draw_cm_context(
         '',
         $icons[0],
         $this->draw_cm_actions(
           $this->draw_cm_action(
             's','','Edit','m',$CM.'1',
              "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
             .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
             true
           )
          .$this->draw_cm_action(
             's','','Delete','m',$CM.'2',
             "if (confirm('Delete this article?')){"
            ."  CM_CloseContext();geid_set('command','article_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
            ."};"
           )
          .$this->draw_cm_action(
             's','','View','m',$CM.'3',
             "CM_CloseContext();window.location=base_url+'article/'+_CM.ID;"
           )
          .($this->admin_level==3 ?
             $this->draw_cm_action(
               's','','Export','m',$CM.'4',
               "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
             )
           :
             ""
           )
          .$this->draw_cm_action(
             's','','Important:','m',$CM.'5',
             "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_important');geid_set('targetID',_CM.ID);geid('form').submit();"
           )
          .(System::has_feature('module-community') ?
             $this->draw_cm_action(
               's','','Shared:','m',$CM.'6',
               "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_shared');geid_set('targetID',_CM.ID);geid('form').submit();"
             )
           :
             ""
           )
         )
       )
      .($this->admin_level>1 ?
         $this->draw_cm_context(
           $CM.'7',
           $icons[1],
           $this->draw_cm_actions(
             $this->draw_cm_action(
              's',$CM.'8','Edit','m',$CM.'9',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
             )
           )
         )
        : ""
       )
      .$this->draw_div_tip('article')
     );
  }

  private function _cm_contact(){
    if ($this->admin_level<2){
      return "";
    }
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_contact',
      'icons' =>    array(
        '18|16|7880|Edit Contact',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'contact',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_show ('".$CM."3',_CM_text[3]);\n"
      ."CM_label('".$CM."4','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."5',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','',"Edit Contact ",'s',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].");",
            true
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'2',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
          : ""
          )
        )
      )
      .($this->admin_level>1 ?
         $this->draw_cm_context(
           $CM.'3',
           $icons[1],
           $this->draw_cm_actions(
             $this->draw_cm_action(
              'm',$CM.'4','Edit Block layout','s',$CM.'5',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
             )
           )
         )
        : ""
       )
     .$this->draw_div_tip('contact')
    );
  }

  private function _cm_content_block(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_content_block',
      'icons' =>    array(
        '19|16|2071|Edit Content Block'
      ),
      'reports' =>  array(
        'content_block'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','','Edit','s',$CM.'1',
             "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
            .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'2',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
          )
        )
      )
      .$this->draw_div_tip('content_block')
    );
  }

  private function _cm_community(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_community',
      'icons' =>    array(
        '18|16|7898|Edit Community'
      ),
      'reports' =>  array(
        'community'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit ','l',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
          .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','l',$CM.'2',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
             )
            : ""
           )
         )
       )
      .$this->draw_div_tip('community')
    );
  }

  private function _cm_community_member(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_community_member',
      'icons' =>    array(
        '19|16|6296|Edit Community Member',
        '18|16|6186|New Article for Community Member',
        '18|16|6204|New Event for Community Member',
        '18|16|6222|New News Item for Community Member',
        '18|16|5202|New Podcast for Community Member',
        '19|16|4713|View Gallery for Community Member'
      ),
      'reports' =>  array(
        'community_member',
        'community_member.articles',
        'community_member.events',
        'community_member.news-items',
        'community_member.podcasts'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
      ."CM_show('CM_community_member_article',_CM.full_member);\n"
      ."CM_show('CM_community_member_newsitem',_CM.full_member||_CM.ministerial_member);\n"
      ."CM_show('CM_community_member_podcast',_CM.full_member);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit ','l',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            's','','View','l',$CM.'2',
            "CM_CloseContext();popWin(_CM.path,'member_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',1200,780,1);"
          )
         .$this->draw_cm_action(
            's','','Print','l',$CM.'3',
            "CM_CloseContext();popWin(_CM.path+'?print=1','member_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',920,620,1);"
          )
         .$this->draw_cm_action(
            's','','Export','l',$CM.'4',
            "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
          )
        )
      )
      .$this->draw_cm_context(
         'CM_community_member_article',
         $icons[1],
         $this->draw_cm_actions(
           $this->draw_cm_action(
              'l','','New Article','','',
               "CM_CloseContext();details('".$reports[1]."','',"
              .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','',0,'memberID='+_CM.ID+'&communityID='+_CM.communityID);"
           )
          .$this->draw_cm_action(
              'l','','View all Articles','','',
               "CM_CloseContext();report('".$reports[1]."',_CM.ID,540,800,1,'communityID='+_CM.communityID);"
           )
         )
       )
      .$this->draw_cm_context(
         'CM_community_member_event',
         $icons[2],
         $this->draw_cm_actions(
           $this->draw_cm_action(
              'l','','New Event','','',
               "CM_CloseContext();void details('".$reports[2]."','',"
              .$this->popup_size_arr[$reports[2]]['h'].",".$this->popup_size_arr[$reports[2]]['w'].",'','',0,'memberID='+_CM.ID+'&communityID='+_CM.communityID+'&map_location='+_CM.map_location);"
           )
          .$this->draw_cm_action(
              'l','','View all Events','','',
               "CM_CloseContext();report('".$reports[2]."',_CM.ID,540,800,1,'communityID='+_CM.communityID+'&map_location='+_CM.map_location);"
           )
         )
       )
      .$this->draw_cm_context(
         'CM_community_member_newsitem',
         $icons[3],
         $this->draw_cm_actions(
           $this->draw_cm_action(
              'l','','New News Item','','',
               "CM_CloseContext();void details('".$reports[3]."','',"
              .$this->popup_size_arr[$reports[3]]['h'].",".$this->popup_size_arr[$reports[3]]['w'].",'','',0,'memberID='+_CM.ID+'&communityID='+_CM.communityID);"
           )
          .$this->draw_cm_action(
              'sl','','View all News Items','','',
               "CM_CloseContext();report('".$reports[3]."',_CM.ID,540,800,1,'communityID='+_CM.communityID);"
           )
         )
       )
      .$this->draw_cm_context(
         'CM_community_member_podcast',
         $icons[4],
         $this->draw_cm_actions(
           $this->draw_cm_action(
              'l','','New Podcast','','',
               "CM_CloseContext();void details('".$reports[4]."','',"
              .$this->popup_size_arr[$reports[4]]['h'].",".$this->popup_size_arr[$reports[4]]['w'].",'','',0,'memberID='+_CM.ID+'&communityID='+_CM.communityID);"
           )
          .$this->draw_cm_action(
              'l','','View all Podcasts','','',
               "CM_CloseContext();report('".$reports[4]."',_CM.ID,540,800,1,'communityID='+_CM.communityID);"
           )
          .$this->draw_cm_action(
              'l','','View Sermons Collection','','',
               "CM_CloseContext();popWin('/'+_CM.path.split('/')[1]+'/'+_CM.path.split('/')[2]+'/sermons/member/'+_CM.path.split('/')[3]+'?print=1','sermons_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',920,620,1);"
           )
         )
       )
      .$this->draw_cm_context(
         'CM_community_member_gallery',
         $icons[5],
         $this->draw_cm_actions(
           $this->draw_cm_action(
              'l','','View Gallery','','',
               "CM_CloseContext();popWin('/'+_CM.path.split('/')[1]+'/'+_CM.path.split('/')[2]+'/gallery/'+_CM.path.split('/')[3]+'?print=1','gallery_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',920,620,1);"
           )
           .$this->draw_cm_action(
              'l','','View Gallery Profile','','',
               "CM_CloseContext();popWin('/'+_CM.path.split('/')[1]+'/'+_CM.path.split('/')[2]+'/gallery/'+_CM.path.split('/')[3]+'/profile?print=1','gallery_profile_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',920,620,1);"
           )
         )
       )
      .$this->draw_div_tip('community_member')
    );
  }

  private function _cm_community_member_article(){
    $args = array(
      'CM' =>       'CM_module_cm_article',
      'icons' =>    array(
        '18|16|2462|Edit Community Member Article',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'community_member.articles',
        'block_layout'
      )
    );
    return $this->_cm_article($args);
  }

  private function _cm_community_member_event(){
    $args = array(
      'CM' =>       'CM_module_cm_event',
      'icons' =>    array(
        '18|16|2516|Edit Community Member Event',
        '20|16|124|View Event Registrants',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'community_member.events',
        'block_layout'
      )
    );
    return $this->_cm_event($args);
  }

  private function _cm_community_member_news(){
    $args = array(
      'CM' =>       'CM_module_cm_news',
      'icons' =>    array(
        '18|16|2480|Edit Community Member News Item',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'community_member.news-items',
        'block_layout'
      )
    );
    return $this->_cm_news($args);
  }

  private function _cm_community_member_podcast(){
    $args = array(
      'CM' =>       'CM_module_cm_podcast',
      'icons' =>    array(
        '18|16|2534|Edit Community Member Podcast',
        '19|16|4750|Edit Podcast Album',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'community_member.podcasts',
        'podcast-albums',
        'block_layout'
      )
    );
    return $this->_cm_podcast($args);
  }

  private function _cm_community_sponsor(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_community_sponsor',
      'icons' =>    array(
        '19|16|4676|Edit Sponsor Image',
        '19|16|4713|Edit Sponsorship Plan'
      ),
      'reports' =>  array(
        'sponsors-for-sponsorship-plan',
        'sponsorship-plans-for-community'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',(_CM.enabled ? 'Disable' :'Enable'));\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
      ."CM_label('".$CM."5',_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's',$CM.'2','Toggle','m',$CM.'3',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','gallery_image_toggle_enabled');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           's','','Delete','m',$CM.'4',
            "if (confirm('Delete this Sponsor?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
        .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'5',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
         )
        )
      )
     .$this->draw_div_tip('gallery_image')
    );
  }

  private function _cm_event(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_event',
      'icons' =>    array(
        '18|16|2516|Edit Event',
        '20|16|124|View Event Registrants',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'events',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2','Delete',_CM.event_registrants);\n"
      ."CM_label('".$CM."3',_CM_text[0],_CM.event_registrants);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
      ."CM_label('".$CM."5',(_CM.important ? 'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_label('".$CM."6',_CM_text[0]);\n"
      ."CM_label('".$CM."7',(_CM.shared ?    'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_show ('".$CM."8',_CM.event_registrants);\n"
      ."CM_label('".$CM."9','View Registrants ('+_CM.event_registrants+')');\n"
      ."CM_show ('".$CM."10',_CM_text[3]);\n"
      ."CM_label('".$CM."11','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."12',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
            .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            's',$CM.'2','Delete','m',$CM.'3',
             "if (_CM.event_registrants){"
            ."  alert('PROBLEM:\\nYou cannot delete an event for which people have already registered.\\n\\nFirst view the registrants and inform them of the cancellation, \\nthen select all registrations shown in the report and delete them.\\n\\nYou may then safely delete the event without upsetting anyone.');"
            ."}\n"
            ."else {\n"
            ."  if (confirm('Delete this event?')){"
            ."    CM_CloseContext();geid_set('command','event_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
            ."  }"
            ."}\n"
          )
         .$this->draw_cm_action(
            's','','View','m',$CM.'4',
            "CM_CloseContext();popWin(base_url+ 'event/'+_CM.ID,'event_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',720,400,1);"
          )
          .$this->draw_cm_action(
             's','','Important:','m',$CM.'5',
             "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_important');geid_set('targetID',_CM.ID);geid('form').submit();"
           )
         .($this->admin_level==3 ?
             $this->draw_cm_action(
               's','','Export','m',$CM.'6',
               "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
             )
           :
             ""
          )
          .(System::has_feature('module-community') ?
             $this->draw_cm_action(
               's','','Shared:','m',$CM.'7',
               "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_shared');geid_set('targetID',_CM.ID);geid('form').submit();"
             )
           :
             ""
           )
        )
      )
     .$this->draw_cm_context(
        $CM.'8',
        $icons[1],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','View','m',$CM.'9',
            "CM_CloseContext();void view_event_registrants(_CM.ID,800,400);"
          )
        )
      )
    .($this->admin_level>1 ?
       $this->draw_cm_context(
         $CM.'10',
         $icons[2],
         $this->draw_cm_actions(
           $this->draw_cm_action(
             's',$CM.'11','Edit','l',$CM.'12',
             "if (_CM_ID[3]!=''){"
            ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
            .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
            ."}"
            ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
           )
         )
       )
       : ""
     )
    .$this->draw_div_tip('event')
    );
  }

  private function _cm_gallery_album(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_gallery_album',
      'icons' =>    array(
        '19|16|4713|Edit Gallery Album',
        '19|16|6258|New Gallery Album'
      ),
      'reports' =>  array('gallery-albums')
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1','Edit',!_CM.ID);\n"
      ."CM_label('".$CM."2',_CM_text[0],!_CM.ID);\n"
      ."CM_label('".$CM."3','Delete',!_CM.can_delete);\n"
      ."CM_label('".$CM."4',_CM_text[0],!_CM.can_delete);\n"
      ."CM_label('".$CM."5',_CM_text[0]);\n"
      ."CM_label('".$CM."6','inside '+_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context_noline(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's',$CM.'1','Edit','m',$CM.'2',
            "if (_CM.ID!=0){"
            ."  CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
            .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');"
            ."}"
            ."else{\n"
            ."  alert('Sorry!\\n\\nThis container album is actually simulated - you can\\\'t actually edit it.');\n"
            ."};",
            true
          )
         .$this->draw_cm_action(
           's',$CM.'3','Delete','m',$CM.'4',
             "if (_CM.can_delete){"
            ."  if (confirm('Delete this album?')){"
            ."    CM_CloseContext();geid_set('submode','gallery_album_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
            ."  }"
            ."}\n"
            ."else{alert('Sorry!\\n\\nYou can\\\'t delete an album with contents inside.\\n\\nTo remove contents, first view the album\\nthen delete all images inside it.');};"
          )
        .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'5',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
         )
        )
      )
     .$this->draw_cm_context_noline(
        '',
        $icons[1],
        $this->draw_cm_actions(
          $this->draw_cm_action(
           's','','New','m',$CM.'6',
            "if (confirm('Add a new album inside this one?')){"
           ."  CM_CloseContext();var title=prompt('Please specify title for album','New Album');"
           ."  if(title){"
           ."    geid_set('targetValue',title);"
           ."    geid_set('submode','gallery_album_sub_album');"
           ."    geid_set('targetID',_CM.ID);"
           ."    geid('form').submit()"
           ."  }"
           ."};"
          )
        )
      )
     .$this->draw_div_tip('gallery_album')
    );
  }

  private function _cm_gallery_image(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_gallery_image',
      'icons' =>    array(
        '19|16|4676|Edit Gallery Image',
        '19|16|4713|Edit Gallery Album',
        '19|16|4947|New Gallery Image',
        '19|16|5101|New TREB Listing',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'gallery-images',
        'gallery-albums',
        'gallery-images',
        'module.treb.listings',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',(_CM.enabled ? 'Disable' :'Enable'));\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
      ."CM_label('".$CM."5',_CM_text[0]);\n"
      ."CM_show ('".$CM."6',typeof _CM_ID[2]!=='undefined' && _CM_ID[2]!=='0');\n"
      ."CM_label('".$CM."7',_CM_text[2],typeof _CM_ID[2]==='undefined' || _CM_ID[2]==='0');\n"
      ."CM_label('".$CM."8',_CM_text[2],typeof _CM_ID[2]==='undefined' || _CM_ID[2]==='0');\n"
      ."CM_show ('".$CM."9',typeof _CM_ID[3]!=='undefined');\n"
      ."CM_label('".$CM."10','Edit');\n"
      ."CM_label('".$CM."11',_CM_text[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's',$CM.'2','Toggle','m',$CM.'3',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','gallery_image_toggle_enabled');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           'm','','<img src="'.BASE_PATH.'img/spacer" class="icon" style="float:none;display:inline;width:8px;height:10px;background-position:-5389px 0" alt="Rotate Left" /> Rotate Left','','',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_rotate_left');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           'm','','<img src="'.BASE_PATH.'img/spacer" class="icon" style="float:none;display:inline;width:8px;height:10px;background-position:-5397px 0" alt="Rotate Right" /> Rotate Right','','',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_rotate_right');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           's','','Delete','m',$CM.'4',
            "if (confirm('Delete this Gallery Image?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
        .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'5',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
         )
        )
      )
     .$this->draw_cm_context(
        $CM.'6',
        $icons[1],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'7',
            "CM_CloseContext();void details('".$reports[1]."',_CM_ID[2],"
           .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
          )
         .$this->draw_cm_action(
           's','','Cover','m',$CM.'8',
            "if (confirm('Use the image as the cover for the album '+_CM_text[2].replace(/&amp;quot;/g,quot)+'?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_cover');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
        )
      )
     .$this->draw_cm_context(
        '',
        $icons[2],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','New Gallery Image','s','',
            "CM_CloseContext();void details('".$reports[2]."','',"
           .$this->popup_size_arr[$reports[2]]['h'].",".$this->popup_size_arr[$reports[2]]['w'].",'','',false,'category='+_CM.category+'&amp;parentID='+_CM_ID[2]);"
          )
        )
      )
     .($this->_modules['TREB'] ?
        $this->draw_cm_context(
          '',
          $icons[3],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              'l','','New TREB Listing','s','',
              "CM_CloseContext();void details('".$reports[3]."','',"
             .$this->popup_size_arr[$reports[3]]['h'].",".$this->popup_size_arr[$reports[3]]['w'].",'','',false,'category='+_CM.category+'&amp;parentID='+_CM_ID[2]);"
            )
          )
        )
       :
        ""
      )
     .($this->admin_level>1 ?
       $this->draw_cm_context(
         $CM.'9',
         $icons[4],
         $this->draw_cm_actions(
           $this->draw_cm_action(
             's',$CM.'10','Edit','m',$CM.'11',
             "if (_CM_ID[3]!=''){"
            ."CM_CloseContext();void details('".$reports[4]."',_CM_ID[3],"
            .$this->popup_size_arr[$reports[4]]['h'].",".$this->popup_size_arr[$reports[4]]['w'].",'','');"
            ."}"
            ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
           )
         )
       )
       : ""
     )
     .$this->draw_div_tip('gallery_image')
    );
  }


  private function _cm_job(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_job_posting',
      'icons' =>    array(
        '18|16|2498|Edit Job Posting',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'job-postings',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[1]);\n"
      ."CM_label('".$CM."3',_CM_text[1]);\n"
      ."CM_label('".$CM."4',_CM_text[1]);\n"
      ."CM_show ('".$CM."5',_CM_text[3]);\n"
      ."CM_label('".$CM."6','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."7',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's','','Delete','m',$CM.'2',
            "if (confirm('Delete this job posting?')){"
           ."  CM_CloseContext();geid_set('command','job_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
           's','','View','m',$CM.'3',
           "CM_CloseContext();window.location=base_url+'job-posting/'+_CM.ID;"
          )
        .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'4',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
         )
        )
      )
     .($this->admin_level==2 || $this->admin_level==3 ?
        $this->draw_cm_context(
          $CM.'5',
          $icons[1],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              's',$CM.'6','Edit','m',$CM.'7',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
            )
          )
        )
        : ""
      )
     .$this->draw_div_tip('job-posting')
    );
  }

  private function _cm_navbutton(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_navbutton',
      'icons' =>    array(
        '26|16|1898|Edit Button',
        '26|16|1924|Edit Button Suite',
        '26|16|1950|Edit Button Style'
      ),
      'reports' =>  array(
        'navbuttons',
        'navsuite',
        'navbuttons_for_navsuite',
        'navstyle'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM.navbuttonText);\n"
      ."CM_label('".$CM."2','Delete',_CM.hasSubmenu);\n"
      ."CM_label('".$CM."3',_CM.navbuttonText,_CM.hasSubmenu);\n"
      ."CM_label('".$CM."4','Add Submenu to ',_CM.canAddSubnav<1);\n"
      ."CM_label('".$CM."5',_CM.navbuttonText,_CM.canAddSubnav<1);\n"
      ."CM_label('".$CM."6',_CM.navbuttonText);\n"
      ."CM_label('".$CM."7',_CM.navsuiteName);\n"
      ."CM_label('".$CM."8',_CM.navsuiteName);\n"
      ."CM_label('".$CM."9',_CM.navstyleName);\n"
      ."CM_label('".$CM."10',_CM.navstyleName);\n"
      ."btn = geid('btn_'+_CM.navbuttonID);\n"
      ."if (btn){\n"
      ."  img = btn.childNodes[0].childNodes[0];\n"
      ."  if (img){\n"
      ."    b_src = img.style.backgroundImage;\n"
      ."    b_url = b_src.split('\"')[1];\n"
      ."    CM_label('CM_navbutton_t11',\n"
      ."      \"<div>\"+\n"
      ."      \"  <div class='fl' style='width:40px;line-height:\"+img.height+\"px;'>Active</div><img src='\"+base_url+\"img/spacer' style='margin:1px;background:\"+b_src+\" no-repeat \"+(_CM.hasSubmenu ? '100%' : '0')+\" 0px' width='\"+img.width+\"' height='\"+img.height+\"' alt='Active'/><br class='clr_b'/>\\n\"+\n"
      ."      \"  <div class='fl' style='width:40px;line-height:\"+img.height+\"px;'>Down</div><img src='\"+base_url+\"img/spacer' style='margin:1px;background:\"+b_src+\" no-repeat \"+(_CM.hasSubmenu ? '100%' : '0')+\" \"+(-1*img.height)+\"px' width='\"+img.width+\"' height='\"+img.height+\"' alt='Down'/><br class='clr_b'/>\\n\"+\n"
      ."      \"  <div class='fl' style='width:40px;line-height:\"+img.height+\"px;'>Normal</div><img src='\"+base_url+\"img/spacer' style='margin:1px;background:\"+b_src+\" no-repeat \"+(_CM.hasSubmenu ? '100%' : '0')+\" \"+(-2*img.height)+\"px' width='\"+img.width+\"' height='\"+img.height+\"' alt='Normal'/><br class='clr_b'/>\\n\"+\n"
      ."      \"  <div class='fl' style='width:40px;line-height:\"+img.height+\"px;'>Over</div><img src='\"+base_url+\"img/spacer' style='margin:1px;background:\"+b_src+\" no-repeat \"+(_CM.hasSubmenu ? '100%' : '0')+\" \"+(-3*img.height)+\"px' width='\"+img.width+\"' height='\"+img.height+\"' alt='Over'/>\\n\"+\n"
      ."      \"</div>\\n\"+\n"
      ."      \"<p class='clr_b' style='margin:0;' align='center'>(Width: \"+img.width+\"px, Height: \"+img.height+\"px, [<a target='_blank' href='\"+b_url+\"'>image</a>])</p>\"\n"
      ."    );\n"
      ."  }\n"
      ."  else {\n"
      ."    CM_label('CM_navbutton_t11','(None - this button is not an image)');\n"
      ."  }\n"
      ."}\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','','Edit','s',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.navbuttonID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            'm',$CM.'2','','s',$CM.'3',
            "if (_CM.hasSubmenu==1){"
           ." alert('The '+_CM.navbuttonText.replace(/&amp;quot;/ig,'&quot;')+' button has a submenu attached to it.\\nYou cannot delete a button that has a buttonsuite attached.');\n"
           ."}\n"
           ."else{\n"
           ."  if (confirm('Delete '+_CM.navbuttonText.replace(/&amp;quot;/ig,'&quot;')+' button?')){"
           ."    CM_CloseContext();geid_set('command','navbutton_delete');"
           ."    geid_set('targetID',_CM.navbuttonID);geid('form').submit();"
           ."  }"
           ."}"
          )
         .$this->draw_cm_action(
            'm',$CM.'4','','s',$CM.'5',
            "switch(_CM.canAddSubnav){"
           ."  case -1:"
           ."    alert('The '+_CM.navbuttonText.replace(/&amp;quot;/ig,'&quot;')+' button already has a submenu attached');"
           ."    break;"
           ."  case 0:"
           ."    alert('The '+_CM.navstyleName.replace(/&amp;quot;/ig,'&quot;')+' buttonstyle has no submenu style defined.');"
           ."    break;"
           ."  default:"
           ."    if (confirm('Add submenu to '+_CM.navbuttonText.replace(/&amp;quot;/ig,'&quot;')+' button?')){"
           ."      CM_CloseContext();geid_set('command','subnav_add');"
           ."      geid_set('targetID',_CM.navbuttonID);geid('form').submit();"
           ."    }"
           ."  break;"
           ."}"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'6',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.navbuttonID)"
            )
           : ""
          )
        )
      )
     .$this->draw_cm_context(
        '',
        $icons[1],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','','Edit','s',$CM.'7',
            "CM_CloseContext();void details('".$reports[1]."',_CM.navsuiteID,"
           .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
          )
         .$this->draw_cm_action(
           'm','','Add button...','','',
           "CM_CloseContext();void details('".$reports[2]."','',"
           .$this->popup_size_arr[$reports[2]]['h'].",".$this->popup_size_arr[$reports[2]]['w'].",'',_CM.navsuiteID);"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'8',
              "CM_CloseContext();export_sql('".$reports[1]."',_CM.navsuiteID)"
            )
          : ""
          )
        )
      )
     .($this->admin_level>1 ?
        $this->draw_cm_context(
          '',
          $icons[2],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              'm','','Edit','s',$CM.'9',
              "CM_CloseContext();void details('".$reports[3]."',_CM.navstyleID,"
             .$this->popup_size_arr[$reports[3]]['h'].",".$this->popup_size_arr[$reports[3]]['w'].",'','');"
            )
          .($this->admin_level==3 ?
             $this->draw_cm_action(
               'm','','Export SQL for','s',$CM.'10',
               "CM_CloseContext();export_sql('".$reports[3]."',_CM.navstyleID)"
             )
           : ""
           )
         )
       )
       : ""
       )
     .$this->draw_div_tip('navbutton')
     .$this->draw_div_sample('Image states for this button:',"<span id='CM_navbutton_t11'></span>")
    );
  }

  private function _cm_news(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_news_item',
      'icons' =>    array(
        '18|16|2480|Edit News Item',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'news-items',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[1]);\n"
      ."CM_label('".$CM."3',_CM_text[1]);\n"
      ."CM_label('".$CM."4',_CM_text[1]);\n"
      ."CM_label('".$CM."5',(_CM.important ? 'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_label('".$CM."6',(_CM.shared ?    'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_show ('".$CM."7',_CM_text[3]);\n"
      ."CM_label('".$CM."8','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."9',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            's','','Delete','m',$CM.'2',
            "if (confirm('Delete this news item?')){"
           ."  CM_CloseContext();geid_set('command','news_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
            's','','View','m',$CM.'3',
            "CM_CloseContext();window.location=base_url+'news-item/'+_CM.ID;"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'4',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
          )
         .$this->draw_cm_action(
             's','','Important:','m',$CM.'5',
             "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_important');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .(System::has_feature('module-community') ?
             $this->draw_cm_action(
               's','','Shared:','m',$CM.'6',
               "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_shared');geid_set('targetID',_CM.ID);geid('form').submit();"
             )
           :
             ""
          )
        )
      )
     .($this->admin_level>1 ?
        $this->draw_cm_context(
          $CM.'7',
          $icons[1],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              's',$CM.'8','Edit','m',$CM.'9',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
            )
          )
        )
        : ""
       )
      .$this->draw_div_tip('news-item')
     );
  }

  private function _cm_page(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_page',
      'icons' =>    array(
        '18|16|297|Edit Page',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'pages',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[1]);\n"
      ."CM_label('".$CM."3',_CM_text[1]);\n"
      ."CM_show ('".$CM."4',_CM_text[3]);\n"
      ."CM_label('".$CM."5','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."6',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's','','Delete','m',$CM.'2',
            "if (confirm('Delete this page?')){"
           ."  CM_CloseContext();geid_set('command','delete_page');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
/*
         .$this->draw_cm_action(
           's','','View','m',$CM.'3',
           "CM_CloseContext();window.location=base_url+'page/'+_CM.ID;"
          )
*/
        )
      )
     .($this->admin_level==2 || $this->admin_level==3 ?
        $this->draw_cm_context(
          $CM.'4',
          $icons[1],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              's',$CM.'5','Edit','m',$CM.'6',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
            )
          )
        )
        : ""
      )
     .$this->draw_div_tip('page')
    );
  }

  private function _cm_podcast(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_podcast',
      'icons' =>    array(
        '18|16|2534|Edit Podcast',
        '19|16|4750|Edit Podcast Album',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'podcasts',
        'podcast-albums',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
      ."CM_label('".$CM."5',(_CM.important ? 'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_label('".$CM."6',(_CM.shared ?    'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_show ('".$CM."7',!(typeof _CM_ID[2]==='undefined' || _CM_ID[2]=='0'));\n"
      ."CM_label('".$CM."8',_CM_text[2]);\n"
      ."CM_show ('".$CM."9',_CM_text[3]);\n"
      ."CM_label('".$CM."10','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."11',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
            .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's','','Delete','m',$CM.'2',
            "if (confirm('Delete this podcast?')){"
           ."  CM_CloseContext();geid('command').value='podcast_delete';geid('targetID').value=_CM.ID;geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
           's','','View','m',$CM.'3',
           "CM_CloseContext();window.location=base_url+'podcast/'+_CM.ID;"
          )
         .($this->admin_level==3 ?
             $this->draw_cm_action(
               's','','Export','m',$CM.'4',
               "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
             )
             : ""
          )
         .$this->draw_cm_action(
            's','','Important:','m',$CM.'5',
            "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_important');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .(System::has_feature('module-community') ?
             $this->draw_cm_action(
               's','','Shared:','m',$CM.'6',
               "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_shared');geid_set('targetID',_CM.ID);geid('form').submit();"
             )
           :
             ""
          )
        )
      )
     .$this->draw_cm_context(
        $CM.'7',
        $icons[1],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'8',
            "CM_CloseContext();void details('".$reports[1]."',_CM_ID[2],"
           .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
          )
        )
      )
     .($this->admin_level>1 ?
        $this->draw_cm_context(
          $CM.'9',
          $icons[2],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              's',$CM.'10','Edit','m',$CM.'11',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[2]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[2]]['h'].",".$this->popup_size_arr[$reports[2]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
            )
          )
        )
        : ""
       )
      .$this->draw_div_tip('podcast')
    );
  }

  private function _cm_podcastalbum(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_podcastalbum',
      'icons' =>    array(
        '19|16|4750|Edit Podcast Album',
        '19|16|6277|New Podcast Album',
        '18|16|5202|New Podcast'
      ),
      'reports' =>  array(
        'podcast-albums',
        'podcasts'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2','Delete',!_CM.can_delete);\n"
      ."CM_label('".$CM."3',_CM_text[0],!_CM.can_delete);\n"
      ."CM_label('".$CM."4',(_CM.important ? 'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_label('".$CM."5',_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's',$CM.'2','Delete','m',$CM.'3',
             "if (_CM.can_delete){"
            ."  if (confirm('Delete this album?')){"
            ."    CM_CloseContext();geid_set('submode','podcast_album_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
            ."  }"
            ."}\n"
            ."else{alert('Sorry!\\n\\nYou cannot delete an album with contents inside.\\n\\nTo remove contents, first view the album\\nthen delete all items inside it.');};"
          )
         .$this->draw_cm_action(
            's','','Important:','m',$CM.'4',
            "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','posting_toggle_important');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'5',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
          )
        )
      )
     .$this->draw_cm_context(
        '',
        $icons[1],
        $this->draw_cm_actions(
          $this->draw_cm_action(
           'l','','New Album at this level','','',
            "CM_CloseContext();void details('".$reports[0]."','',"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','',false,'parentID='+_CM_ID[2]);"
          )
        )
      )
     .$this->draw_cm_context(
        '',
        $icons[2],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','New Podcast','s','',
            "CM_CloseContext();void details('".$reports[1]."','',"
           .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','',false,'parentID='+_CM.ID);"
          )
        )
      )
     .$this->draw_div_tip('podcastalbum')
    );
  }

  private function _cm_product(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_product',
      'icons' =>    array(
        '18|16|4182|Edit Product',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'product',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[1]);\n"
      ."CM_label('".$CM."3',_CM_text[1]);\n"
      ."CM_label('".$CM."4',(_CM.important ? 'Yes' : 'No')+'<div style=\'float:right\'>(Click to change)<\/div>');\n"
      ."CM_label('".$CM."5',_CM_text[1]);\n"
      ."CM_show ('".$CM."6',_CM_text[3]);\n"
      ."CM_label('".$CM."7','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."8',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            's','','Delete','m',$CM.'2',
            "if (confirm('Delete this product?')){"
           ."  CM_CloseContext();geid('command').value='product_delete';geid('targetID').value=_CM.ID;geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
            's','','View','m',$CM.'3',
            "CM_CloseContext();window.location=base_url+'product/'+_CM.ID;"
          )
         .$this->draw_cm_action(
            's','','Important:','m',$CM.'4',
            "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','product_toggle_important');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              's','','Export','m',$CM.'5',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
          )
        )
      )
     .($this->admin_level>1 ?
        $this->draw_cm_context(
          $CM.'6',
          $icons[1],
          $this->draw_cm_actions(
            $this->draw_cm_action(
              's',$CM.'7','Edit','m',$CM.'7',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
            )
          )
        )
        : ""
      )
     .$this->draw_div_tip('product')
    );
  }

  private function _cm_report(){
    if ($this->admin_level<3){
      return "";
    }
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_report',
      'icons' =>    array(
        '15|16|1976|Edit Report'
      ),
      'reports' =>  array(
        'report'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
      ""
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','Edit this report','','',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            'l','','Export Report','','',
            "export_sql('".$reports[0]."',_CM.ID);"
          )
        )
      )
     .$this->draw_div_tip('report')
    );
  }

  private function _cm_report_column(){
    if ($this->admin_level<3){
      return "";
    }
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_report_column',
      'icons' =>    array(
        '15|16|1991|Edit Report Column'
      ),
      'reports' =>  array(
        'report_columns'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
      ""
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','Edit this column','','',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           'l','','Export this column','','',
           "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
          )
         .$this->draw_cm_action(
           'l','','Delete this column','','',
           "if (confirm('Delete this column?')){"
          ."  CM_CloseContext();geid('targetID').value=_CM.ID;geid('submode').value='column_delete';geid('form').submit();"
          ."};"
          )
        )
      )
     .$this->draw_div_tip('report')
    );
  }

  private function _cm_report_filter(){
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_report_filter',
      'icons' =>    array(
        '15|16|2006|Edit Report Filter'
      ),
      'reports' =>  array(
        'report_filters'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_show('".$CM."1',_CM_ID[4]=='g' || _CM_ID[4]=='s');\n"
      ."CM_show('".$CM."2',_CM_ID[4]=='g' || _CM_ID[4]=='p');\n"
      ."CM_show('CM_report_filter_assign_global',_CM_ID[4]=='s' || _CM_ID[4]=='p');\n"
    );
    return $this->draw_cm(
      'CM_report_filter',
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','Delete this filter','','',
            "var label = geid('filters_for_report_'+_CM_ID[1]+'_'+_CM.ID).title;\n"
           ."if (confirm('Delete filter '+String.fromCharCode(34)+label+String.fromCharCode(34)+'?')){"
           ."  CM_CloseContext();geid('targetReportID').value=_CM_ID[1];geid('submode').value='filter_delete';geid('targetID').value=_CM.ID;geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
            'l','','Rename this filter','','',
             "var label = geid('filters_for_report_'+_CM_ID[1]+'_'+_CM.ID).innerHTML;\n"
            ."if (label.substr(0,5)=='<img '){\n"
            ."  var s = label.indexOf('alt=')+5;\n"
            ."  var l = label.substr(s).indexOf(String.fromCharCode(34));\n"
            ."  label = '[ICON]'+label.substr(s,l)+'[/ICON]'+label.substr(label.indexOf('>')+1);\n"
            ."}\n"
            ."var _n=prompt('New name for filter',label);\n"
            ."if (_n!==null && _n!==''){\n"
            ."  CM_CloseContext();\n"
            ."  geid_set('targetReportID',_CM_ID[1]);\n"
            ."  geid_set('submode','filter_rename');\n"
            ."  geid_set('targetID',_CM.ID);\n"
            ."  geid_set('targetValue',_n);\n"
            ."  geid('form').submit();\n"
            ."};\n"
          )
        .($this->admin_level>1?
           $this->draw_cm_action(
              'l',$CM.'1','Share just to me','','',
              "if (confirm('Make available only to me?')){"
             ."CM_CloseContext();"
             ."geid('targetReportID').value=_CM_ID[1];"
             ."geid('submode').value='filter_assign_me';"
             ."geid('targetID').value=_CM.ID;"
             ."geid('form').submit();"
             ."};"
            )
          .$this->draw_cm_action(
              'l',$CM.'2',"Share with this site",'','',
              "if (confirm('Make available to all users on this site?')){"
             ."CM_CloseContext();"
             ."geid('targetReportID').value=_CM_ID[1];"
             ."geid('submode').value='filter_assign_local';"
             ."geid('targetID').value=_CM.ID;"
             ."geid('form').submit();"
             ."};"
           )
           : ""
         )
        .($this->admin_level==3 ?
           $this->draw_cm_action(
              'l',$CM.'3','Share with all sites','','',
              "if (confirm('Make available to all users on all sites?')){"
             ."CM_CloseContext();"
             ."geid_set('targetReportID',_CM_ID[1]);"
             ."geid_set('submode','filter_assign_global');"
             ."geid_set('targetID',_CM.ID);"
             ."geid('form').submit();"
             ."};"
           )
           : ""
         )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'l','','Export filter as SQL','','',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
            : ""
          )
        )
      )
      .$this->draw_div_tip('report_filters')
    );
  }

  private function _cm_theme_accent(){
    if ($this->admin_level<2){
      return "";
    }
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_theme_accent',
      'icons' =>    array(
        '18|16|2133|Edit Theme'
      ),
      'reports' =>  array(
        'theme_accent_',
        'theme'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_ID[1]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','',"Edit Accent <span id='".$CM."1'></span> for",'s',$CM.'2',
            "CM_CloseContext();void details('".$reports[0]."'+_CM_ID[1],_CM.ID,"
           .$this->popup_size_arr[$reports[0].'1']['h'].",".$this->popup_size_arr[$reports[0].'1']['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            'm','','Edit Theme','s',$CM.'3',
            "CM_CloseContext();void details('".$reports[1]."',_CM.ID,"
           .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'4',
              "CM_CloseContext();export_sql('".$reports[1]."',_CM.ID)"
            )
          : ""
          )
        )
      )
     .$this->draw_div_tip('theme')
    );
  }

  private function _cm_theme_banner(){
    if ($this->admin_level<2){
      return "";
    }
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_theme_banner',
      'icons' =>    array(
        '18|16|2133|Edit Theme'
      ),
      'reports' =>  array(
        'theme_banner',
        'theme'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_ID[1]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
    );
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','','Edit Banner for','s',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
            'm','','Edit Theme','s',$CM.'2',
            "CM_CloseContext();void details('".$reports[1]."',_CM.ID,"
           .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'3',
              "CM_CloseContext();export_sql('".$reports[1]."',_CM.ID)"
            )
           : ""
          )
        )
      )
    .$this->draw_div_tip('theme')
    );
  }

  private function _cm_user(){
    if ($this->admin_level<2){
      return "";
    }
    $args = func_get_args();
    $vars = array(
      'CM' =>       'CM_user',
      'icons' =>    array(
        '18|16|7862|Edit User',
        '18|16|4130|Edit Block Layout'
      ),
      'reports' =>  array(
        'user',
        'block_layout'
      )
    );
    $this->_get_args($args,$vars,true);
    $CM =       $vars['CM'];
    $icons =    $vars['icons'];
    $reports =  $vars['reports'];
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',_CM_text[0]);\n"
      ."CM_show ('".$CM."3',_CM_text[3]);\n"
      ."CM_label('".$CM."4','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."5',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '',
        $icons[0],
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'm','',"Edit User ",'s',$CM.'1',
            "CM_CloseContext();void details('".$reports[0]."',_CM.ID,"
           .$this->popup_size_arr[$reports[0]]['h'].",".$this->popup_size_arr[$reports[0]]['w'].");",
            true
          )
         .($this->admin_level==3 ?
            $this->draw_cm_action(
              'm','','Export SQL for','s',$CM.'2',
              "CM_CloseContext();export_sql('".$reports[0]."',_CM.ID)"
            )
          : ""
          )
        )
      )
      .($this->admin_level>1 ?
         $this->draw_cm_context(
           $CM.'3',
           $icons[1],
           $this->draw_cm_actions(
             $this->draw_cm_action(
              'm',$CM.'4','Edit Block layout','s',$CM.'5',
              "if (_CM_ID[3]!=''){"
             ."CM_CloseContext();void details('".$reports[1]."',_CM_ID[3],"
             .$this->popup_size_arr[$reports[1]]['h'].",".$this->popup_size_arr[$reports[1]]['w'].",'','');"
             ."}"
             ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
             )
           )
         )
        : ""
       )
    .$this->draw_div_tip('user')
    );
  }



  private function _cm_module_treb_listing(){
    $CM = 'CM_treb_listing';
    $this->register_js(
      $CM,
       "CM_label('".$CM."1',_CM_text[0]);\n"
      ."CM_label('".$CM."2',(_CM.enabled ? 'Disable' :'Enable'));\n"
      ."CM_label('".$CM."3',_CM_text[0]);\n"
      ."CM_label('".$CM."4',_CM_text[0]);\n"
      ."CM_label('".$CM."5','Turn Featured <b>'+(_CM['category'].indexOf('featured')!==-1 ? 'OFF' : 'ON')+'</b>');\n"
      ."CM_label('".$CM."6','Turn Open House <b>'+(_CM['category'].indexOf('open-house')!==-1 ? 'OFF' : 'ON')+'</b>');\n"
      ."CM_show ('".$CM."7',!(typeof _CM_ID[2]==='undefined' || _CM_ID[2]=='0'));\n"
      ."CM_label('".$CM."8',_CM_text[2]);\n"
      ."CM_label('".$CM."9',_CM_text[2]);\n"
      ."CM_show ('".$CM."10',!(typeof _CM_text[3]==='undefined' || _CM_text[3]==''));\n"
      ."CM_label('".$CM."11','Edit',!_CM_ID[3]);\n"
      ."CM_label('".$CM."12',_CM_text[3],!_CM_ID[3]);\n"
    );
    return $this->draw_cm(
      $CM,
      $this->draw_cm_context(
        '','19|16|5082|TREB Listing',
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'1',
            "CM_CloseContext();void details('module.treb.listings',_CM.ID,"
           .$this->popup_size_arr['module.treb.listings']['h'].",".$this->popup_size_arr['module.treb.listings']['w'].",'','');",
            true
          )
         .$this->draw_cm_action(
           's',$CM.'2','Toggle','m',$CM.'3',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('command','gallery_image_toggle_enabled');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           's','','Delete','m',$CM.'4',
            "if (confirm('Delete this TREB Listing?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_delete');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
           'm','','<img src="'.BASE_PATH.'img/spacer" class="icon" style="float:none;display:inline;width:8px;height:10px;background-position:-5389px 0" alt="Rotate Left" /> Rotate Left','m','CM_gallery_image_rotate_left_text',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_rotate_left');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           'm','','<img src="'.BASE_PATH.'img/spacer" class="icon" style="float:none;display:inline;width:8px;height:10px;background-position:-5397px 0" alt="Rotate Right" /> Rotate Right','m','CM_gallery_image_rotate_right_text',
           "CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_rotate_right');geid_set('targetID',_CM.ID);geid('form').submit();"
          )
         .$this->draw_cm_action(
           'l',$CM.'5','','','',
            "if (confirm('Toggle '+quot+'Featured'+quot+' setting?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','treb_listing_feature');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
         .$this->draw_cm_action(
           'l',$CM.'6','','','',
            "if (confirm('Toggle '+quot+'Open House'+quot+' setting?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','treb_listing_openhouse');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
        )
      )
     .$this->draw_cm_context(
        $CM.'7','19|16|4713|Gallery Album',
        $this->draw_cm_actions(
          $this->draw_cm_action(
            's','','Edit','m',$CM.'8',
            "CM_CloseContext();void details('gallery-albums',_CM_ID[2],"
           .$this->popup_size_arr['gallery-albums']['h'].",".$this->popup_size_arr['gallery-albums']['w'].",'','');"
          )
         .$this->draw_cm_action(
           's','','Cover','m',$CM.'9',
            "if (confirm('Use the image as the cover for the album '+_CM_text[2].replace(/&amp;quot;/g,String.fromCharCode(34))+'?')){"
           ."  CM_CloseContext();geid_set('source',_CM['source']);geid_set('submode','gallery_image_cover');geid_set('targetID',_CM.ID);geid('form').submit();"
           ."};"
          )
        )
      )
     .$this->draw_cm_context(
        '','19|16|5101|TREB Listing',
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','New TREB Listing','s','',
            "CM_CloseContext();void details('module.treb.listings','',"
           .$this->popup_size_arr['module.treb.listings']['h'].",".$this->popup_size_arr['module.treb.listings']['w'].",'','',false,'category='+_CM.category+'&amp;parentID='+_CM_ID[2]);"
          )
        )
      )
     .$this->draw_cm_context(
        '','19|16|4947|Gallery Image',
        $this->draw_cm_actions(
          $this->draw_cm_action(
            'l','','New Gallery Image','s','',
            "CM_CloseContext();void details('gallery-images','',"
           .$this->popup_size_arr['gallery-images']['h'].",".$this->popup_size_arr['gallery-images']['w'].",'','',false,'category='+_CM.category+'&amp;parentID='+_CM_ID[2]);"
          )
        )
      )
     .($this->admin_level>1 ?
       $this->draw_cm_context(
         $CM.'10',
         '18|16|4130|Block Layout',
         $this->draw_cm_actions(
           $this->draw_cm_action(
             's',$CM.'11','Edit','m',$CM.'12',
             "if (_CM_ID[3]!=''){"
            ."CM_CloseContext();void details('block_layout',_CM_ID[3],"
            .$this->popup_size_arr['block_layout']['h'].",".$this->popup_size_arr['block_layout']['w'].",'','');"
            ."}"
            ."else{alert('The '+_CM_text[3].replace(/&amp;quot;/ig,'&quot;')+' Block Layout is shared and cannot be edited by you.');};"
           )
         )
       )
       : ""
     )
     .$this->draw_div_tip('treb_listing')
    );
  }

  function draw_cm($ID,$content){
    $this->register_type($ID);
    return
       "<div class='context_menu' id='".$ID."'>\n"
      .$content
      ."</div>\n";
  }

  function draw_cm_action($op_css,$op_id,$op_txt,$val_css,$val_id,$js,$close=false){
    return
       "      <div class='action".($close ? " close" : "")."'"
      ." onclick=\"".$js."\">\n"
      ."        <div class='op_".$op_css."'"
      .($op_id ? " id='".$op_id."'" : "")
      .">".$op_txt."</div>\n"
      ."        <div class='val_".$val_css."'"
      .($val_id ? " id='".$val_id."'" : "")
      ."></div>\n"
      ."      </div>\n"
      .($close ? "[x]\n" : "");
  }

  function draw_cm_actions($content){
    return
       "    <div class='actions'>\n"
      .$content
      ."    </div>\n";
  }

  function draw_cm_context($ID='',$icon='',$content=''){
    return
       "  <div class='context'".($ID ? " id='".$ID."'" : '').">\n"
      .($icon ? $this->draw_cm_icon($icon) : "")
      .$content
      ."  </div>\n";
  }

  function draw_cm_context_noline($ID='',$icon='',$content=''){
    return
       "  <div class='context noline'".($ID ? " id='".$ID."'" : '').">\n"
      .($icon ? $this->draw_cm_icon($icon) : "")
      .$content
      ."  </div>\n";
  }

  function draw_cm_icon($what){
    $what = explode("|",$what);
    return
      "  <div class='icon'><img src='".BASE_PATH."img/spacer' class='icons' "
     ."style='width:".$what[0]."px;height:".$what[1]."px;background-position:-".$what[2]."px 0px;' "
     ."title=\"".$what[3]."\" alt=\"".$what[3]."\" /></div>\n";
  }

  function draw_div_sample($title,$content) {
    return
       "  <div class='cm_panel cm_sample'>\n"
      .$this->draw_cm_icon('11|11|2600|(i)')
      ."    <div class='cm_panel_content'>\n"
      ."      <div class='cm_panel_title'>".$title."</div>\n"
      ."      <div class='cm_panel_text'>".$content."</div>\n"
      ."    </div>\n"
      ."  </div>\n";
  }

  function draw_div_tip($context){
    switch ($context) {
      case "navbutton":
        $title =    "Tips for admins:";
        $content =
           "Don't right-click to open a page in a new tab:&nbsp;<br />"
          ."Instead, press [CTRL] and <i>left</i>-click.";
      break;
      case "report":
        $title =    "Tips for admins:";
        $content =
           "Don't right-click to open report in a new tab:&nbsp;<br />"
          ."Instead, press [CTRL] and <i>left</i>-click.";
      break;
      case "report_filters":
        $title =    "Tips for admins:";
        $content =
           "To create filters for reports, set up filter criteria and press 'Save...'.";
      break;
      default:
        $title =    "Tips for admins:";
        $content =
           "Don't right-click to open a link in a new tab:<br />"
          ."Instead, press [CTRL] and <i>left</i>-click."
          ."<img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/>"
          ."To copy text, select it and press [CTRL]+C";
      break;
    }
    return
       "  <div class='cm_panel cm_tip'>\n"
      .$this->draw_cm_icon('11|11|2600|(i)')
      ."    <div class='cm_panel_content'>\n"
      ."      <div class='cm_panel_title'>".$title."</div>\n"
      ."      <div class='cm_panel_text'>".$content."</div>\n"
      ."    </div>\n"
      ."  </div>\n";
  }

  function draw_JS($admin_level) {
    $this->admin_level = $admin_level;
    $cm = $this->get_HTML();
    $js =
       "function CM_HideContext() {\n"
      ."  \$J(['".implode("','",$this->_cm_types)."']).each(\n"
      ."    function(idx,id){\n"
      ."      \$J('#'+id)[0].style.display='none';\n"
      ."    }\n"
      ."  )\n"
      ."}\n"
      ."function CM_ContextShow(event){\n"
      ."  var btn, b_src, b_url, divContext, target, scrollLeft, scrollTop;\n"
      ."  CM_HideContext();\n"
      ."  if (_mouseOverContext){\n"
      ."    return false;\n"
      ."  }\n"
      ."  if (!_replaceContext) {\n"
      ."    return true;\n"
      ."  }\n"
      ."  if (!event) { event = window.event; }\n"
      ."  target = (event.target ? event.target : event.srcElement);\n"
      ."  scrollTop = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop;\n"
      ."  scrollLeft = document.body.scrollLeft ? document.body.scrollLeft : document.documentElement.scrollLeft;\n"
      ."  switch (_CM.type) {\n";
    foreach($this->_cm_js as $type=>$_js){
      $js.=
         "    case '".substr($type,3)."':\n"
        .$_js
        ."    break;\n";
    }
    $js.=
       "    default:\n"
      ."      return false;\n"
      ."    break;\n"
      ."  }\n"
      ."  divContext = geid('CM_'+_CM.type);\n"
      ."  // hide the menu first to avoid an 'up-then-over' visual effect\n"
      ."  divContext.style.display = 'none';\n"
      ."  divContext.style.left = event.clientX + scrollLeft + 'px';\n"
      ."  divContext.style.top = event.clientY + scrollTop + 'px';\n"
      ."  divContext.style.zIndex = 210;\n"
      ."  divContext.style.display = 'block';\n"
      ."  _replaceContext = false;\n"
      ."  return false;\n"
      ."}\n";
    return
       $js
      ."// Context Menu Version ".System::get_item_version('codebase').", Level ".$this->admin_level."\n"
      ."var cm_html = \n"
      ."\""
      .str_replace(
        array("\"","\\n","\n"),
        array("\\\"","\\\\n","\\n\"\n+\""),
        $cm
      )
      ."\";\n";
  }

  function get_HTML() {
    // this->admin_level: 3=MASTERADMIN, 2=SYSADMIN, 1=SYSEDITOR or SYSAPPROVER, 0=SYSLOGON
    $Obj = new Report;
    $reports_csv =
       "articles,block_layout,component,contact,content_block,events,gallery-albums,gallery-images,"
      ."job-postings,listtype,manage_refunds,navstyle,navbuttons,navbuttons_for_navsuite,navsuite,"
      ."news-items,pages,podcasts,podcast-albums,product,product_grouping,report,"
      ."report_columns,system,layouts,theme,theme_banner,theme_accent_1,user,"
      ."community,community_member,community_member.articles,community_member.events,"
      ."community_member.news-items,community_member.podcasts,sponsorship-plans-for-community,sponsors-for-sponsorship-plan";
    $this->_modules['TREB'] = in_array('treb',explode(",",str_replace(' ','',Base::get_modules_installed())));
    if ($this->_modules['TREB']){
      $reports_csv.=",module.treb.listings";
    }
    $Obj->get_popup_sizes_for_names($this->popup_size_arr,$reports_csv);
    return
       $this->_cm_article()
      .$this->_cm_community()
      .$this->_cm_community_member()
      .$this->_cm_community_member_article()
      .$this->_cm_community_member_event()
      .$this->_cm_community_member_news()
      .$this->_cm_community_member_podcast()
      .$this->_cm_community_sponsor()
      .$this->_cm_contact()
      .$this->_cm_content_block()
      .$this->_cm_event()
      .$this->_cm_gallery_album()
      .$this->_cm_gallery_image()
      .$this->_cm_job()
      .$this->_cm_navbutton()
      .$this->_cm_news()
      .$this->_cm_page()
      .$this->_cm_podcast()
      .$this->_cm_podcastalbum()
      .$this->_cm_product()
      .$this->_cm_report_column()
      .$this->_cm_report_filter()
      .$this->_cm_report()
      .$this->_cm_theme_accent()
      .$this->_cm_theme_banner()
      .$this->_cm_user()
      .($this->_modules['TREB'] ?       $this->_cm_module_treb_listing() : "")
      ;
  }

  private function register_js($type,$js){
    $this->_cm_js[$type] = $js;
  }

  private function register_type($type){
    $this->_cm_types[] = $type;
  }

  public function get_version(){
    return VERSION_CONTEXT_MENU;
  }
}
?>