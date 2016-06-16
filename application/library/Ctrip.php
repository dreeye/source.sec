<?php

require '../vendor/autoload.php';

use Goutte\Client;
use GuzzleHttp\Client as HTTP;
use GuzzleHttp\Cookie\FileCookieJar;

class Ctrip {

    private   $Client;
    private   $loginCookieCache = 'CtripLoginCookie';
    protected $Response;
    private   $ctripUrl = 'http://flights.ctrip.com/booking/';
    private   $loginUrl = 'https://www.corporatetravel.ctrip.com/crptravel/login?lang=zh-cn';
    private   $riskUrl = 'http://ct.ctrip.com/crptravel/Login/CheckRisk';
    private   $ctFlightUrl = 'http://ct.ctrip.com/flight/AjaxPages/GetFlightsInfo.aspx?FlightResLang=zh_cn';
    private   $cookieTmp = '/tmp/cookie_ctrip.txt';

    public function __construct()
    {
        $this->Client = new Client();
        $this->HTTP = new HTTP();
        $this->Response = new Response(); 
        $this->Cache = new Cache();
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
        $cookie = $this->checkRisk($user);
        $loginCookie = $this->login($user);
        $this->getCtFlight($user, $loginCookie);
        
    }

    private function getCtFlight($user)
    {
        $cookieJar = new FileCookieJar($this->cookieTmp, TRUE); 
        $response = $this->HTTP->request('POST', $this->ctFlightUrl, [
            'cookies' => $cookieJar,
            'form_params' => [
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
            ],
            
            'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:47.0) Gecko/20100101 Firefox/47.0',
                    'Referer' => 'http://ct.ctrip.com/flight/ShowFareFirst.aspx',
                    'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
            ]
        ]);
        if ( $response->getStatusCode() != 200) {
            echo $response->getStatusCode();exit();             
        }
echo '<pre>';print_r((string)$response->getBody());echo '</pre>';exit(); 
    }


    public function checkRisk($user)
    {
        $cookieJar = new FileCookieJar($this->cookieTmp, TRUE); 
        $response = $this->HTTP->request('POST', $this->riskUrl, [
            'cookies' => $cookieJar,
            'form_params' => [
                'uid' => $user['username'],
            ],
            
            'headers' => [
                    'referer' => 'http://ct.ctrip.com/',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0',
            ]
        ]);
        if ( $response->getStatusCode() != 200) {
            echo $response->getStatusCode();exit();             
        }
    }

    public function login($user)
    {
        $cookieJar = new FileCookieJar($this->cookieTmp, TRUE); 
        $response = $this->HTTP->request('POST', $this->loginUrl, [
            'cookies' => $cookieJar,
            'form_params' => [
                'backurl' => 'http://ct.ctrip.com/corptravel/zh-cn',
                'loginname' => $user['username'],
                'needVCode' => 'F',
                'passwd' => $user['password'],
                'vcode' => '',
                'fastneedVCode' => '',
                'hidbit' => '',
            ],
            
            'headers' => [
                    'referer' => 'http://ct.ctrip.com/',
            ]
        ]);
        if ( $response->getStatusCode() != 200) {
            echo $response->getStatusCode();exit();             
        }
    }

}