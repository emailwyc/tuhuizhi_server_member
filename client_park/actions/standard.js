import utils from '../utils';
const conf = window.conf;

// 获取车位
const getInfoRequest = () => ({
  type: 'GETINFOREQUEST',
});

const getInfoSuccess = data => ({
  type: 'GETINFOSUCCESS',
  data,
});

const getInfoError = () => ({
  type: 'GETINFOERROR',
});

export const getInfo = () => dispatch => {
  dispatch(getInfoRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/getParkIntro'),
    data: {
      key_admin: conf.key,
    },
    isSuccessShow: true,
  }).then((result) => {
    dispatch(getInfoSuccess(result.data));
  }, () => {
    dispatch(getInfoError());
  });
};
