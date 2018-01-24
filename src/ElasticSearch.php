<?php
/**
 * ElasticSearch接口开发
 */
namespace SwaSky\ElasticSearch;

use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    /*
    /**
     * @var string 索引名称 这个名字必须小写，不能以下划线开头，不能包含逗号
     */
    protected $indexName = '';
    /**
     * @var string 对应索引的类型名称 命名可以是大写或者小写，但是不能以下划线或者句号开头，不应该包含逗号， 并且长度限制为256个字符
     */
    protected $typeName = '';
    /**
     * 客户端连接句柄
     * @var \Elasticsearch\Client|null
     */
    protected $client = null;

    /**
     * 搜索查询提交参数
     * @var array
     */
    protected $params = [];

    /**
     * 条件缓存
     * @var array
     */
    protected $conditionCache = [];

    /**
     * 连接elasticsearch
     * EsSearch constructor.
     * @return $this
     */
    public static function Connect()
    {
        $self = new static();
        $self->typeName = $self->indexName . '_type';
        $config = Config::getConfig();
        $logger = ClientBuilder::defaultLogger($config['logfile']);
        $self->client = ClientBuilder::create()
            ->setHosts([
                [
                    "host" => $config["eshost"],
                    "port" => $config["esport"]
                ]
            ])//设置host
            ->setLogger($logger)//设置日志
            ->build();
        return $self;
    }

    /**
     * 创建索引
     * @param int $shards 分片数
     * @param int $replicas 备份数
     * @param array $settings 相关的设置
     * @param array $fields 字段信息
     * @return array
     */
    public function indexCreate(int $shards = 1, int $replicas = 0,$settings = [],$fields = [])
    {
        $params = [
            'index' => $this->indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => $shards, //分片数
                    'number_of_replicas' => $replicas //备份数
                ]
            ]
        ];
        if(!empty($settings)){//设置
            $params['body']['settings'] = $settings;
        }
        if(!empty($fields)){//映射字段信息
            $params['body']['mappings'][$this->typeName]['properties'] = $fields;
        }
        return $this->client->indices()->create($params);
    }

    /**
     * 获取配置信息
     * @return array
     */
    public function getSettings()
    {
        $params['index'] = $this->indexName;
        return $this->client->indices()->getSettings($params);
    }

    /**
     * 删除索引
     * @return array
     */
    public function indexDelete()
    {
        return $this->client->indices()->delete([
            'index' => $this->indexName
        ]);
    }

    /**
     * 单个文档插入
     * @param $body 一维数组，单行数据
     * @return array
     */
    public function docCreateOne(array $body)
    {
        $id = null;
        if (isset($body['id'])) {
            $id = $body['id'];
            unset($body['id']);
        }
        $params = [
            'index' => $this->indexName, //索引名称
            'type' => $this->typeName, //类型名称
            'id' => $id,
            'body' => $body
        ];
        return $this->client->index($params);
    }

    /**
     * 如果存在，则更新数据，如果不存在，则创建
     * @param array $condion
     * @param array $body
     * @return array
     */
    public function firstOrCreate(array $condion,array $body)
    {
        $this->must(function ($query) use ($condion){
            if(!empty($condion)){
                foreach ($condion as $k=>$v){
                    $query->term($k,$v);
                }
            }
        });
        $search = $this->search();
        if(isset($search['data'][0])){//已存在的数据
            $result = $this->docUpdateById($search['data'][0]['id'],$body); //修改数据
        }else{
            $result = $this->docCreateOne($body); //创建新数据
        }
        return $result;
    }

    /**
     * 创建文档
     * @param array $body 二维数组
     * @return array
     */
    public function docCreate(array $body, int $batch = 1000)
    {
        $params = ['body' => []];

        foreach ($body as $key => $bd) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->indexName, //索引名称
                    '_type' => $this->typeName, //类型名称
                    '_id' => empty($bd['id']) ? null : $bd['id'] //插入文档id
                ]
            ];
//            if (isset($bd['id'])) unset($bd['id']); //去掉id
            $params['body'][] = $bd;
            // 批处理
            if ($key > 0 && $key % $batch == 0) {
                $responses = $this->client->bulk($params);
                //重置
                $params = ['body' => []];
                // unset the bulk response when you are done to save memory
                unset($responses);
            }
        }
        if (!empty($params['body'])) { //批量插入
            return $this->client->bulk($params);
        }
    }

    /**
     * 通过id获取文档
     * @param $id
     * @return array
     */
    public function docById($id)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->typeName, //类型名称
            'id' => $id
        ];
        $flag = $this->client->exists($params); //判断文档是否存在
        return $flag ? $this->client->get($params)['_source'] : [];
    }

    /**
     * 修改文档
     * @param $id 需要修改id
     * @param array $body 字段
     * @return array
     */
    public function docUpdateById($id, array $body)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->typeName,
            'id' => $id
        ];

        $flag = $this->client->exists($params); //判断文档是否存在
        if ($flag) {
            $params['body'] = [
                'doc' => $body
            ];
            // Update doc at /my_index/my_type/my_id
            return $this->client->update($params);
        }
    }

    /**
     * 根据id删除文档
     * @param $id
     * @return array
     */
    public function docDeleteById($id)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->typeName,
            'id' => $id
        ];
        $flag = $this->client->exists($params); //判断文档是否存在
        if ($flag) {
            // Delete doc at /my_index/my_type/my_id
            return $this->client->delete($params);
        }
    }

    /**
     * 模糊搜索,字段查询
     * @param string $field 字段名
     * @param string/array $keyword 关键字
     * @return $this
     */
    public function match(string $field,$keyword)
    {
        $this->conditionCache[] = ['match' => [$field => $keyword]];
        return $this;
    }

    /**
     * 模糊匹配,多字段
     * @param array $field 多个字段数组 如果为空表示查询所有字段
     * @param string/array $keyword 关键字
     * @return $this
     */
    public function multiMatch(array $field = [],$keyword)
    {
        $multi = ['query' => $keyword];
        if (!empty($field)) {
            $multi['fields'] = $field;
        }
        $this->conditionCache[] = ['multi_match' => $multi];
        return $this;
    }

    /**
     * 短语匹配,单字段查询, 精确匹配一系列单词或者短语
     * @param string $field 字段名
     * @param string/array $keyword 关键字
     * @return $this
     */
    public function matchPhrase(string $field,$keyword)
    {
        $this->conditionCache[] = ['match_phrase' => [$field => $keyword]];
        return $this;
    }

    /**
     * 语法匹配 例如: (瀚海 AND 博联) OR html5
     * @param array $field 字段名 如果为空时，则查找所有字段
     * @param string/array $keyword 关键字
     * @return $this
     */
    public function queryString(array $field = [],$keyword)
    {
        $querystring = ['query' => $keyword];
        if (!empty($field)) { //多个字段
            $querystring['fields'] = $field;
        }
        $this->conditionCache[] = ['query_string' => $querystring];
        return $this;
    }

    /**
     * 范围查询
     * @param string $field 字段名称
     * @param array $condions 查询条件 例如大于、小于、等于等
     * @return $this
     */
    public function range(string $field, array $condions = [])
    {
        $this->conditionCache[] = ['range' => [$field => $condions]];
        return $this;
    }

    /**
     * 精确值查找 类似于where条件
     * @param string $field 字段名称
     * @param string/array $keyword 查找值
     * @return $this
     */
    public function term(string $field,$keyword)
    {
        $this->conditionCache[] = ['term' => [$field => $keyword]];
        return $this;
    }

    /**
     * 精确值查找 类似于where条件 ,同时匹配多个值
    "terms" : {
    "price" : [20, 30]
    }
     * @param string $field 字段名称
     * @param array $keyword 以数组值表示多个值
     * @return $this
     */
    public function terms(string $field, array $keyword)
    {
        $this->conditionCache[] = ['terms' => [$field => $keyword]];
        return $this;
    }

    /**
     * 所有的语句都 必须（must） 匹配，与 AND 等价
     * @param callable $query
     * @return $this
     */
    public function must(callable $query)
    {
        call_user_func($query, $this);
        if (!empty($this->conditionCache))
            $this->params['body']['query']['bool']['must'] = $this->conditionCache; //缓存查询条件
        $this->conditionCache = []; //清空缓存条件
        return $this;
    }

    /**
     * 所有的语句都 不能（must not） 匹配，与 NOT 等价
     * @param callable $query
     * @return $this
     */
    public function mustNot(callable $query)
    {
        call_user_func($query, $this);
        if (!empty($this->conditionCache))
            $this->params['body']['query']['bool']['must_not'] = $this->conditionCache; //缓存查询条件
        $this->conditionCache = []; //清空缓存条件
        return $this;
    }

    /**
     * 至少有一个语句要匹配，与 OR 等价
     * @param callable $query
     * @return $this
     */
    public function should(callable $query)
    {
        call_user_func($query, $this);
        if (!empty($this->conditionCache))
            $this->params['body']['query']['bool']['should'] = $this->conditionCache; //缓存查询条件
        $this->conditionCache = []; //清空缓存条件
        return $this;
    }

    /**
     * 必须 匹配，但它以不评分、过滤模式来进行。这些语句对评分没有贡献，只是根据过滤标准来排除或包含文档。
     * @param callable $query
     * @return $this
     */
    public function filter(callable $query)
    {
        call_user_func($query, $this);
        if (!empty($this->conditionCache))
            $this->params['body']['query']['bool']['filter'] = $this->conditionCache; //缓存查询条件
        $this->conditionCache = []; //清空缓存条件
        return $this;
    }

    /**
     * 需要显示的字段
     * @param array $fields
     * @return $this
     */
    public function select(array $fields)
    {
        $this->params['_source'] = $fields;
        return $this;
    }

    /**
     * 从第几行开始
     * @param int $f 开始行
     * @return $this
     */
    public function from($f)
    {
        $this->params['body']['from'] = $f;
        return $this;
    }

    /**
     * 每页显示个数
     * @param int $p 每页显示个数
     * @return $this
     */
    public function size($p)
    {
        $this->params['body']['size'] = $p;
        return $this;
    }

    /**
     * 字段排序
     * @param array $sort 排序字段，二维数组
     * @return $this
     */
    public function sort($sort = [])
    {
        $order = [];
        foreach ($sort as $s => $srt) {
            $order[$s] = ['order' => $srt];
        }
        $this->params['body']['sort'] = $order;
        return $this;
    }

    /**
     * 自定义查询
     * @param array $query 查寻数组
     * @return $this
     */
    public function query(array $query)
    {
        $this->params['body']['query'] = $query;
        return $this;
    }

    /**
     * 搜索结果
     * @return array
     */
    public function search()
    {
        $this->params['index'] = $this->indexName;
        $this->params['type'] = $this->typeName;
        $results = $this->client->search($this->params);
        $temp = [
            'total' => $results['hits']['total'], //总记录数
            'data' => [] //数据初始化
        ];
        foreach ($results['hits']['hits'] as $dd) {
            $dd['_source']['id'] = $dd['_id'];
            $temp['data'][] = $dd['_source'];
        }
        return $temp;
    }
}