<?php

namespace SwaSky\ElasticSearch;

use Composer\Script\Event;

$packageDir = dirname(__DIR__); //当前包的根目录

$vendorDir = dirname(dirname(dirname($packageDir))); //项目根目录

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

    public static function warmCache(Event $event)
    {
        $composer = $event->getComposer();
        // make cache toasty
    }
}