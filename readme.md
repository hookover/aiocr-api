## 注意
如果重新开发这个项目，我可能会选择swoole,或者go之类的，所以参考参考就好了吧

## 基于LUMEN开发的高性能图片识别平台API接口源码
此项目是整个AI图片 识别项目 的API接口端

原本是作为商业项目开发的，但项目未上线就宣告结束了，出于共同学习的目的，开源此项目

因为项目并未真正上线运营，所以有一些不足或考虑不周全之处请自行忽略，若发现错误，还请指正。

用户管理系统&业务官网源码请移步至 https://github.com/hookover/aiocr-web.git

根据预期业务，将接口设计为：承载每天2000万张图片上传并相应处理的API

## API接口 很少

/servers    | 获取服务器列表

/user/login                 | 登录
/user/point?token=:token    | 获取用户剩余积分

/decode/upload-base64?token=:token  | 以base64字符串方式上传图片
/decode/upload?token=:token         | 以form-data数据流方式上传图片

/result?token=:token        | 获取识别结果
/report?token=:token        | 若图片识别错误，可报错                

接口的详细使用，请搭建https://github.com/hookover/aiocr-web.git 后点击API文档查看

## 技术目标
高并发、高性能、安全稳定、最佳实践方案

## 用到的技术栈
尽量RESTful规范

JWT，加密算法修改为了最快的SHA256

REDIS长连接，测试了memcached

高性能自增ID生成器：php_snowflke (测试每秒生成10万+不重复ID)

性能分析工具使用的是xhprof
     
APIDOC 根据代码注释自动生成API文档

MYSQL事件+存储过程 将上传图片日志表自动按天分区，并删除7天之前的分区

MYSQL事件+存储过程 自动统计每个用户/开发工程师每天产生的数据


#### LUMEN 层面
    自定义Auth,将token验证放到了Auth服务模块中
    定义了日志服务
    统一的API响应错误代码机制
    自增ID采用2个数据字段存储，并使用联合索引提升性能
    SQL语句优化
    还有一些细节已经忘记了

    
## 安装
    1、 为php安装此扩展 https://github.com/hookover/php_snowflake.git
    2、 克隆此代码并composer安装代码依赖
    3、 配置nginx/apache 参考lumen的配置
    4、 cp .env.example .env
    5、 项目数据表的创建和基础数据在这里： https://github.com/hookover/aiocr-web.git，建议先搭建业务系统再搭建API
    
## 代码层面优化
借助xhprof查看性能瓶颈，根据性能瓶颈提出解决方案，并实施，最终实现单个API请求处理在10ms左右，数据并发起来，业务逻辑也不会出现任何问题。

## 高并发是一个系统工程
个人认为，要想让整个系统实现高并发，需要从软件到硬件再到服务器架构配置做好全面协调，任何一个地方的短板都可能成为整个系统的短板。

代码层面无非就是减少对数据库的读写压力、将一些频繁数据放入缓存，PHP到缓存到数据库都通过长连接提高连接速度，数据库表合理的使用好索引，注意SQL质量和调整好一些耗费资源的算法。

服务器软件配置方面又会涉及到LINUX本身的优化，服务软件之间的协调，数据库之间的协调，负载均衡等。

硬件层面又需要考虑CPU瓶颈，硬盘读写性能，内存大小，服务器间网络通信状况等..

## 简单压测
开发机：
Intel® Core™ i7-4770 CPU @ 3.40GHz × 8 + 15.6 GiB 内存

环境：ubuntu + nginx + php7 + php-fpm tcp

压测工具： ab

开发机上同时运行了phpstorm，网页等

nginx和php-fpm是使用的apt安装后的默认配置，未做深入调优

若需要下载代码自己压测，或者真正到线上运行，
还需要调整nginx、php、php-fpm、mysql、redis的配置，以便让这些服务器软件协同工作。

测试数据： 

100并发1万次
    
    Server Software:        nginx/1.12.2
    Server Hostname:        api.decaptcha.com
    Server Port:            80
    
    Document Path:          /v2/decode/upload-base64?file=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAARCA0dGVyY4LQCQAAABFXKwAAAABJRU5ErkJggg==&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTk4NjkxOCwiZXhwIjoxNTIxNzAwMDY0fQ.tFbzUwqI5VU1oENTJWOqYLNGb5bg4Yh-L7rsIvRDe2M
    Document Length:        82 bytes
    
    Concurrency Level:      100
    Time taken for tests:   10.254 seconds
    Complete requests:      10000
    Failed requests:        0
    Total transferred:      2450000 bytes
    HTML transferred:       820000 bytes
    Requests per second:    975.25 [#/sec] (mean)
    Time per request:       102.537 [ms] (mean)
    Time per request:       1.025 [ms] (mean, across all concurrent requests)
    Transfer rate:          233.34 [Kbytes/sec] received
    
    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.2      0       4
    Processing:     6  102 267.1     31    4582
    Waiting:        6  102 267.1     31    4582
    Total:          6  102 267.1     31    4583

200并发10万次

    Server Software:        nginx/1.12.2
    Server Hostname:        api.decaptcha.com
    Server Port:            80
    
    Document Path:          /v2/decode/upload-base64?file=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAARCA0dGVyY4LQCQAAABFXKwAAAABJRU5ErkJggg==&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTk4NjkxOCwiZXhwIjoxNTIxNzAwMDY0fQ.tFbzUwqI5VU1oENTJWOqYLNGb5bg4Yh-L7rsIvRDe2M
    Document Length:        82 bytes
    
    Concurrency Level:      200
    Time taken for tests:   104.265 seconds
    Complete requests:      100000
    Failed requests:        17
       (Connect: 0, Receive: 0, Length: 17, Exceptions: 0)
    Non-2xx responses:      17
    Total transferred:      24501853 bytes
    HTML transferred:       8201717 bytes
    Requests per second:    959.09 [#/sec] (mean)
    Time per request:       208.531 [ms] (mean)
    Time per request:       1.043 [ms] (mean, across all concurrent requests)
    Transfer rate:          229.49 [Kbytes/sec] received
    
    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.2      0       3
    Processing:     6  208 1630.6     43   60001
    Waiting:        6  208 1630.6     43   60001
    Total:          6  208 1630.6     44   60001


500并发，10万次

    Server Software:        nginx/1.12.2
    Server Hostname:        api.decaptcha.com
    Server Port:            80
    
    Document Path:          /v2/decode/upload-base64?file=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAARCA0dGVyY4LQCQAAABFXKwAAAABJRU5ErkJggg==&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTk4NjkxOCwiZXhwIjoxNTIxNzAwMDY0fQ.tFbzUwqI5VU1oENTJWOqYLNGb5bg4Yh-L7rsIvRDe2M
    Document Length:        82 bytes
    
    Concurrency Level:      500
    Time taken for tests:   106.200 seconds
    Complete requests:      100000
    Failed requests:        114
       (Connect: 0, Receive: 0, Length: 114, Exceptions: 0)
    Non-2xx responses:      114
    Total transferred:      24511451 bytes
    HTML transferred:       8210864 bytes
    Requests per second:    941.62 [#/sec] (mean)
    Time per request:       531.001 [ms] (mean)
    Time per request:       1.062 [ms] (mean, across all concurrent requests)
    Transfer rate:          225.39 [Kbytes/sec] received
    
    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    0   0.7      0      14
    Processing:     5  530 3091.5    151   63039
    Waiting:        5  530 3091.5    151   63039
    Total:          5  530 3091.9    151   63049

    
1000并发1万次

    Server Software:        nginx/1.12.2
    Server Hostname:        api.decaptcha.com
    Server Port:            80
    
    Document Path:          /v2/decode/upload-base64?file=data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAMAAAARCA0dGVyY4LQCQAAABFXKwAAAABJRU5ErkJggg==&token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTk4NjkxOCwiZXhwIjoxNTIxNzAwMDY0fQ.tFbzUwqI5VU1oENTJWOqYLNGb5bg4Yh-L7rsIvRDe2M
    Document Length:        82 bytes
    
    Concurrency Level:      1000
    Time taken for tests:   27.926 seconds
    Complete requests:      10000
    Failed requests:        64
       (Connect: 0, Receive: 0, Length: 64, Exceptions: 0)
    Non-2xx responses:      64
    Total transferred:      2456016 bytes
    HTML transferred:       825824 bytes
    Requests per second:    358.09 [#/sec] (mean)
    Time per request:       2792.559 [ms] (mean)
    Time per request:       2.793 [ms] (mean, across all concurrent requests)
    Transfer rate:          85.89 [Kbytes/sec] received
    
    Connection Times (ms)
                  min  mean[+/-sd] median   max
    Connect:        0    2   6.7      0      27
    Processing:    62 1170 3005.8    256   27883
    Waiting:       62 1170 3005.8    256   27883
    Total:         62 1172 3008.8    256   27906


![](1.png)
