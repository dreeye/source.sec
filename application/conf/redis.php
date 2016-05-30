<?php

$redis_config = [

    'host'   => $_SERVER['RD_HOST'],
    'port'   => $_SERVER['RD_PORT'],
    'password' => $_SERVER['RD_PASS'],

];

if ($_SERVER['DB_ENV'] == 'development' ) 
{
    $redis_prefix = [

        'user' => 'dev_user_',
        'mobile' => 'dev_mobile_',
    ];

}
else
{
    $redis_prefix = [

        'user' => 'user_',
        'mobile' => 'mobile_',
    ];

}






