<?php
require '../vendor/autoload.php';
use Dreeye\Helper\String_helper;
use Dreeye\Helper\Geo_helper;
use Dreeye\Helper\Validate_helper;

class CtripController extends Core
{

    public function init()
    {
        parent::init();
        $this->Trip = new Trip();
        $this->Ctrip = new Ctrip();
        $this->Validate_helper = new Validate_helper();
    }

    public function get_flightAction()
    {
        $from = ( $this->_post['from'] ?? $this->Response->error('40016')) ? : $this->Response->error('40019');
        $to = ( $this->_post['to'] ?? $this->Response->error('40016') ) ? : $this->Response->error('40020');
        // $mode = ( $this->_post['mode'] ?? 'normal' ) ? : $this->Response->error('40020');
        $date = ( $this->_post['date'] ?? date('Y-m-d')) ? : date('Y-m-d');
        if ( ! $this->Validate_helper->isDate($date, 'Y-m-d') ) $this->Response->error('40022');

        //if ($mode == 'discount') {  
            // 需要登陆,获取一个账号密码
            $ctripMod = new CtripModel();
            $userData = $ctripMod->getUser();
            $flightData = $this->Ctrip->discount($from, $to, $date, $userData);
            if ($flightData) {
                $flightData = [
                                'flight_data'=>$flightData
                              ]; 
            } else {
                $flightData = [ 'flight_data'=>$this->Trip->air($from, $to, $date)]; 

            }
        // } else {
       // }
        if ($flightData) {
            $this->Response->success($flightData);
        }
        
    }

    public function get_cityAction()
    {
        $cityMod = new CtripModel();
        $cityData = $cityMod->getCity();
        $this->Response->success($cityData);
    }

}
