<?php

class ModelDj {

    protected $_db = False;

    public function __construct() 
    {
        $mysqli = new mysqli ($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASS'], $_SERVER['DB_NAME_DJ']);
        $this->_db = new MysqliDb ($mysqli);

        $this->Response = new Response();
    }

}
