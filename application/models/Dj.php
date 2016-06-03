<?php 


class DjModel extends ConnectModel {

    const TBL_ZONE = 'zone';
    const TBL_MALL = 'mall';
    const TBL_MENU = 'menu';

    public function __construct()
    {
        $this->_db = $this->connectDj();
    }

    public function getZone($id=FALSE)
    {
        if ($id) {
            $this->_db->where('id', $id);
            $data = $this->_db->getOne(SELF::TBL_ZONE);
        }
        else
        {
            $data = $this->_db->get(SELF::TBL_ZONE, null);
        }
        if(!$data) {
            return FALSE;
        } 
        return $data;

    }

    public function getZoneByName($name)
    {
        $this->_db->where('name', $name);
        $data = $this->_db->getOne(SELF::TBL_ZONE);
        if(!$data) {
            return FALSE;
        } 
        return $data;

    }

    public function getMall($zoneId=FALSE)
    {
        if ($zoneId) {
            $this->_db->where('zone_id', $zoneId);
            $data = $this->_db->get(SELF::TBL_MALL, null, ['id as mall_id', 'name as mall_name', 'logo as mall_logo']);
            foreach($data as $key => $val) {
                if(strpos($val['mall_logo'], 'http:') === FALSE){
                    $data[$key]['mall_logo'] = '';
                }
            }
        }
        else
        {
            $data = $this->_db->get(SELF::TBL_MALL, null);
        }
        if(!$data) {
            return FALSE;
        } 
        return $data;

    }

    public function getMenu($mallId=FALSE)
    {
        $this->_db->where('mall_id', $mallId);
        $data = $this->_db->get(SELF::TBL_MENU, null, ['menu_name', 'menu_img', 'menu_price']);
        foreach($data as $key => $val) {
            $data[$key]['menu_img'] = trim($val['menu_img'], "'");
        }
        if(!$data) {
            return FALSE;
        } 
        return $data;

    }

}
