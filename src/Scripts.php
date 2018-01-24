<?php
/**
 * 包安装时需要处理的脚本
 */
namespace SwaSky\ElasticSearch;

use Composer\Script\Event;

class Scripts
{
    /**
     * 更新包时激活
     * @param Event $event
     */
    public static function postUpdate(Event $event)
    {
        $composer = $event->getComposer();
        // do stuff
    }

    public static function install(Event $event)
    {
        //$composer = $event->getComposer();
        // do stuff
    }
}