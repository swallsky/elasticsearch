<?php
/**
 * es相关的配置
 */
namespace SwaSky\ElasticSearch;

class Config
{
    /**
     * 返回当前包目录
     * @return string
     */
    public static function packagePath()
    {
        return dirname(__DIR__);
    }

    /**
     * 返回项目目录
     * @return string
     */
    public static function vendorPath()
    {
        return dirname(dirname(dirname(Config::packagePath())));
    }

    /**
     * 返回配置信息
     * @return array|mixed
     */
    public static function getConfig()
    {
        $vendorconfig = Config::vendorPath().'/config/es.php'; //项目配置文件
        if(file_exists($vendorconfig)){
            return include($vendorconfig);
        }
        $packconfig = Config::packagePath().'/config/es.php'; //当前包配置文件
        if(file_exists($packconfig)){
            return include($packconfig);
        }
        return [];
    }
}