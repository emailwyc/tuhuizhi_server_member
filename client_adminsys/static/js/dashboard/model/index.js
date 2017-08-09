const $ = window.$;
require('../../modules/cookie')($);
import { cookieTime } from '../../modules/cookieTime';
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('dashboard.rtmap.com') > 0 || location.href.indexOf('dashboardvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// 测试地址  https://backend.rtmap.com
// 正式地址  http://mem.rtmap.com

// 部署时需要 在本地分支 sh ./bin/pub.sh
// const apiPath = 'https://mem.rtmap.com';
// 登录
export const apiLogin = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Index/login`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          // cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 帐号详情
export const details = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/record`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 新建帐号
export const addAdmin = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/add_admin`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 接口配置
export const apiConFig = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/apiconfig`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};


// 获取单条接口配置
export const apiConFigOne = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/apiconfig_one`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 询建筑物下所有的接口
export const apiType = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/apitype`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 启用
export const enable = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/enable`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 获取所有接口列表
export const getApiList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/getApiList`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 获取当前api现有的keys
export const getRequestKey = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/getRequestKey`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 获取当前api返回key
export const getResponseKey = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/getResponseKey`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 配置api的请求keys
export const setRequestKey = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/setRequestKey`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 配置api返回key
export const setResponseKey = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/setResponseKey`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 商场列表
export const adminList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/admin_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 帐号修改
export const modification = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/modification`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

// 保存密码
export const savePwd = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/save_pwd`;
    $.ajax({
      url,
      dataType,
      data: params,
      Type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => reject(json),
    });
  });
};

export const weiList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/wei_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json.data);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 权限列表
export const jurisdictionList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/jurisdiction_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 配置商户下栏目
export const adminJurisdiction = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Integral/Integral/admin_jurisdiction`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 修改权限

export const jurisdictionSave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/jurisdiction_save`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 添加权限

export const jurisdictionadd = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/jurisdiction_add`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改或添加权限
export const jurisdictionOne = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/jurisdiction_one`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 查询商户下配置的栏目
export const conFigColumnList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/admin_jurisdiction_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 配置商户下栏目
export const conFigColumn = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/admin_jurisdiction`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'post',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 获取appid和建筑物id
export const getBuildAndAppid = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/getBuildAndAppid`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 修改appid和建筑物id
export const editBuildAndAppid = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}//DevAdmin/Member/editBuildAndAppid`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 获取版本
export const getversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Version/getList`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 添加版本
export const addversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Version/create`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 更新
export const upversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Version/update`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 删除
export const delversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Version/del`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

export const setversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Version/updateAdminSetting`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

export const getshopversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Version/getAdminSetting`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 获取子账户列表 all
export const getPayChildList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Merchant/getPayChildList`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改支付子账户
export const updatePayChild = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Merchant/updatePayChild`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 获取单个支付子账号详情
export const getPayChildById = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Merchant/getPayChildById`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 创建支付子账号
export const createPayChild = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Merchant/createPayChild`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 创建支付子账号
export const delPayChild = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Merchant/delPayChild`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 添加功能
export const cataloginsert = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_insert`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 获取版本列表
export const cataloglist = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 启用或禁用某个功能
export const catalogstatus = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_status`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 获取某个功能下面的版本
export const catalogversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_version`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 添加版本
export const versioninsert = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/version_insert`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改版本
export const versiononcesave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/version_once_save`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 修改版本状态
export const versiononcestatus = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/version_once_status`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 获取开启功能列表
export const catalogliststatus = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_list_status`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 获取某个功能下开启的版本列表
export const catalogversiontwo = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_version_two`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 获取商户配置的版本信息
export const catalogversionlist = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_version_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改商户配置版本接口
export const adminversion = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/admin_version`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改商户配置版本接口
export const catalogsave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/catalog_save`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 添加子栏目
export const subcolumninsert = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/sub_column_insert`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 获取子栏目列表
export const subcolumnlist = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/sub_column_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改子栏目
export const subcolumnsave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/sub_column_save`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改子栏目状态
export const subcolumnonce = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/sub_column_once`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 查询版本配置后子栏目列表（url）
export const versioncolumnlist = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/version_column_list`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 修改单个子栏目（url）
export const versioncolumnsave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/version_column_save`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 删除子栏目（url）
export const versioncolumndel = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/version_column_del`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

export const getMsgSign = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/SendMessage/getMsgSign`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        console.log(json);
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 商户列表选择
export const getUnMsgSign = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/SendMessage/getUnMsgSign`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        console.log(json);
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 商户添加
export const addMsgSign = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/SendMessage/addMsgSign`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        console.log(json);
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 通过id获取商户
export const getSignById = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/SendMessage/getSignById`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        console.log(json);
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 通过id获取商户
export const editMsgSign = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/SendMessage/editMsgSign`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        console.log(json);
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 通过id获取商户
export const delMsgSign = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/SendMessage/delMsgSign`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        console.log(json);
        if (json.code === 200) {
          cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 获取会员配置=>表单列表
export const formList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/AutoForm/FormList`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        // console.log(json);
        if (json.code === 200) {
          // cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 会员配置=>新建表单
export const createForm = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/AutoForm/CreateForm`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        // console.log(json);
        if (json.code === 200) {
          // cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
// 会员配置=>删除formlist
export const deleteForm = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/AutoForm/DeleteForm`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'get',
      xhrFields: {
        withCredentials: true,
      },
      crossDomin: true,
      success: (json) => {
        // console.log(json);
        if (json.code === 200) {
          // cookieTime(params.ukey);
          resolve(json);
        } else {
          reject(json);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};
