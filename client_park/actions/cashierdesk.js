import utils from '../utils';
const conf = window.conf;
import { getUserInfo } from 'wxlogin';

const getDRequest = () => ({
  type: 'GETDREQUEST',
});
const getDSuccess = (data) => ({
  data,
  type: 'GETDSUCCESS',
});
const getDError = () => ({
  type: 'GETDERROR',
});

const payRequest = () => ({
  type: 'PAYREQUEST',
});
const paySuccess = (data) => ({
  data,
  type: 'PAYSUCCESS',
});
const payError = (data) => ({
  type: 'PAYERROR',
  data,
});

const toWxPay = (data) => new Promise((resolve) => {
  const onBridgeReady = () => {
    window.WeixinJSBridge.invoke(
      'getBrandWCPayRequest', {
        appId: data.appId,     // 公众号名称，由商户传入
        timeStamp: `${data.timeStamp}`,         // 时间戳，自1970年以来的秒数
        nonceStr: data.nonceStr, // 随机串
        package: data.package,
        signType: data.signType,         // 微信签名方式：
        paySign: data.paySign, //微信签名
      },
      res => {
        resolve(res);
      }
    );
  };
  if (typeof WeixinJSBridge === 'undefined') {
    if (document.addEventListener) {
      document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    } else if (document.attachEvent) {
      document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
      document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
    }
  } else {
    onBridgeReady();
  }
});

const wxPayDo = (data, orderItemId) => dispatch => {
  toWxPay(data).then((result) => {
    dispatch(payRequest());
    if (result.err_msg === 'get_brand_wcpay_request:ok') {
      dispatch(paySuccess(result));
      location.href = `/park/buysuccess?key_admin=${conf.key}&id=${orderItemId}&data=${
        encodeURIComponent(JSON.stringify(data))}`;
    } else {
      dispatch(payError(result));
      location.href = `/park/buyerror?key_admin=${conf.key}`;
    }
  });
};

export const payFun = (orderInfo) => dispatch => {
  dispatch(getDRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/buyParkCoupon'),
    data: {
      prize_id: orderInfo.prize_id,
      num: orderInfo.num,
      key_admin: conf.key,
      openid: getUserInfo().openid,
      order_id: orderInfo.order_id,
    },
    isSuccessShow: true,
  }).then((result) => {
    dispatch(getDSuccess(result));
    dispatch(wxPayDo(result.data, orderInfo.orderItemId[0]));
  }, (error) => {
    dispatch(getDError(error));
  });
};
