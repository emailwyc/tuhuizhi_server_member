# vip管理系统
## 简介
[https://vip.rtmap.com](https://vip.rtmap.com)

## 数据统计

### 基础数据统计接口
#### 请求

`get /api/dt/base?service=1000&sdate=2016-10-01&edate=2016-10-10&size=10&page=1&key=`

| 参数       |是否必填| 默认值    | 描述                     | 开发者 |
| ----------|---| ------    |:-----------------------:|:----:|
| service   |是 |1000, [查询其它值](http://182.92.3.90:12345/ka_client/dt/blob/master/conf.json)        | 服务类别，1000表示全部      |王祥 |
| sdate     |否 |前一天日期    | 开始日期                  |王祥 |
| edate     |否 |今天日期      | 结束日期                  |王祥 |
| page      |否 |1           | 服务类别                   |王祥 |
| size      |否 |10          | 服务类别，1000表示全部       |王祥 |
| key       |是 |''          | key_admin                 |王祥 |

#### 响应
```
{
  "code": 200,
  "data": {
    "totalPage": 10,
    "list": [{
      // 日期
      "date": "2016-10-01",
      // 累计pv
      "totalpv": 9999,
      // 累计用户量
      "totaluv": 5555,
      "pv": 888,
      "uv": 666
    }]
  }
}
```

### 导出统计数据接口
#### 请求

`get /api/dt/base?service=1000&sdate=2016-10-01&edate=2016-10-10&size=10&page=1&action=export`

参数同查询接口。
