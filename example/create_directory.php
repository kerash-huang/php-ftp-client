<?php
/**
 * 用於建立目錄的範例
 */
require_once "../lib/FTPClient.class.php";
require_once "ftp.config.php";

define("_BREAKLINE_" , "<br>");

$FTP = new kerash\FTPClient\FTPClient();

try {
    $FTP->open($FTP_Server);
    if($FTP->is_connnected()) {
        $success = $FTP->login($FTP_User, $FTP_Pass);
        if($success) {
            echo "Where am I : ".$FTP->pwd() ._BREAKLINE_;

            echo "File List <hr>". _BREAKLINE_;
            $RemoteFileList = $FTP->ls();
            foreach($RemoteFileList as $filename) {
                echo $filename. _BREAKLINE_;
            }
            
            if(!$FTP->mkdir("test")) {
                echo $FTP->get_ftpclient_error() . _BREAKLINE_;
            }

            if(!$FTP->mkdir("test2")) {
                echo $FTP->get_ftpclient_error() . _BREAKLINE_;
            }

            if(!$FTP->mkdir("test/testfolder")) {
                echo $FTP->get_ftpclient_error() . _BREAKLINE_;
            }

            echo "Where am I: ".$FTP->pwd() . _BREAKLINE_;
            
            $FTP->cd("test");

            echo "File List ><hr>";
            $RemoteFileList = $FTP->ls();
            foreach($RemoteFileList as $filename) {
                echo $filename . _BREAKLINE_;
            }
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




        