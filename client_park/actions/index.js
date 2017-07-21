import utils from '../utils';
import { mustLogin } from 'wxlogin';
mustLogin();
const conf = window.conf;

const db = localStorage;

// 获取车位
const getParkRequest = () => ({
  type: 'GETPARKREQUEST',
});

const getParkSuccess = data => ({
  type: 'GETPARKSUCCESS',
  data,
});

const getParkError = () => ({
  type: 'GETPARKERROR',
});

export const getPark = () => dispatch => {
  dispatch(getParkRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/getfreeparking'),
    data: {
      key_admin: conf.key,
    },
    isSuccessShow: true,
  }).then((result) => {
    db.setItem('localstorage', result.localstorage);
    const data = {
      data: result.data,
      logo: result.logo,
    };
    dispatch(getParkSuccess(data));
  }, () => {
    dispatch(getParkError());
  });
};
