import utils from '../utils';
const conf = window.conf;
// const wx = window.wx;
import { getUserInfo, mustLogin } from 'wxlogin';
if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
  mustLogin();
}

// 购买优惠券列表
const activateRequest = () => ({
  type: 'ACTIVATEREQUEST',
});

const activateSuccess = data => ({
  type: 'ACTIVATESUCCESS',
  data,
});

const activateError = () => ({
  type: 'ACTIVATEERROR',
});

export const activateFun = (code) => dispatch => {
  dispatch(activateRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/activateParkCoupon'),
    data: {
      // key_admin: conf.key,
      active_code: code,
      openid: getUserInfo().openid,
      nickname: getUserInfo().nickname,
      headimgurl: getUserInfo().headimgurl,
    },
    isSuccessShow: true,
  }).then((data) => {
    dispatch(activateSuccess(data.data));
    location.href = `/park/activateopen?key_admin=${conf.key}&data=${
      encodeURIComponent(JSON.stringify(data.data))}`;
  }, (error) => {
    dispatch(activateError(error));
  });
};
