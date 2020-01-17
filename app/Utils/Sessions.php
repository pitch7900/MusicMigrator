<?php

namespace App\Utils;

/**
 * Description of Sessions
 * from https://zend18.zendesk.com/hc/en-us/articles/205880128-Lock-Picking-PHP-Sessions
 * @author pierre
 */
class Sessions {

    public static function session_get() {
        global $SessionData;
        session_start();
        session_write_close();
        $SessionData = $_SESSION;
    }

    public static function session_set() {
        global $SessionData;
        session_start();
        $_SESSION = $SessionData;
        session_write_close();
    }

}
