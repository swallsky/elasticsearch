<?php
/**
 * 文档相关的测试
 */
require("../vendor/autoload.php");

class Doc extends \SwaSky\ElasticSearch\ElasticSearch
{
    /**
     * 索引名称
     * @var string
     */
    protected $indexName = "swallsky";

    /**
     * 单行数据添加
     */
    public function addrow()
    {
        $res = $this->docCreateOne([
            'id' => 1, //id可不用设置
            'title' => '测试',
            'content' => '小礼物走一走，求点赞!'
        ]);
        print_r($res);
    }

    /**
     * 批量插入
     */
    public function add()
    {
        $res = $this->docCreate([
            [
                'id' => 10, //id可不用设置
                'title' => '测试',
                'content' => '小礼物走一走，求点赞!'
            ],
            [
                'id' => 11, //id可不用设置
                'title' => '测试',
                'content' => '小礼物走一走，求点赞!'
            ]
        ]);
        print_r($res);
    }

    /**
     * 通过id修改
     */
    public function edit()
    {
        $res = $this->docUpdateById(10,[
            'title' =>  '我被修改了'
        ]);
        print_r($res);
    }

    /**
     * 删除对应的文档
     */
    public function del()
    {
        $res = $this->docDeleteById(10);
        print_r($res);
    }

    /**
     * 通过id搜索
     */
    public function read()
    {
        $res = $this->docById(11);
        print_r($res);
    }
}

$type = $argv[1]; //测试类型
Doc::Connect()->{$type}();