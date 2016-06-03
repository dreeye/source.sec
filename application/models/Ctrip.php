<?php 


class CtripModel extends ConnectModel {

    const TBL_CITY = 'city';
    const TBL_USER = 'user';

    public function __construct()
    {
        $this->_db = $this->connectCtrip();
    }

    public function getCity()
    {
        $data = $this->_db->get(SELF::TBL_CITY, null, ['name', 'code']);
        if(!$data) {
            return FALSE;
        } 
        return $data;

    }

    public function getUser()
    {
        if (!$user = $this->_db->getOne(SELF::TBL_USER)){
             error_log('get user data error '. $this->_db->getLastError());
             exit();
        }
        return $user;

    }

}
