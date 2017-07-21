const $ = window.$;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// 获取账户列表
export const getList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ChildAccount/getList`;
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

// 获取超管栏目
export const getColumnList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ChildAccount/getColumnList`;
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

// 子账号添加或者编辑
export const editAccountOne = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ChildAccount/editAccountOne`;
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

// 获取单个账号详情
export const getDetailById = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ChildAccount/getDetailById`;
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

// 获取该商户下所有buildid信息
export const getBuildidAll = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ChildAccount/getBuildidAll`;
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

// 子账户删除
export const delAccountOne = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/ChildAccount/delAccountOne`;
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
