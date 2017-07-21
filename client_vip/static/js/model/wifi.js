const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// const apiPath = 'https://mem.rtmap.com';

// 获取wifi数据
export const getwifi = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/getwifi`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'post',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        console.log(res);
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 修改wifi数据
export const confwifi = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/confwifi`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'post',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        console.log(res);
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// wifi配置
export const confmemwifi = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/confmemwifi`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'post',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        console.log(res);
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};
