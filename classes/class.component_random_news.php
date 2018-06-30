<?php
define("VERSION_COMPONENT_RANDOM_NEWS", "1.0.1");
/*
Version History:
  1.0.1 (2015-01-28)
    1) Now has parameters to show content and title and CM editing of news item currently being displayed
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Random_News extends Component_Base
{
    public function __construct()
    {
        $this->_ident =             "random_news";
        $this->_parameter_spec = array(
            'category_csv' =>
                array('match' => '',            'default'=>'',      'hint'=>'csv list of categories to pick from'),
            'content_show' =>
                array('match' => 'enum|0,1',    'default' =>'1',    'hint'=>'0|1'),
            'title_show' =>
                array('match' => 'enum|0,1',    'default' =>'0',    'hint'=>'0|1')
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $this->_setup($instance, $args, $disable_params);
        $this->_draw();
        return $this->_html;

    }


    protected function _draw()
    {
        $this->_draw_control_panel(true);
        $Obj_News_Item =    new News_Item;
        $record =           $Obj_News_Item->get_random_record($this->_cp['category_csv']);
        if ($record) {
            $isMASTERADMIN =    get_person_permission("MASTERADMIN");
            $isSYSADMIN =       get_person_permission("SYSADMIN");
            $isSYSEDITOR =      get_person_permission("SYSEDITOR");
            $canEdit =          ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
            $this->_html.=
               "<div"
              .($canEdit && isset($record['ID']) && $record['ID'] && ($record['systemID']==SYS_ID || $isMASTERADMIN)?
                  " onmouseover=\""
                 ."if(!CM_visible('CM_news_item')) {"
                 ."this.style.backgroundColor='"
                 .($record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
                 ."';"
                 ."_CM.type='news_item';"
                 ."_CM.ID=".$record['ID'].";"
                 ."_CM_text[0]='&quot;".str_replace(array("'", "\""), array('', '&quot;'), $record['title'])."&quot;';"
                 ."_CM_text[1]=_CM_text[0];}\" "
                 ." onmouseout=\"this.style.backgroundColor='';_CM.type='';\""
               :
                 ""
              )
              .">\n";
            if ($this->_cp['title_show']) {
                $this->_html.= "<div class='".$this->_safe_ID."_title'>".$record['title']."</div>\n";
            }
            if ($this->_cp['content_show']) {
                $this->_html.= "<div class='".$this->_safe_ID."_content'>".$record['content']."</div>\n";
            }
            $this->_html.= "</div>";
        }
    }

    public function get_version()
    {
        return VERSION_COMPONENT_RANDOM_NEWS;
    }
}
