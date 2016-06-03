<?php

class ConnectModel {

    protected $_db = False;

    public function __construct() 
    {

        $this->Response = new Response();
    }

    protected function connectDj()
    {
        $mysqli = new mysqli ($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASS'], $_SERVER['DB_NAME_DJ']);
        $db = new MysqliDb ($mysqli);
        return $db;
    }

    protected function connectCtrip()
    {
        $mysqli = new mysqli ($_SERVER['DB_HOST'], $_SERVER['DB_USER'], $_SERVER['DB_PASS'], $_SERVER['DB_NAME_CTRIP']);
        $db = new MysqliDb ($mysqli);
        return $db;
    }
}
