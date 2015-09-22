<?php
/**
 * FTP 基本連線使用方式
 */
require_once "../lib/FTPClient.class.php";
require_once "ftp.config.php";

define("_BREAKLINE_" , "<br>");

$FTP = new kerash\FTPClient\FTPClient();

try {
    $FTP->open($FTP_Server);
    if($FTP->is_connected()) {
        $success = $FTP->login($FTP_User, $FTP_Pass);
        if($success) {
            echo "Where am I : ".$FTP->pwd() ._BREAKLINE_;
        } else {
            echo "Login failed";
        }
    } else {
        echo "Couldn't connect to FTP Server : ". $FTP_Server;
    }
} catch (Exception $ftp_error) {
    echo $ftp_error->getMessage();
}
$FTP->quit();