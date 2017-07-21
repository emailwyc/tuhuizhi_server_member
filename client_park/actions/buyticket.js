import utils from '../utils';
const conf = window.conf;
import { getUserInfo, mustLogin } from 'wxlogin';
if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
  mustLogin();
}

// 购买优惠券列表
const buyRequest = () => ({
  type: 'BUYREQUEST',
});

const buySuccess = data => ({
  type: 'BUYSUCCESS',
  data,
});

const buyError = () => ({
  type: 'BUYERROR',
});

export const buyTicketInfoFun = (id) => dispatch => {
  dispatch(buyRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/getParkCouponDetails'),
    data: {
      prize_id: id,
    },
    isSuccessShow: true,
  }).then((data) => {
    dispatch(buySuccess(data.data));
  }, (error) => {
    dispatch(buyError(error));
  });
};


// 下订单

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

// const setDefault = () => ({
//   type: 'SETDEFAULT',
// });
//
// export const defaultPay = () => dispatch => {
//   dispatch(setDefault());
// };

export const createOrder = (data) => dispatch => {
  dispatch(orderRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/createCouponOrder'),
    data: {
      prize_id: data.prize_id,
      num: data.num,
      key_admin: conf.key,
      openid: getUserInfo().openid,
      uname: getUserInfo().nickname,
    },
    isSuccessShow: true,
  }).then((result) => {
    dispatch(orderSuccess(result.data));
    location.href = `/pay/cashierdesk?key_admin=${conf.key}&data=${
      encodeURIComponent(JSON.stringify(data))}&orders=${
        encodeURIComponent(JSON.stringify(result.data))}`;
  }, (error) => {
    dispatch(orderError(error));
  });
};
