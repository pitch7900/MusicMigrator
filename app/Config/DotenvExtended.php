<?php

namespace App\Config;

/**
 * Description of DotenvExtended
 *
 * @author pierre
 */
class DotenvExtended extends \Dotenv\Dotenv {

    private $dir;

    public function __construct($dir) {
        parent::__construct($dir);
        $this->dir = $dir;
    }

    /**
     * Change a specific key in .env file
     * @param type $key
     * @param type $value
     */
    public function changeEnvironmentVariable($key, $value) {
        $path = $this->getFilePath();
        $old = "";
        if ($this->checkVariableIsDefined($key)) {
            $old = getenv($key);
        }
        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                            "$key=" . $old, "$key=" . $value, file_get_contents($path)
            ));
        }
    }

    /**
     * Return the file path for this .env file
     * @return type
     */
    public function getFilePath() {
        return $this->dir . '.env';
    }
    
    /**
     * Check if the key is defined
     * @param type $key
     * @return boolean
     */
    private function checkVariableIsDefined($key) {
        if (strlen(getenv($key)) > 0) {
            return true;
        } return false;
    }

}
