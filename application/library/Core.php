<?php

require '../vendor/autoload.php';

class Core extends Yaf_Controller_Abstract {

    protected $_post;

    protected $Response;

    protected function init()
    {
        $this->Response = new Response(); 
        $this->_post = json_decode(file_get_contents('php://input'), TRUE); 
    }



}
