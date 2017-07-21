import utils from '../utils';
const conf = window.conf;
import { getUserInfo, mustLogin } from 'wxlogin';
if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
  mustLogin();
}

// 购买优惠券列表
const mycketRequest = () => ({
  type: 'MYCKETREQUEST',
});

const mycketSuccess = data => ({
  type: 'MYCKETSUCCESS',
  data,
});

const mycketError = () => ({
  type: 'MYCKETERROR',
});

export const myTicketFun = (status) => dispatch => {
  dispatch(mycketRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/getMyParkCoupon'),
    data: {
      key_admin: conf.key,
      status,
      openid: getUserInfo().openid,
    },
    isSuccessShow: true,
  }).then((data) => {
    dispatch(mycketSuccess(data));
  }, (error) => {
    dispatch(mycketError(error));
  });
};
