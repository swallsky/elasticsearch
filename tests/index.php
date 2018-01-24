<?php
/**
 * 索引相关实例
 */
require("../vendor/autoload.php");

class Index extends \SwaSky\ElasticSearch\ElasticSearch
{
    /**
     * 索引名称
     * @var string
     */
    protected $indexName = 'swallsky';

    /**
     * 新增索引
     */
    public function add()
    {
        $res = $this->indexCreate(1,0);
        print_r($res);
    }

    /**
     * 删除索引
     */
    public function del()
    {
        $res = $this->indexDelete();
        print_r($res);
    }
}

$type = $argv[1]; //测试类型
Index::Connect()->{$type}();