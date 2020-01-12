<?php

namespace App\Utils;

/**
 * Description of Logs
 *
 * @author pierre
 */
class Logs {

    public static  $MODE_INTERACTIVE = "interactive";
    public static  $MODE_FILE = "logfile";
    public static  $MODE_BOTH ="both";
    
    public function __construct() {
      
    }

    /**
     * Logs the informations passed as $data
     * The mode can be :
     *  "interactive" : return to stdout
     *  "logfile" : write the data to a log file defined by $logfile
     *  "both" : display to stdout and write to $lofile
     * @param string $loglevel
     * @param string $mode
     * @param string $file
     * @param string $data
     */
    public function write($loglevel, $mode, $file, $data) {
        $backtrace = debug_backtrace();
        $logfile = __DIR__."/../../logs/" . $file;
        //$caller returns the File(function) that is calling this function
        $caller = $backtrace[count($backtrace) - 1]['file'] . "(" . $backtrace[count($backtrace) - 1]['function'] . ")";
        $data .= "\n";

        $date = date("Y-m-d H:i:s");
        $informations = $date . " [" . $loglevel . "] " . $caller;
        switch ($mode) {
            case $this::$MODE_INTERACTIVE :
                echo $informations . " " . $data;
                break;
            case $this::$MODE_FILE :
                $fp = fopen($logfile, 'a+');
                fwrite($fp, $informations . " " . $data);
                fclose($fp);
                break;
            case $this::$MODE_BOTH :
                echo $informations . " " . $data;
                $fp = fopen($logfile, 'a+');
                fwrite($fp, $informations . " " . $data);
                fclose($fp);
                break;
            default:
                echo $informations . " " . $data;
                break;
        }
    }



}
