<?php
/*
Version History:
  1.0.0 (2015-02-01)
    1) Initial release - split from RSS class file
*/

class RSS_Help
{
    const VERSION = '1.0.0';
    public function _get_object_name()
    {
        return "Help";
    }
    public function plural()
    {
        return;
    }

    public function get_version()
    {
        return self::VERSION;
    }
}
