const $ = window.$;
// const dt = window.dt;
const apiPath = location.href.indexOf('h5.rtmap.com') > 0 ?
  'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// 会员信息接口
export const lookcar = params => {
  console.log(1);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/ParkApp/ParkPay/get_ParkingNo`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType: 'jsonp',
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        // dt('api', { params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 绑定会员卡
export const bindCard = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Member/Member/bindCard`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType:'jsonp',
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 获取验证码
export const sendMsg = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Member/Member/sendMsg`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType: 'jsonp',
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

export const getuserinfo = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Member/Member/getuserinfo`;
    // alert(url);
    // alert(params.openid);
    // alert(params.key_admin);
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType:'jsonp',
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 签到
export const checkin = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Sign/Go/do_sign`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType:'jsonp',
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};
// 是否已签到
export const checkSigned = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Sign/Go/check_signed`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType:'jsonp',
      success: (res) => {
        resolve(res);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 卡样
export const cardimg = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Member/Member/cardimg`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType:'jsonp',
      success: (res) => {
        resolve(res);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};
// 获取icon菜单

export const geticonlist = params => {
  const time = +(new Date);
  console.log(time);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Member/Member/getSquaredMenuList`;
    $.ajax({
      url,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      dataType:'jsonp',
      success: (res) => {
        resolve(res);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};
