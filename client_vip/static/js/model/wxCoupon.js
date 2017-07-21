
const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';

// 微信小程序列表
export const activitiesList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/activitiesList`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// 微信小程序活动添加
export const addActivity = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/addActivity`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// 微信小程序 活动编辑
export const editActivity = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/editActivity`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// 微信小程序-删除 Coupon/BackEnd/delActivity
export const delActivity = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/delActivity`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// 添加Banner图
export const addBanner = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/createMiniProgramBanner`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// banner列表
export const bannerList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/miniProgramBannerList`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// 删除banner列表
export const delbanner = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/miniProgramDelBanner`;
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
      },
      error: (json) => reject(json),
    });
  });
};
/* 支付明细列表 */
export const appletPayList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/pingxx_list`;
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
      },
      error: (json) => reject(json),
    });
  });
};

  /*  获取小程序title */
export const getAppletTitle = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/get_applet_title`;
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
      },
      error: (json) => reject(json),
    });
  });
};

/*  获取小程序title */
export const appletTitle = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/applet_title`;
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
      },
      error: (json) => reject(json),
    });
  });
};

/*  获取活动下的券列表 */
export const propertyList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/property_list`;
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
      },
      error: (json) => reject(json),
    });
  });
};

/*  编辑券排序 */
export const couponSave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/Coupon/BackEnd/coupon_save`;
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
      },
      error: (json) => reject(json),
    });
  });
};
