const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// 转赠积分列表
export const intergalLIst = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ScoreTransfer/scoretransferlist`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'get',
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
export const setTing = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ScoreTransfer/setting`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'get',
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

export const getSetTing = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ScoreTransfer/getsetting`;
    $.ajax({
      dataType,
      data,
      url,
      type: 'get',
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
