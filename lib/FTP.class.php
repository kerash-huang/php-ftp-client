<?php
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
    public function _chdir();
    public function _cd();
    public function _cdup();
    
}