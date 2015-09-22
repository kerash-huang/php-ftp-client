<?php
/**
 * FTPClient
 * @author  Kerash <blog@kerash.tw>
 * @copyright MIT License
 * @date    2015/09/21
 * @version 1.0.0 建立基本功能
 */
namespace kerash\FTPClient;

require_once "FTP.class.php";

class FTPClient implements FTP {
    /**
     * FTPClient 最後錯誤訊息
     * @var string
     */
    private $FTPClientError = "";

    /**
     * 是否使用加密方式連線
     * @var boolean
     */
    private $is_security_connection = false;

    /**
     * FTP 連線埠
     * @var integer
     */
    private $default_ftp_port = 21;
    /**
     * 當前 FTP 連線物件
     * @var null
     */
    private $ftp_connection = null;

    /**
     * 目前連線狀態
     * @var boolean
     */
    private $is_connected = false;

    /**
     * 使用者登入狀態
     * @var boolean
     */
    private $is_logined   = false;

    /**
     * 當前 ftp server host
     * @var string
     */
    private $ftp_host = "";

    /**
     * 使用者當前路徑
     * @var string
     */
    private $_current = "";

    function __construct() {

    }

    function __destruct() {
        $this->quit();
    }

    private function _setSSL() {
        $this->is_security_connection = true;
        $this->default_ftp_port = 22;
    }

    private function _setPort($port) {
        $this->default_ftp_port = $port;
    }

    /**
     * 建立 FTP 連線
     * @param  string  $server
     * @param  boolean $use_ssl
     * @return null
     */
    function open($server, $use_ssl = false) {
        $url_param = parse_url($server);
        if(isset($url_param)) {
            if(isset($url_param["schema"]) && $url_param["schema"] == "sftp") {
                $this->_setSSL();
            }

            if(isset($url_param["path"])) {
                $this->ftp_host = $url_param["path"];
            } else if(isset($url_param["host"])) {
                $this->ftp_host = $url_param["host"];
            }

            if(isset($url_param["port"])) {
                $this->_setPort($url_param["port"]);
            }

        } else {
            $this->ftp_host = $server;
        }

        /**
         * Force to use ssl connection
         */
        if($use_ssl) {
            $this->is_security_connection = true;
        }

        if($this->is_security_connection) {
            $this->ftp_connection = ftp_ssl_connect($this->ftp_host, $this->default_ftp_port);
        } else {
            $this->ftp_connection = ftp_connect($this->ftp_host, $this->default_ftp_port);
        }

        if($this->ftp_connection !== null) {
            $this->is_connected = true;
        }
    }

    /**
     * 登入 FTP
     * @param  string $user
     * @param  string $pass
     * @return bool
     */
    function login($user = "anonymouse", $pass = "") {
        try {
            $success = @ftp_login($this->ftp_connection, $user, $pass);
        } catch (Exception $ftp_login_fail) {
            return false;
        }
        if($success) {
            $this->is_logined = true;
            $this->_current = $this->_pwd();
            return true;
        } else {
            return false;
        }
    }

    function _get($file, $target) {

    }

    function _put($file, $target) {

    }

    /**
     * 建立目錄資料夾
     * @param  string $name
     * @return bool
     */
    function _mkdir($name = ".") {
        $tmp_current = $this->_current;
        $check_exist = $this->_chdir($name);
        if($check_exist) {
            $this->_chdir($tmp_current);
            $this->FTPClientError = "Can't create directory: File exists.";
            return false;
        }
        $pathlist = explode("/", $name);
        if( count($pathlist) >0 ) {
            // 先 reverse 後在 pop 出來路徑
            $pathlist = array_reverse($pathlist);
            $q = 0;
            while( ($directoryName = array_pop($pathlist)) !==NULL ) {
                // 假設是最後一層的時候，代表他是要產生的目錄
                if(count($pathlist)==0) {
                    if( !ftp_mkdir($this->ftp_connection, $directoryName) ) {
                        $this->FTPClientError = "Can't create directory: Create directory failed.";
                    }
                    break;
                }
                // 假如目錄有不存在就回應建立錯誤
                if( !$this->_chdir($directoryName) ) {
                    $this->FTPClientError = "Can't create directory: No such file or directory";
                    break;
                }
            }
            $this->_chdir($tmp_current);
        } else {
            return false;
        }
        return true;
    }

    /**
     * 返回上一層
     * @return bool
     */
    function _cdup() {
        if( ftp_cdup($this->ftp_connection) ) {
            $this->_current = $this->_pwd();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 切換目錄
     * @param  string $folder
     * @return bool
     */
    function _chdir($folder) {
        if(@ftp_chdir($this->ftp_connection, $folder)) {
            $this->_current = $this->_pwd();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 切換目錄， alias of _chdir
     * @param  string $folder
     * @return bool
     */
    function _cd($folder) {
        if($this->_chdir($folder)) {
            return true;
        } else {
            $this->FTPClientError = "Can't change directory to {$folder}: No such file or directory.";
            return false;
        }
    }

    /**
     * 列出當前路徑所有檔案
     * @return array
     */
    function _ls() {
        return ftp_nlist($this->ftp_connection, $this->_current);
    }

    /**
     * 當前目錄路徑
     * @return [type]
     */
    function _pwd() {
        $this->_current = ftp_pwd($this->ftp_connection);
        return $this->_current;
    }

    /**
     * 登出
     * @return [type]
     */
    function quit() {
        if($this->ftp_connection!==null) {
            ftp_close($this->ftp_connection);
            $this->ftp_connection = null;
        }
        $this->is_logined   = false;
        $this->is_connected = false;
        $this->_current = "";
    }

    function __call($funcName, $args) {
        if(!$this->is_connected && !$this->is_logined) {
            throw new \Exception("You're not login ftp yet.");
        }
        if(method_exists($this, "_{$funcName}")) {
            return call_user_method("_{$funcName}", $this, count($args)==0? null : $args[0]);
        } else {
            throw new \Exception("Command [{$funcName}] not found.");
        }
    }

    /**
     * Not standard ftp method
     */
    function is_connected() {
        return $this->is_connected;
    }

    function is_logined() {
        return $this->is_logined;
    }

    function get_ftpclient_error() {
        return $this->FTPClientError;
    }
}
