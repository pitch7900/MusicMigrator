<?php

namespace App\Authentication;

use App\Database\sql as sql;
use App\Authentication\Authentification as Authentication;

/**
 * Description of Users
 *
 * @author pierre
 */
class Users {

    private $sql;
    private $authentication;

    public function __construct() {

        $this->sql = new sql();
        $this->authentication = new Authentication();
    }

    public function getlist() {
        
        $array = $this->sql->get_users_list();


        return $array;
    }

    static function get_username($userid) {
        $sql = new sql();
        return $sql->get_user_namebyid($userid);
    }

    public function set_password($userid,$password) {
        $query="UPDATE users set password='".password_hash($password, PASSWORD_DEFAULT). "'WHERE id=$userid;";
        $this->sql->query($query);
    }
    
    public function set_username($userid,$username) {
        $query="UPDATE users set username='$username' WHERE id=$userid;";
        $this->sql->query($query);
    }
}
