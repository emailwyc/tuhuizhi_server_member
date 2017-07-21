# 微服务-停车缴费
## 上线流程
### 发布静态资源到CDN
登录上线机器并执行发布命令。
```
$ cd /usr/www/park
$ sh ./bin/master.sh
```
### 重启两台生产服务器
分别登录两台生产机器并执行重启命令。
```
$ cd /usr/www/park
$ sh ./bin/online.sh
```

## app.json
项目配置文件

## Dockerfile
创建docker镜像
`$ docker build -t park:0.0.2 .`

测试镜像
`$ docker run -it -p 8001:8001 park:0.0.2`

请求http://127.0.0.1:8001/park
