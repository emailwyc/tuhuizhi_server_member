import utils from '../utils';
const conf = window.conf;

// 购买优惠券列表
const buysRequest = () => ({
  type: 'BUYSREQUEST',
});

const buysSuccess = data => ({
  type: 'BUYSSUCCESS',
  data,
});

const buysError = () => ({
  type: 'BUYSERROR',
});

export const buysFun = () => dispatch => {
  dispatch(buysRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/getParkCouponType'),
    data: {
      key_admin: conf.key,
    },
    isSuccessShow: true,
  }).then((data) => {
    dispatch(buysSuccess(data.data));
  }, (error) => {
    dispatch(buysError(error));
  });
};
