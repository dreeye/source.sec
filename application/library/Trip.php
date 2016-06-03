<?php

require '../vendor/autoload.php';

use Goutte\Client;
use GuzzleHttp\Client as HTTP;

class Trip {

    private   $Client;
    protected $Response;
    private   $ctripUrl = 'http://flights.ctrip.com/booking/';
    private   $loginUrl = 'https://www.corporatetravel.ctrip.com/crptravel/login?lang=zh-cn';
    private   $riskUrl = 'http://ct.ctrip.com/crptravel/Login/CheckRisk';

    public function __construct()
    {
        $this->Client = new Client();
        $this->HTTP = new HTTP();
        $this->Response = new Response(); 
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
        $cookie = $this->checkRisk($user);
        $loginCookie = $this->login($user, $cookie);
    }
    public function checkRisk($user)
    {
        
        $response = $this->HTTP->request('POST', $this->riskUrl, [
            'form_params' => [
                'uid' => $user['username'],
            ],
            
            'headers' => [
                    'referer' => 'http://ct.ctrip.com/',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:45.0) Gecko/20100101 Firefox/45.0',
            ]
        ]);
         return implode(' ', $response->getHeader('Set-Cookie'));
        // return $response->getHeader('Set-Cookie');

    }
    public function login($user, $cookie)
    {
        $response = $this->HTTP->request('POST', $this->loginUrl, [
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
                    'cookie' => $cookie
            ]
        ]);
         return implode(' ', $response->getHeader('Set-Cookie'));

    }

}
