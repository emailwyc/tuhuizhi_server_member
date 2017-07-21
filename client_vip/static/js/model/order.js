const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';

// 开票
export const confirmInvoice = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Park/confirmInvoice`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'POST',
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
        // dt('api', { url, data, res, time: (+(new Date) - time) });
      },
      error: (json) => {
        reject(json);
      },
    });
  });
};


// getOrderList
export const getOrderList = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Park/getOrderList`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'POST',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          console.log(res);
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
        // dt('api', { url, data, res, time: (+(new Date) - time) });
      },
      error: (json) => {
        reject(json);
      },
    });
  });
};

// 会员优惠标准

export const designParkIntro = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Park/designParkIntro`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'POST',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          console.log(res);
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
        // dt('api', { url, data, res, time: (+(new Date) - time) });
      },
      error: (json) => {
        reject(json);
      },
    });
  });
};

// 获取优惠标准
export const getParkIntro = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/ParkApp/ParkPay//getParkIntro`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'POST',
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
        // dt('api', { url, data, res, time: (+(new Date) - time) });
      },
      error: (json) => {
        reject(json);
      },
    });
  });
};
