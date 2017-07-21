const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// const apiPath = 'https://mem.rtmap.com';

// 获取评价分类列表
export const getClassList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getClassList`;
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

// 获取分类所有
export const getClassAll = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getClassAll`;
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

// 获取标签所有
export const getTagsAll = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getTagsAll`;
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

// 获取分类单条
export const getClassOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getClassOne`;
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

// 编辑添加分类
export const editClassOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/editClassOne`;
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

// 删除评价分类
export const delClassOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/delClassOne`;
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

// 获取标签列表
export const getTagsList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getTagsList`;
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

// 编辑标签
export const editTagsOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/editTagsOne`;
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

// 获取单条标签
export const getTagsOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getTagsOne`;
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

// 删除标签
export const delTagsOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/delTagsOne`;
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

// 获取员工列表
export const getStaffList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getStaffList`;
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

// 获取单条员工
export const getStaffOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getStaffOne`;
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

// 编辑员工
export const editStaffOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/editStaffOne`;
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

// 删除员工
export const delStaffOne = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/delStaffOne`;
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

// 获取员工的评价
export const getEvalList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Evaluate/getEvalList`;
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
