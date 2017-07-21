import utils from '../utils';
import { getUserInfo } from 'wxlogin';
// import { cancel } from '../models';
// const wx = window.wx;

const conf = window.conf;
const db = localStorage;

const chooseCarRequest = () => ({
  type: 'CHOOSECARREQUEST',
});

const chooseCarSuccess = data => ({
  type: 'CHOOSECARSUCCESS',
  data,
});

const chooseCarError = (data) => ({
  type: 'CHOOSECARERROR',
  data,
});

const orderRequest = () => ({
  type: 'ORDERREQUEST',
});
const orderSuccess = (data) => ({
  data,
  type: 'ORDERSUCCESS',
});
const orderError = (data) => ({
  type: 'ORDERERROR',
  data,
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

const setDefault = () => ({
  type: 'SETDEFAULT',
});

export const defaultPay = () => dispatch => {
  dispatch(setDefault());
};

export const getCarInfo = () => dispatch => {
  dispatch(chooseCarRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/choosecar'),
    data: {
      carno: JSON.parse(db.getItem(conf.key)).carNo || '',
      key_admin: conf.key,
      openid: getUserInfo().openid,
    },
    isSuccessShow: true,
  }).then((result) => {
    // PayValue 应会金额 MoneyValue 实付金额 discountValue 优惠金额
    // discount 折扣 100是无折扣 80是八折
    dispatch(chooseCarSuccess(result.data));
  }, (error) => {
    dispatch(chooseCarError(error));
    if (error.code === 400) {
      location.href = `/park?key_admin=${conf.key}`;
    }
  });
};

// 木有调通jssdk这种方式
// const toWxPay = (data) => new Promise((resolve, reject) => {
//   alert(JSON.stringify(data));
//   wx.chooseWXPay({
//     timeStamp: data.timeStamp,
//      // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
//     nonceStr: data.nonceStr, // 支付签名随机串，不长于 32 位
//     package: data.package, // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
//     signType: data.signType, // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
//     paySign: data.paySign, // 支付签名
//
//     success: res => {
//         // 支付成功后的回调函数
//       resolve(res);
//     },
//     fail: res => {
//       reject(res);
//     },
//     cancel: res => {
//       reject(res);
//     },
//   });
// });

const cancelPayRequest = () => ({
  type: 'CANCELREQUEST',
});
const cancelPaySuccess = (data) => ({
  data,
  type: 'CANCELSUCCESS',
});
const cancelPayError = (data) => ({
  type: 'CANCELERROR',
  data,
});

export const cancelPay = (orderId) => dispatch => {
  dispatch(cancelPayRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/unlock'),
    data: {
      orderId,
    },
    isSuccessShow: true,
  }).then((result) => {
    dispatch(cancelPaySuccess(result.data));
    dispatch(payError());
  }, (error) => {
    alert(JSON.stringify(error));
    dispatch(cancelPayError(error));
  });
};

const toWxPay = (data) => new Promise((resolve) => {
  const onBridgeReady = () => {
    window.WeixinJSBridge.invoke(
      'getBrandWCPayRequest', {
        appId: data.appId,     // 公众号名称，由商户传入
        timeStamp: data.timeStamp,         // 时间戳，自1970年以来的秒数
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

export const wxPayDo = (data) => dispatch => {
  toWxPay(data).then((result) => {
    dispatch(payRequest());
    if (result.err_msg === 'get_brand_wcpay_request:ok') {
      if (db.getItem('localstorage') === '1') {
        db.setItem(`${conf.key}park_mycars`,
          JSON.stringify([{ CarSerialNo: JSON.parse(db.getItem(conf.key)).carNo,
           carimg: JSON.parse(db.getItem(conf.key)).carimg },
        ]));
      } else {
        db.setItem(`${conf.key}park_mycars`,
          JSON.stringify([]));
      }
      if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
        location.href = `/park/buysuccess?key_admin=${conf.key}&data=${
          encodeURIComponent(JSON.stringify(data))}`;
      } else {
        dispatch(paySuccess(result));
      }
    } else {
      dispatch(cancelPay(data.orderId));
    }
  });
};

export const wxPay = (isActive, num) => dispatch => {
  dispatch(orderRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/paybyweixin'),
    data: {
      carno: JSON.parse(db.getItem(conf.key)).carNo || '',
      key_admin: conf.key,
      openid: getUserInfo().openid,
      use_coupon: isActive === 'active' ? 1 : 2,
    },
    isSuccessShow: true,
  }).then((result) => {
    dispatch(orderSuccess(result.data));
    if (result.data.total_fee !== 0) {
      dispatch(wxPayDo(result.data, num));
    } else {
      if (db.getItem('localstorage') === '1') {
        db.setItem(`${conf.key}park_mycars`,
          JSON.stringify([{ CarSerialNo: JSON.parse(db.getItem(conf.key)).carNo,
           carimg: JSON.parse(db.getItem(conf.key)).carimg },
        ]));
      } else {
        db.setItem(`${conf.key}park_mycars`,
          JSON.stringify([]));
      }
      if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
        location.href = `/park/buysuccess?key_admin=${conf.key}&data=${
          encodeURIComponent(JSON.stringify(result.data))}`;
      } else {
        dispatch(paySuccess(result));
      }
    }
  }, (error) => {
    dispatch(orderError(error));
  });
};


// 积分
const pointRequest = () => ({
  type: 'POINTREQUEST',
});
const pointSuccess = (data) => ({
  data,
  type: 'POINTSUCCESS',
});
const pointError = (data) => ({
  type: 'POINTERROR',
  data,
});

// 几分支付
const pointPay = (orderno) => dispatch => {
  dispatch(payRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/paybyscore'),
    data: {
      carno: JSON.parse(db.getItem(conf.key)).carNo || '',
      key_admin: conf.key,
      openid: getUserInfo().openid,
      orderno,
    },
    isSuccessShow: true,
  }).then((result) => {
    if (db.getItem('localstorage') === '1') {
      db.setItem(`${conf.key}park_mycars`,
        JSON.stringify([{ CarSerialNo: JSON.parse(db.getItem(conf.key)).carNo,
         carimg: JSON.parse(db.getItem(conf.key)).carimg },
      ]));
    } else {
      db.setItem(`${conf.key}park_mycars`,
        JSON.stringify([]));
    }
    if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
      db.setItem(`${conf.key}park_mycars`,
        JSON.stringify([]));
      location.href = `/park/buysuccess?key_admin=${conf.key}&data=${
        encodeURIComponent(JSON.stringify(result.data))}`;
    } else {
      dispatch(paySuccess(result.data));
    }
  }, () => {
    dispatch(payError());
  });
};

// 积分下单
export const pointOrder = () => dispatch => {
  dispatch(pointRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/cscoreorder'),
    data: {
      carno: JSON.parse(db.getItem(conf.key)).carNo || '',
      key_admin: conf.key,
      openid: getUserInfo().openid,
    },
    isSuccessShow: true,
  }).then((result) => {
    dispatch(pointSuccess(result.data));
    dispatch(pointPay(result.data.orderNo));
  }, (error) => {
    dispatch(pointError(error));
  });
};
