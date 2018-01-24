# ElasticSearch
> elasticsearch简介
  * Elasticsearch 是一个分布式、可扩展、实时的搜索与数据分析引擎。 它能从项目一开始就赋予你的数据以搜索、分析和探索的能力，这是通常没有预料到的。 它存在还因为原始数据如果只是躺在磁盘里面根本就毫无用处。
  * 无论你是需要全文搜索，还是结构化数据的实时统计，或者两者结合，这本指南都能帮助你了解其中最基本的概念， 从最基本的操作开始学习 Elasticsearch。之后，我们还会逐渐开始探索更加高级的搜索技术，不断提升搜索体验来满足你的用户需求。
  * Elasticsearch 不仅仅只是全文搜索，我们还将介绍结构化搜索、数据分析、复杂的语言处理、地理位置和对象间关联关系等。 我们还将探讨如何给数据建模来充分利用 Elasticsearch 的水平伸缩性，以及在生产环境中如何配置和监视你的集群。
> elasticsearch安装及教程
  * [官网](https://www.elastic.co/cn/)
  * [下载](https://www.elastic.co/downloads/elasticsearch)
  * [权威指南](https://www.elastic.co/guide/cn/elasticsearch/guide/current/index.html)
## 大数据基础
> ElasticSearch是大数据搜索的基础，掌握了ElasticSearch，就已经迈入了大数据殿堂。

## 方便开发者使用ElasticSearch
> 虽然ElasticSearch入门并不难，但是不免有很多坑要踩，作者只为了更方便的使用ElasticSearch，和更好的理解ElasticSearch。

## 运行要求
  * JDK >= 1.8.0
  * MAVEN
  * PHP >= 7.0.0
  * JAVA_HOME环境变量设置
 
## 使用方法
  请查看本包tests中，测试程序，通过第三个参数直接调用测试方法
  ```php
  php index.php add #添加索引
  php index.php del #删除索引
  ```