[TOC]


#内部支付方法


| 名词| 解释|
| ----- | ---------------------------------------- |
| 命名空间 | namespace Pay\Service;                   |
| 支持方式  | 微信：支付、退款（2018-01-25无订单供测试，未测试）、订单号查询；<br/>支付宝：支付、退款（2018-01-25无订单供测试，未测试）、无订单号查询接口；<br/> 客户自己提供的第三方支付，需在账号配置后台配置对接类，如果是第三方支付，请后台配置时精确到功能模块，如停车|

## 支付方法
```html
 方法：public static function requestOrder($platform, $tradeType, $param,$keyAdmin, $modular) 
```

| 参数名        | 参数值                                      |
| ---------- | ---------------------------------------- |
| $platform  | 支付平台：微信填wechat；支付宝填alipay |
| $tradeType | 交易方式，目前支持的交易方式有，1微信：jsapi、app、micropay，2支付宝：wappay |
| $param     | [请求订单字段信息](#payparam)，分微信和支付宝，并且分交易方式类型，下面是详细说明 |
| $keyAdmin  | 商户key |
| $modular   | 模块名称，停车：park，券商城：coupon，公共：public |

### <span id="payparam">微信下单请求参数</span>
#### 微信请求公共参数

| 参数                  | 参数类型    | 参数说明                              | 是否为空 | 样例                                      |
| ------------------- | ------- | --------------------------------- | ---- | --------------------------------------- |
| total_fee           | Integer | 金额。必须大于1，单位：分                     | 不可空  | 100                                     |
| body                | String  | 商品描述交易。                           | 不可空  | 腾讯充值中心-QQ会员充值。                          |
| notify_url          | String  | 业务回调地址。支付成功后，根据此地址拼接业务参数，将消息下发子系统 | 可空   | http://www.rtmap.com/notify             |
| attach              | String  | 公用回传参数                            | 可空   | {"carNo":"京WJ9087","key_admin":"xxxx"}  |
| attach_transmit_tag | String  | 是否透传attach给微信,解决attach过长被微信拦截     | 可空   | 默认“Y”                                   |
| device_info         | String  | 设备号                               | 可空   | 终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传"WEB" |
| detail              | String  | 商品详情                              | 可空   | 商品详情                                    |
| fee_type            | String  | 货币类型                              | 可空   | 货币类型                                    |
| goods_tag           | String  | 商品标记                              | 可空   | 商品标记，代金券或立减优惠功能的参数                      |
| out_trade_no        | String  | 商户订单号,保证唯一性，不填则默认使用支付系统生成的        | 可空   | T0002016111002432043341865563597        |
| receipt             | String  | 暂不可用，需要电子发票，值传  Y 用户订单详情才会有开票入口   | 可空   | Y                                       |

#### JSAPI交易类型

| 参数      | 参数类型   | 参数说明                                     | 是否为空 | 样例                           |
| ------- | ------ | ---------------------------------------- | ---- | ---------------------------- |
| openid  | String | 微信用户标识。                                  | 不可空  | oWm-rtxY3ayd5Sz2KxDfi7psjARA |
| appid   | String | 微信分配的子商户公众账号ID，如需在支付完成后获取sub_openid则此参数必传。 | 可空   | wx25da70813e5cefe8           |
| wxa_tag | String | 小程序支付标识，Y代表小程序支付场景，默认为N                  | 可空   | N                            |

#### APP交易类型

| 参数    | 参数类型   | 参数说明                                     | 是否为空 | 样例                 |
| ----- | ------ | ---------------------------------------- | ---- | ------------------ |
| appid | String | 微信分配的子商户公众账号ID，如需在支付完成后获取sub_openid则此参数必传。 | 不可空  | wx25da70813e5cefe8 |



#### MICROPAY交易类型

| 参数        | 参数类型   | 参数说明  | 是否为空    | 样例                 |
| --------- | ------ | ----- | ------- | ------------------ |
| auth_code | String | 用户支付码 | **不可空** | 130032369162478159 |

### 支付宝请求通用参数

| 参数           | 参数类型    | 参数说明                              | 是否为空 | 样例                                       |
| ------------ | ------- | --------------------------------- | ---- | ---------------------------------------- |
| total_amount | Integer | 金额。必须大于1，单位：分                     | 不可空  | 100                                      |
| subject      | String  | 商品描述交易。                           | 不可空  | 腾讯充值中心-QQ会员充值。                           |
| return_url   | String  | 支付完成前端跳转地址                        | 可空   | 不传则跳转到默认值，默认值可联系负责人反馈后修改                 |
| notify_url   | String  | 业务回调地址。支付成功后，根据此地址拼接业务参数，将消息下发子系统 | 可空   | http://www.rtmap.com/notify              |
| attach       | String  | 公用回传参数，需URLEncode后再提交             | 可空   | merchantBizType%3d3C%26merchantBizNo%3d2016010101111 |
| out_trade_no | String  | 商户订单号,保证唯一性，不填则默认使用支付系统生成的        | 可空   | T0002016111002432043341865563597         |
| receipt      | String  | 暂不可用，需要电子发票，值传  Y 用户订单详情才会有开票入口   | 可空   | Y                                        |

#### WAPPAY交易类型

暂无







### 返回结果

### 微信支付返回结果

#### 响应字段随tradeType支付方式变动

当tradeType=JSAPI时，响应参数如下：

```json
{
  "status": 200,
  "message": "success.",
  "data": {
    "timeStamp": "1474181109",
    "package": "prepay_id=wx20160918144509b608770e350358145698",
    "paySign": "A32D16104C488A1BD001CD360344DB2C",
    "appId": "wxf3a057928b881466",
    "outTradeNo": "T0022016091814450853928332239279",
    "signType": "MD5",
    "nonceStr": "sDMO3sZNmLScBeZv"
  }
}
```

| 参数         | 参数类型   | 参数说明                                     | 是否为空 | 样例                                       |
| ---------- | ------ | ---------------------------------------- | ---- | ---------------------------------------- |
| appId      | String | 公众号名称                                    | 不可空  | wxf3a057928b881466                       |
| timeStamp  | String | 时间戳，自1970年以来的秒数                          | 不可空  | 1474181109                               |
| nonceStr   | String | 随机串                                      | 不可空  | sDMO3sZNmLScBeZv                         |
| package    | String | 订单详情扩展字符串。统一下单接口返回的prepay_id参数值，提交格式如：prepay_id=*** | 不可空  | prepay_id=wx20160918144509b608770e350358145698 |
| signType   | String | 微信签名方式                                   | 不可空  | MD5                                      |
| paySign    | String | 微信签名                                     | 不可空  | A32D16104C488A1BD001CD360344DB2C         |
| outTradeNo | String | 商户系统内部的订单号,32个字符内、可包含字母,                 | 不可空  | T0022016091811444926537392894154         |



当tradeType=APP时,响应参数如下：

```json
{
  "status": 200,
  "message": "success.",
  "data": {
    "timestamp": "1474181109",
    "package": "wx20160918144509b608770e350358145698",
    "sign": "A32D16104C488A1BD001CD360344DB2C",
    "partnerid": "1262120901",
    "appid": "wxf3a057928b881466",
    "outTradeNo": "T0022016091814450853928332239279",
	"noncestr": "sDMO3sZNmLScBeZv"
  }
}
```

| 参数         | 参数类型   | 参数说明                                     | 是否为空 | 样例                                   |
| ---------- | ------ | ---------------------------------------- | ---- | ------------------------------------ |
| appid      | String | 公众号名称                                    | 不可空  | wxf3a057928b881466                   |
| noncestr   | String | 随机码                                      | 不可空  | sDMO3sZNmLScBeZv                     |
| partnerid  | String | 商户号                                      | 不可空  | 1262120901                           |
| package    | String | 订单详情扩展字符串。统一下单接口返回的prepay_id参数值，提交格式如：prepay_id=*** | 不可空  | wx20160918144509b608770e350358145698 |
| prepayid   | String | 微信签名方式                                   | 不可空  | MD5                                  |
| outTradeNo | String | 商户系统内部的订单号,32个字符内、可包含字母,                 | 不可空  | T0022016091811444926537392894154     |



当tradeType=NATIVE时,响应参数如下：

```json
{
  "status": 200,
  "message": "success.",
  "data": {
    "codeUrl": " http://t.cn/SXswwe2",
    "outTradeNo": "T0022016091814450853928332239279"
}
}
```

| 参数         | 参数类型   | 参数说明                     | 是否为空 | 样例                               |
| ---------- | ------ | ------------------------ | ---- | -------------------------------- |
| codeUrl    | String | 付款二维码地址                  | 不可空  | http://t.cn/SXswwe2              |
| outTradeNo | String | 商户系统内部的订单号,32个字符内、可包含字母, | 不可空  | T0022016091811444926537392894154 |



当tradeType=MICROPAY时，响应参数如下：

```json
{
  "status": 200,
  "message":"success.",
  "data": {
		"outTradeNo": "T0022016091814450853928332239279"
    }
}
```

| 参数         | 参数类型   | 参数说明                    | 是否为空 | 样例                               |
| ---------- | ------ | ----------------------- | ---- | -------------------------------- |
| outTradeNo | String | 商户系统内部的订单号,32个字符内、可包含字母 | 不可以  | T0022016091811444926537392894154 |



#### 支付宝返回结果

当tradeType=WAPPAY, 响应HTML

```html
<form name="punchout_form" method="post" action="https://openapi.alipay.com/gateway.do?charset=UTF-8&method=alipay.trade.wap.pay&sign=g4N4o1EMPNY5s%2BAPseHL0lpea2MZzzdTWwcg9TlRyAqwyYIxyYW4zvh8wrxTXM%2Fa1QapJ%2FVpGq%2BE64h3eYJm7Mv3oeA2CvJn2wAJ3LioDVaX5DDaOqSZ8pVDuEdRCW6uzcgd%2ByIz0lDzsrrKMUPzhwt9Lmo4xQcWdMbmRsKy9V3o4UpXTZOTsV3ZdM4GdUL9cz1mkFVv0xuhdS6F4otJ4TyQ%2F%2F4qyPqFjBF7BnkaSaxdH5ZQSRMLcvr2j5wqVb%2BQuV8dVmhEdCrwepPWXtGrwzPgT1lZ4bxMiuEvEtZsZVaMuHLNFmtOPP%2BJsV%2FjEPuwg0do4QRYhxC%2FdhUxvddXQg%3D%3D&return_url=http%3A%2F%2Fwww.rtmap.com%2Fzb_2%2Fh5pay%2Fwx%2Falipay.html&notify_url=http%3A%2F%2Fforward.muarine.com%2Fpay-api%2Fcallback%2Falipay&version=1.0&app_id=2016050501368073&sign_type=RSA2&timestamp=2017-06-14+17%3A40%3A40&alipay_sdk=alipay-sdk-java-dynamicVersionNo&format=json">
    <input type="hidden" name="biz_content" value="{&quot;body&quot;:&quot;虚拟商品&quot;,&quot;goods_type&quot;:&quot;0&quot;,&quot;out_trade_no&quot;:&quot;A0012017061417404045297392273733&quot;,&quot;product_code&quot;:&quot;QUICK_WAP_PAY&quot;,&quot;subject&quot;:&quot;测试支付宝WAP支付&quot;,&quot;timeout_express&quot;:&quot;2h&quot;,&quot;total_amount&quot;:&quot;0.1&quot;}">
    <input type="submit" value="立即支付" style="display:none" >
</form>
<script>document.forms[0].submit();</script>
```




## 退款方法

```html
 方法：public static function refund($platform, $keyAdmin,$modular, $param)
```

| 参数名       | 参数值                       |
| --------- | ------------------------- |
| $platform | 支付平台：微信填wechat；支付宝填alipay |
| $keyAdmin | 商户key                     |
| $modular  | 模块名称，停车：park，券商城：coupon   |
| $param    | [请求订单字段信息](#refundparam)  |



### <span id="refundparam">参数说明</span>
#### 传递参数，微信支付宝共用

| 参数              | 参数类型    | 参数说明                                     | 是否为空 | 样例                               |
| --------------- | ------- | ---------------------------------------- | ---- | -------------------------------- |
| out_trade_no    | String  | 商户订单号。                                   | 不可空  | T0022016091810271359912601092865 |
| refund_fee      | Integer | 退款金额，单位：分                                | 不可空  | 100                              |
| refund_account  | String  | 退款资金来源，REFUND_SOURCE_UNSETTLED_FUNDS---未结算资金退款、REFUND_SOURCE_RECHARGE_FUNDS---可用余额退款 | 可空   | REFUND_SOURCE_UNSETTLED_FUNDS    |
| device_info     | String  | 终端设备号                                    | 可空   | 013467007045764                  |
| refund_fee_type | String  | 货币种类                                     | 可空   | CNY                              |

**微信返回结果**

```json

  "status": 200,
  "message": "success.",
  "data": {
    "transactionId": "4008152001201609184277874006",
    "outTradeNo": "T0022016091814424667710978256826",
    "outRefundNo": "T2152016091814440793488024100761",
    "refundId": "2008152001201609180456831963"
  }
}
```

| 参数            | 参数类型   | 参数说明                     | 是否为空 | 样例                               |
| ------------- | ------ | ------------------------ | ---- | -------------------------------- |
| outTradeNo    | String | 商户系统内部的订单号,32个字符内、可包含字母, | 不可空  | T0022016091811444926537392894154 |
| transactionId | String | 微信订单号                    | 不可空  | 4008152001201609184265588676     |
| refundId      | String | 商户退款单号                   | 不可空  | 2008152001201609180456761553     |
| outRefundNo   | String | 微信退款单号                   | 不可空  | T2042016091813290484858988008862 |

**支付宝返回结果**

```json
  "status": 200,
  "message": "success.",
  "data": {
    "transactionId": "2017061421001004510275263240",
    "outTradeNo": "A0012017061417404045297392273733",
    "outRefundNo": "A0012017061417404045297392273733"
  }
}
```

| 参数            | 参数类型   | 参数说明                     | 是否为空 | 样例                               |
| ------------- | ------ | ------------------------ | ---- | -------------------------------- |
| outTradeNo    | String | 商户系统内部的订单号,32个字符内、可包含字母, | 不可空  | A0022016091811444926537392894154 |
| transactionId | String | 支付宝交易单号                  | 不可空  | 4008152001201609184265588676     |
| outRefundNo   | String | 支付宝退款单号                  | 不可空  | A2042016091813290484858988008862 |



## 查询订单详情方法

```html
 方法：public static function queryOrder($platform, $param, $keyAdmin, $modular)
```
| 参数名       | 参数值                          |
| --------- | ---------------------------- |
| $platform | 支付平台：微信填wechat；支付宝填alipay    |
| $keyAdmin | 商户key                        |
| $modular  | 模块名称，停车：park，券商城：coupon      |
| $param    | [请求订单字段信息](#queryorderparam) |


### <span id="queryorderparam">参数说明</span>
#### 传递参数，微信支付宝共用


| 参数           | 参数类型    | 参数说明       | 是否为空 | 样例                               |
| ------------ | ------- | ---------- | ---- | -------------------------------- |
| platform     | String  | 支付平台类型     | 不可空  | wx                               |
| mchId        | Integer | 微信支付分配的商户号 | 不可空  | 1262120901                       |
|              |         |            |      |                                  |
| out_trade_no | String  | 商户订单号。     | 不可空  | T0022016091810271359912601092865 |

#### 返回结果

```json
{
  "status": 200,
  "message": "success.",
  "data": {
    "createTime": 1478719614000,
    "updateTime": 1478718874000,
"transactionId": "4001882001201611109260671680",
    "outTradeNo": "T0002016111003264825715132280804",
    "openid": "oq1Gkt1cOWRhX1RUYl23E1uIBXkI",
    "subOpenid": null,
    "appid": "wxfbea9e80126c504f",
    "body": "停车缴费",
    "totalFee": 1,
    "paymentTime": 1478719613000,
    "tradeType": "JSAPI",
    "bankType": "CMB_CREDIT",
    "feeType": "CNY",
    "attach": "T0002016111003264825719010317791",
    "status": 1
  }
}
```

| 参数            | 参数类型    | 参数说明                                     | 是否为空 | 样例                               |
| ------------- | ------- | ---------------------------------------- | ---- | -------------------------------- |
| createTime    | Long    | 记录创建时间                                   | 不可空  | 1478719614000                    |
| updateTime    | Long    | 记录更新时间                                   | 不可空  | 1478719614000                    |
| transactionId | String  | 微信支付订单号                                  | 不可空  | 4005692001201610126482127413     |
| outTradeNo    | String  | 商户订单号                                    | 不可空  | T0022016101215272637836888665923 |
| openid        | String  | 用户标识                                     | 不可空  | oWm-rt0yjfypc6b3mP8uRWWtIe8U     |
| subOpenid     | String  | 用户子标识                                    | 可空   | oJjX6vownzYbJEe54WYTBNnA0cZw     |
| bankType      | String  | 支付银行卡                                    | 不可空  | CMB_CREDIT                       |
| feeType       | String  | 货币种类                                     | 不可空  | CNY                              |
| appid         | String  | 公众账号ID                                   | 可空   | wxf3a057928b881466               |
| totalFee      | Int     | 总金额，单位为分                                 | 可空   | 1                                |
| paymentTime   | Long    | 支付时间                                     | 可空   | 1478719613000                    |
| tradeType     | String  | 交易类型                                     | 可空   | JSAPI                            |
| attach        | String  | 商家数据包                                    | 不可空  |                                  |
| status        | Integer | 交易状态: 交易状态:订单交易状态  0-未支付 1-已支付,未提货 2.  已提货  3. 全额退款  4.部分退款  5.已取消  6.已关闭  9 已删除 | 不可空  |                                  |











## 支付成功回调通知

| 回调方式         | POST                            |
| ------------ | ------------------------------- |
| Content-type | application/json; charset=utf-8 |

### 回调报文示例

支付完成后，微信或支付宝会把相关支付结果发送给智慧图支付系统，校验无误后根据预下单参数notify_url地址，将支付结果推送给对接方(若无响应或网络不可达，累计推送三次，直到推送成功)。

**注意：同样的通知可能会多次发送给商户系统。商户系统必须能够正确处理重复的通知。**

```json
{
    "out_trade_no": "T0112016121220531807135686477564",
  	"transaction_id": "4006572001201612122604845330",
    "money": 25500,
    "openid": "oq1Gkt2dyq1bO1_TN2zeMj_XuvYI",
    "sign": "2CB5D045C939915D96EC8EAC4760014A",
    "result_code": "SUCCESS",
    "paymentTime": 1481547224000,
    "tradeType": "JSAPI",
    "IntType": 1
}
```

### 通用字段

| 参数             | 参数类型    | 参数说明                         | 是否为空 | 样例                                     |
| -------------- | ------- | ---------------------------- | ---- | -------------------------------------- |
| out_trade_no   | String  | 商户系统内部的订单号,32个字符内、可包含字母,     | 不可空  | T0022016091811444926537392894154       |
| transaction_id | String  | 微信流水单号                       | 不可空  | 4006572001201612122604845330           |
| money          | Integer | 金额：分                         | 不可空  | 10                                     |
| openid         | String  | 服务号openid                    | 不可空  | o9n4cw1tgvJXTqk-KiDkx-hyXA70           |
| sign           | String  | 参数签名，详见签名规则                  | 不可空  | 9D4606A48E179FBB9AC92A6F1C169EC0       |
| attach         | String  | JSON数据结构。附加数据，如车牌号、key_admin | 可空   | {"carNo":"京WJ9087","key_admin":"xxxx"} |
| paymentTime    | Long    | 支付时间，时间戳                     | 不可空  | 1479890291498                          |
| tradeType      | String  | 交易类型                         | 不可空  | JSAPI、APP                              |
| IntType        | Integer | 支付渠道                         | 不可空  | 1.微信 2.支付宝                             |

### 普通商户模式新增字段

| 参数          | 参数类型   | 参数说明     | 是否为空 | 样例                 |
| ----------- | ------ | -------- | ---- | ------------------ |
| appId       | String | 服务号appId | 不可空  | wxf3a057928b881466 |
| mchId       | String | 商户号      | 不可空  | 1234667902         |
| isSubscribe | String | 是否关注     | 可空   | Y                  |

### 受理模式新增字段

| 参数             | 参数类型   | 参数说明      | 是否为空 | 样例                 |
| -------------- | ------ | --------- | ---- | ------------------ |
| subAppId       | String | 子公众号appId | 不可空  | wxb5e69065eb3d67ce |
| subMchId       | String | 子商户号mchId | 不可空  | 1262120901         |
| subIsSubscribe | String | 子公众号是否关注  | 不可空  | N                  |

### 返回参数

```json
{
  "status": 200,			// 返回码
  "message": "success"		// 返回信息
}
```





## 附录-支付平台返回码

| 返回码  | 说明                   |
| ---- | -------------------- |
| 200  | 请求成功                 |
| 500  | 服务不可用,出现异常           |
| 514  | 非法的token票据           |
| 516  | 非法的签名                |
| 517  | 请求参数不能为空,详见message字段 |
| 519  | 重复数据                 |
| 520  | 暂无匹配到的请求类型或数据        |
| 521  | 非法的文件                |
| 522  | 无效的用户                |
| 523  | 操作失败                 |
| 524  | 暂不能操作                |
| 525  | 支付错误，详见message字段     |
