<?php
/**
 * FTPClient
 * @author  Kerash <blog@kerash.tw>
 * @copyright MIT License
 * @date    2015/09/21
 * @version 1.0.0 建立基本功能
 */
namespace kerash\FTPClient;

interface FTP {
    public function open($server);
    public function login($user,$pass);
    public function quit();

    public function _put($file, $target);
    public function _get($file, $target);
    
    public function _mkdir($name);
    public function _pwd();

    public function _ls();
    public function _chdir($folder);
    public function _cd($folder);
    public function _cdup();
    
}