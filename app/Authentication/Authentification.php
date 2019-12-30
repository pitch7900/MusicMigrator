<?php

namespace App\Authentication;

use App\Database\sql as sql;
use App\Auth\Sslauth as ssl;
use App\Utils\Logs as logs;

class Authentification {

    STATIC PUBLIC $ADMINISTRATOR = 1;
    STATIC PUBLIC $EDITOR = 2;
    STATIC PUBLIC $READER = 3;
    PRIVATE $userid;
    private $sql;
    private $ssl;

    /**
     * Class constructor
     * Commented section is for CLI debugging
     */
    public function __construct() {
        $this->sql = new sql();
        $this->ssl = new ssl();
//        $log = new logs();

        if (isset($_SESSION['user'])) {
//            $log->write("Warning", "logfile", "debug.log", "User ID passe for this session is : " . $_SESSION['user']);

            $this->userid = $_SESSION['user'];
        } else {
//            $log->write("Warning", "logfile", "debug.log", "User ID passe for this session is : " . $this->ssl->get_userid());

            $this->userid = $this->ssl->get_userid();
        }
    }

    /**
     * 
     * Class destructor
     */
    public function __destruct() {
        $this->sql = null;
    }

    /**
     * Return the roleid based on rolename
     * if no match return a default "reader" only role
     * @global int $SSL_ADMINISTRATOR
     * @global int $SSL_EDITOR
     * @global int $SSL_READER
     * @param type $rolename
     * @return int
     */
    public function get_roledid_from_rolename($rolename) {
        switch ($rolename) {
            case "administrator" :
                $roleid = $this::$ADMINISTRATOR;
                break;
            case "editor" :
                $roleid = $this::$EDITOR;
                break;
            case "reader" :
                $roleid = $this::$READER;
                break;
            default:
                $roleid = $this::$READER;
                break;
        }
        return $roleid;
    }

    /**
     * Return current user roleid
     * @return integer
     */
    public function get_roleid() {
        return $this->sql->get_user_permissions($this->userid);
    }

    /**
     * Return an array of array of [roleid,rolename] for all possible roles
     * @return array
     */
    public function get_roles() {
        $result = $this->sql->query("SELECT * from roles");
        $roles = array();
        while ($row = $this->sql->fetch_array($result)) {
            array_push($roles, array('id' => $row["id"], 'name' => $row['name']));
        }
        return $roles;
    }

    /**
     * Return current user rolename
     * rolename can be : 
     *  - administrator
     *  - editor
     *  - reader
     * @return string
     */
    public function get_rolename() {

        $roleid = $this->sql->get_user_permissions($this->userid);

        switch ($roleid) {
            case $this::$ADMINISTRATOR :
                $rolename = "administrator";
                break;
            case $this::$EDITOR :
                $rolename = "editor";
                break;
            case $this::$READER :
                $rolename = "reader";
                break;
            default:
                $rolename = "reader";
                break;
        }
        return $rolename;
    }

    /**
     * Return current CA
     * @return string
     */
    public function get_CA() {
        return $this->ssl->get_CA();
    }

    /**
     * Return SSL common Name
     * @return string
     */
    public function get_CommonName() {
        return $this->ssl->get_CommonName();
    }

    /**
     * Return true if current user is authentificateded
     * @return boolean
     */
    public function is_SSLClientAuth() {
        return $this->ssl->is_SSLClientAuth();
    }

    /**
     * Return current user certificate
     * @return string
     */
    public function get_Certificate() {
        return $this->ssl->get_Certificate();
    }

    /**
     * Change the order (equivalent to "tac" under linux)
     * of the certificate CN and replace all separators from / to ,
     * @param type $cn
     * @return string
     */
    public function reorder_cn($cn) {
        return $this->ssl->reorder_cn($cn);
    }

    /**
     * Return true if current user has administrator privileges
     * @return boolean
     */
    public function is_administrator() {
        return $this->sql->get_user_permissions($this->userid) == $this::$ADMINISTRATOR;
    }

    /**
     * Return true if current user has editor privileges
     * @return boolean
     */
    public function is_editor() {
        return $this->sql->get_user_permissions($this->userid) <= $this::$EDITOR;
    }

    /**
     * Return current userid
     * @return integer
     */
    public function get_userid() {
        return $this->userid;
    }

    /**
     * Return true if current user has editor privileges
     * @return boolean
     */
    public function is_reader() {
        return $this->sql->get_user_permissions($this->userid) <= $this::$READER;
    }

    /**
     * return current user name
     * @return string
     */
    public function username() {
        return $this->sql->get_user_namebyid($this->userid);
    }

    /**
     * Renice a certificate for proper html display
     * @param type $cert
     * @return string
     */
    public static function renice_cert($cert) {
        return \App\Auth\Sslauth::renice_cert($cert);
    }

    /**
     * Return true if current user wants adult content
     * @return boolean
     */
    public function wants_adultcontent() {

        return $this->sql->get_user_wants_adult_content($this->userid);
    }

    /**
     * Return true if current user is allowed to display adult content
     * @return type
     */
    public function adult_content_allowed() {
        return $this->sql->get_user_allow_adult_content($this->userid);
    }

}
