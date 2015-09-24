<?php
/**
 * FTP 基本連線使用方式
 */
require_once "../lib/FTPClient.class.php";
require_once "ftp.config.php";

define("_BREAKLINE_" , "<br>");

$file = $_FILES["pf"];
if(!$file or $file["error"]>0) {
    header("Location: upload.html#error");die();
}

$file_path = $file["tmp_name"];
$file_name = $file["name"];

$FTP = new kerash\FTPClient\FTPClient();

try {
    $FTP->open($FTP_Server);
    if($FTP->is_connected()) {
        $success = $FTP->login($FTP_User, $FTP_Pass);
        if($success) {
            // if you want to upload into specified folder,please use ->cd jump first.
            // $FTP->cd("test");
            $result = $FTP->put($file_path, $file_name);
            if($result) {
                echo "Success";
            } else {
                echo "Upload Failed";
            }
        } else {
            echo "Login Failed";
        }
    } else {
        echo "Couldn't connect to FTP Server : ". $FTP_Server;
    }
} catch (Exception $ftp_error) {
    echo $ftp_error->getMessage();
}
$FTP->quit();