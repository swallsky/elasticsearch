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

    /**
     * 创建索引时创建分词器
     */
    public function ikanalyzer()
    {
        $res = $this->indexCreate(1,0,
            [
                'analysis' => [ //设置分析器
                    'analyzer' => [
                        'ik_analyzer' => [//自定义分词器
                            'tokenizer' => 'ik_analyzer_selecter',
                            "char_filter" => ["html_strip"] //过滤html标签
                        ]
                    ],
                    'tokenizer' => [ //设置分词
                        'ik_analyzer_selecter' => [
                            'type' => 'ik_smart'
                        ]
                    ]
                ]
            ],
            [
                'title' => [ //名称
                    'type' => 'text',
                    'analyzer' => 'ik_analyzer', //中文分词
                    'search_analyzer' => 'ik_analyzer',
                ]
            ]
        );
        print_r($res);
    }
}

$type = $argv[1]; //测试类型
Index::Connect()->{$type}();