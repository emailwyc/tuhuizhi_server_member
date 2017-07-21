import utils from '../utils';
import { getUserInfo, mustLogin } from 'wxlogin';
const conf = window.conf;
if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
  mustLogin();
}

// 获取车位
const getRecordsRequest = () => ({
  type: 'GETRECORDSREQUEST',
});

const getRecordsSuccess = data => ({
  type: 'GETRECORDSSUCCESS',
  data,
});

const getRecordsError = () => ({
  type: 'GETRECORDSERROR',
});

export const getRecords = (page = '1') => dispatch => {
  dispatch(getRecordsRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/getParkOrderLists'),
    data: {
      page,
      key_admin: conf.key,
      openid: getUserInfo().openid,
    },
    isSuccessShow: true,
  }).then((json) => {
    const result = {};
    result.data = json.data;
    if (json.data.length < 10) {
      result.isMore = false;
    } else {
      result.isMore = true;
    }
    dispatch(getRecordsSuccess(result));
  }, () => {
    dispatch(getRecordsError());
  });
};
