const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://o2o.rtmap.com/' : 'https://wumai.rtmap.com';
const apiPathNew = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// const apiPathNew = 'https://mem.rtmap.com';
// const apiPath = 'https://o2o.rtmap.com/';

// 获取数据
export const getPayType = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/o2oPark/ParkApp/ParkBackend/getPayType`;
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
        console.log(res);
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

// 删除
export const deleteMemberFTConf = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/o2oPark/ParkApp/ParkBackend/deleteMemberFTConf`;
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
        console.log(res);
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

// 保存
export const changePayType = data => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/o2oPark/ParkApp/ParkBackend/changePayType`;
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
        console.log(res);
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


// 添加功能
export const setSquared = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Configure/SetSquared`;
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

// 获取功能列表
export const getSquaredList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Configure/GetSquaredList`;
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

// 删除功能
export const delSquared = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Configure/DelSquared`;
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

// 获取排序列表
export const getSquaredOrderNum = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Configure/GetSquaredOrderNum`;
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

// 获取单个功能列表信息
export const getOneSquared = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Configure/GetOneSquared`;
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

// 获取栏目模板列表
export const getColumnList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Configure/get_column_list`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 获取车辆关联配置
export const getCarRelationlimit = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Park/getCarRelationlimit`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 获取车辆关联配置
export const editCarRelationlimit = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPathNew}/MerAdmin/Park/editCarRelationlimit`;
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
      },
      error: (json) => reject(json),
    });
  });
};
