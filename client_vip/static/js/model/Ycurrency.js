const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';

// Y币查询所有奖品列表
export const prizeList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/integral_list`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币获取活动ID
export const obtainAct = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/obtain_act`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币添加或修改活动ID
export const actAdd = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/act_add`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币修改或添加banner接口
export const bannerSave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/banner_save`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币查询所有banner接口
export const bannerList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/banner_list`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币删除banner接口
export const bannerDel = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/banner_del`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币获取单个banner接口
export const bannerFind = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/banner_find`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};
// Y币获取单个banner接口
export const bannerUp = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/banner_up`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// Y币获取单个banner接口
export const integralOperation = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/integral_operation`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 获取Y币商城兑换记录接口
export const getPrizeSearch = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Ycoinaction/get_prize_search`;
    $.ajax({
      url,
      dataType,
      data: params,
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
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};
