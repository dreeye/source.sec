<?php
require '../vendor/autoload.php';
use Dreeye\Helper\String_helper;
use Dreeye\Helper\Geo_helper;
use Dreeye\Helper\Validate_helper;
use Dreeye\Helper\Time_helper;

class DjController extends Core
{

    public function init()
    {
        parent::init();
        $this->DjMod = new DjModel();
        $this->Validate_helper = new Validate_helper();
        
    }

    public function get_locationAction()
    {
        $locationX = ( $this->_post['location_x'] ?? $this->Response->error('40016')) ? : $this->Response->error('40019');
        $locationY = ( $this->_post['location_y'] ?? $this->Response->error('40016')) ? : $this->Response->error('40020');
        if (!$this->Validate_helper->isNumeric($locationX) || !$this->Validate_helper->isNumeric($locationY)) {
            $this->Response->error('40022');
        }
        $postLocation = $locationX.' '.$locationY;
        $json = file_get_contents('location.json');
        $location = json_decode($json, TRUE);
        $Geo_helper = new Geo_helper();
        foreach($location as $val) {
            foreach ($val['points'] as $po) {
                $polygon[] = $po[0].' '.$po[1]; 
            }   
            if ($Geo_helper->pointInPolygon($postLocation, $polygon) != 'outside') {
                $zoneName = $val['name'];
                $zoneData = $this->DjMod->getZoneByName($zoneName);
                $mallData = $this->DjMod->getMall($zoneData['id']);
                $data = [
                   'zone_id'=>$zoneData['id'], 
                   'zone_name'=>$zoneData['name'], 
                   'mall_data'=>$mallData, 
                ];
                $this->Response->success($data);
                break;
            }
            $polygon = []; 
        }
        $this->Response->error('40021');
    }

    public function get_menuAction()
    {
        $mallId = ( $this->_post['mall_id'] ?? $this->Response->error('40016')) ? : $this->Response->error('40019');
        if (!$this->Validate_helper->isInteger($mallId)) $this->Response->error('40023');
        if ($menuData = $this->DjMod->getMenu($mallId)) {
            $menuData = ['menu_data'=>$menuData];
            $this->Response->success($menuData);
        }
        $this->Response->success([]);
    }


}
