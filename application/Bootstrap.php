<?php

class Bootstrap extends Yaf_Bootstrap_Abstract {

    // 加载本地library, error
    public function _initLoader()
    {
        # Yaf_Loader::import(APP_PATH. "/application/conf/redis.php");
        # Yaf_Registry::set('RedisConf', $redis_config);
        # Yaf_Registry::set('RP', $redis_prefix);
        # Yaf_Loader::import(APP_PATH. "/application/conf/error.php");
        # $Response = new Response();
        /*$Curl = new Curl();
        $Common = new Common();
        Yaf_Registry::set('Curl', $Curl);
        Yaf_Registry::set('Common', $Common);*/
    }

    // 关闭了自动渲染模板
    public function _initConfig(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->autoRender(FALSE);
    }


}
