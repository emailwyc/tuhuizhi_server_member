const $ = window.$;

const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';

// 获取停车寻车配置接口
export const parkingfind = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ParkingCar/parking_find`;
    $.ajax({
      url,
      dataType: 'jsonp',
      data: params,
      type: 'post',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 停车寻车配置提交接口
export const parkingsave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ParkingCar/parking_save`;
    $.ajax({
      url,
      dataType: 'jsonp',
      data: params,
      type: 'post',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
