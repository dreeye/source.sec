<?php

require '../vendor/autoload.php';

use Goutte\Client;
use GuzzleHttp\Client as HTTP;
use GuzzleHttp\Plugin\Cookie\CookiePlugin;
use GuzzleHttp\Plugin\Cookie\CookieJar\FileCookieJar;

class Trip {

    private   $Client;
    private   $loginCookieCache = 'CtripLoginCookie';
    protected $Response;
    private   $ctripUrl = 'http://flights.ctrip.com/booking/';
    private   $loginUrl = 'https://www.corporatetravel.ctrip.com/crptravel/login?lang=zh-cn';
    private   $riskUrl = 'http://ct.ctrip.com/crptravel/Login/CheckRisk';
    private   $ctUrl = 'http://ct.ctrip.com/corptravel/zh-cn';
    private   $homeUrl = 'http://ct.ctrip.com/';
    private   $ctFlightUrl = 'http://ct.ctrip.com/flight/AjaxPages/GetFlightsInfo.aspx?FlightResLang=zh_cn';
    private   $cookieTmp = '/tmp/cookie_ctrip';

    public function __construct()
    {
        $this->Client = new Client();
        $this->HTTP = new HTTP();
        $this->Response = new Response(); 
        $this->Cache = new Cache();
        $this->Curl = new Curl();
    }

    public function air($from, $to, $date)
    {
        $ctripUrl = $this->ctripUrl.$from.'-'.$to.'-day-1.html?DDate1='.$date;
        $crawler = $this->Client->request('GET', $ctripUrl); 
        $url = $crawler->filter('body script')->text(); 
                if (preg_match("/\"http:\/\/.*?\"/i", $url, $matches)) {
                    $url = trim($matches[0], '"');
                }
                else
                {
                    die('url error!');
                }
        $response = $this->Client->request('GET', $url, [
            'headers' => [

                    'referer' => $ctripUrl,
            ]
        
            ]); 
        $json = json_decode($response->text(),true);
        $data = $json['fis'];
        $res = []; 
        foreach ($data as $key => $i) {
              // 到达的机场
              $airport_to = $i['apc'].$i['abid'];
              $res[$key]['airport_to'] = $i['apc'].$i['abid'];
              $res[$key]['airport_to_zh'] = $json['apb'][$airport_to] ?? '';
              // 起飞的机场
              $airport_from = $i['dpc'].$i['dbid'];
              $res[$key]['airport_from'] = $i['dpc'].$i['dbid'];
              $res[$key]['airport_from_zh'] = $json['apb'][$airport_from] ?? '';
              // 出发的城市
              $res[$key]['dcc'] = $i['dcc'] ?? '';
              $res[$key]['acc'] = $i['acc'] ?? '';
        
              // 到达时间
              $res[$key]['dt'] = $i['dt'] ?? '';

              // 起飞时间
              $res[$key]['at'] = $i['at'] ?? '';
              // 历史准点率
              $res[$key]['pr'] = $i['pr'] ?? '';
              // 民航基金
              $res[$key]['tax'] = $i['tax'] ?? '';
              // 航空公司
              $companyKey = $i['alc'] ?? '';
              $res[$key]['company_name'] = $json['als'][$companyKey] ?? '';
            
              // 飞机型号
              $res[$key]['fn'] = $i['fn'] ?? '';
              // 价格
              $res[$key]['price'] = $i['lp'] ?? '';
              // 飞机大小
              $s = ($i['cf']['s'] == 'L') ? '大' : ( ($i['cf']['s'] == 'M') ? '中' : '小' );
              // 机型
              $res[$key]['cf'] = $i['cf']['c'].'('.$s.')';
        }
        return $res;


    }

    public function discount($from, $to, $date, $user)
    {
        /*$fileCache = $this->Cache->file();
        if ($loginCookie = $fileCache->get($this->loginCookieCache)) {
            $this->getCtFlight($user, $loginCookie);
            //$this->site($loginCookie);
        }*/
        // 通过登录重新获取cookie
        //$svcCookie = $this->home();
        $this->checkRisk($user);
        $this->login($user);
        $this->getCtFlight($user);
            //$this->site($loginCookie);
        
    }

    private function getCtFlight($user)
    {
        $this->Curl->setReferrer('http://ct.ctrip.com/flight/ShowFareFirst.aspx');
        $this->Curl->setopt(CURLOPT_COOKIEFILE, $this->cookieTmp);
        $this->Curl->setopt(CURLOPT_COOKIEJAR, $this->cookieTmp);
        $this->Curl->setHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
        $this->Curl->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0');
        $this->Curl->post($this->ctFlightUrl, [
                'ACity1' => 'SHA',
                'ACity2' => '',
                'ACity3' => '',
                'ACity4' => '',
                'ACity5' => '',
                'ACity6' => '',
                'Airline' => '',
                'BookingPolicy' => 'C',
                'BookingType' => 'self',
                'ClassType' => '',
                'CorpCardType' => 'C',
                'CorpPayType' => 'pub',
                'DCity1' => 'BJS',
                'DCity2' => '',
                'DCity3' => '',
                'DCity4' => '',
                'DCity5' => '',
                'DCity6' => '',
                'DDate1' => '2016-06-18',
                'DDate2' => '',
                'FlightNumber' => '2',
                'FlightSearchType' => 'S',
                'IsCanBookingForNOTEmp' => 'T',
                'IsRunFlt15' => 'False',
                'NextUrl' => '',
                'PassengerQuantity' => '1',
                'PassengerType' => 'ADU',
                'SendTicketCity' => '',
                'UseTRFlag' => '0',
                'XDDate' => '',
                'acity' => '',
                'cityend' => '上海(SHA)',
                'citystart' => '北京(BJS)',
                'currentLang' => 'zh_cn',
                'flight_company' => '',
                'isIntl' => 'T',
                'reserver' => 'self',
                'txtAirline' => '',



                /*'PassengerIDList' => $user['username'],
                'PassengerQuantity' => '1',
                'PassengerType' => 'ADU',
                'RouteIndex' => '1',
                'SearchType' => 'S',
                'SendTicketCity' => '北京',
                'corp_PassList' => $user['username'].$user['name'],
                'corp_PolicyID' => $user['username'],
                'hidTransitProduct' => 'false',*/
            
        ]);
echo '<pre>';print_r($this->Curl);echo '</pre>';exit(); 
        if ($this->Curl->error) {
            echo $this->Curl->error_code;exit();
        } else {
        }
    }

    private function site($loginCookie)
    {
        $client = new Client();
        $crawler = $client->request('GET', $this->ctUrl, [
            'headers' => [
                    'referer' => 'http://ct.ctrip.com/corptravel/zh-cn',
                    'cookie' => $loginCookie
            ]
        ]);
        $nodes = $crawler->filter(".cui_toolkit_login");
        if($nodes->count()) {

            echo '<pre>';print_r($nodes->text());echo '</pre>';exit(); 
        }
echo '<pre>';print_r(12);echo '</pre>';exit(); 
    }

    public function checkRisk($user)
    {
        $this->Curl->setReferrer('http://ct.ctrip.com/');
        $this->Curl->setopt(CURLOPT_COOKIEFILE, $this->cookieTmp);
        $this->Curl->setopt(CURLOPT_COOKIEJAR, $this->cookieTmp);
        $this->Curl->post($this->riskUrl, [
                'uid' => $user['username'],
        ]);
        if ($this->Curl->error) {
            echo $this->Curl->error_code;exit();
        }
        

    }
    public function home()
    {
        $this->Curl->setReferrer('http://ct.ctrip.com/crptravel/logout?url=http://ct.ctrip.com/crptravel/zh-cn');
        $this->Curl->get($this->homeUrl);
        if ($this->Curl->error) {
            echo $this->Curl->error_code;exit();
        }

    }

    public function login($user)
    {
        $this->Curl->setReferrer('http://ct.ctrip.com/');
        $this->Curl->setopt(CURLOPT_COOKIEFILE, $this->cookieTmp);
        $this->Curl->setopt(CURLOPT_COOKIEJAR, $this->cookieTmp);
        $this->Curl->post($this->loginUrl, [
                'backurl' => 'http://ct.ctrip.com/corptravel/zh-cn',
                'loginname' => $user['username'],
                'needVCode' => 'F',
                'passwd' => $user['password'],
                'vcode' => '',
                'fastneedVCode' => '',
                'hidbit' => '',
        ]);
        if ($this->Curl->error) {
            echo $this->Curl->error_code;exit();
        }

    }


}
