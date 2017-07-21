import utils from '../utils';
import { mustLogin } from 'wxlogin';
const conf = window.conf;
if (conf.key === 'e4273d13a384168962ee93a953b58ffd') {
  mustLogin();
}

// 搜索车辆
const searchCarRequest = () => ({
  type: 'SEARCHCARREQUEST',
});

const searchCarSuccess = data => ({
  type: 'SEARCHCARSUCCESS',
  data,
});

const searchCarError = data => ({
  type: 'SEARCHCARERROR',
  data,
});

export const searchCarList = (carno) => dispatch => {
  dispatch(searchCarRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/searchcar'),
    data: {
      carno,
      key_admin: conf.key,
    },
    isSuccessShow: true,
  }).then((data) => {
    dispatch(searchCarSuccess(data.data));
  }, (error) => {
    dispatch(searchCarError(error));
  });
};

// 会报错，action必须是纯对象
// export const onSelectCar = (carNo, carimg) => {
//   db.setItem('park_selected_carno', JSON.stringify({
//     carNo,
//     carimg,
//   }));
//   location.href = `/pay/park?key_admin=${conf.key}`;
// };
