const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';

const apiFn = (sur, urls) => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    $.ajax({
      type: 'post',
      url: urls,
      data: sur,
      dataType,
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (data) => {
        if (data.code === 200) {
          resolve(data);
        } else {
          reject(data);
        }
        console.log(time);
      },
      error: (json) => reject(json),
    });
  });
};

// 活动劵添加更新
export const editClassOne = sur => apiFn(sur, `${apiPath}/MerAdmin/Coupon/editClassOne`);

// 获取单个活动劵
export const getOne = sur => apiFn(sur, `${apiPath}/MerAdmin/Coupon/getOne`);

// 活动劵删除
export const delTagsOne = sur => apiFn(sur, `${apiPath}/MerAdmin/Coupon/delTagsOne`);

// 活动劵发送
export const sendCoupon = sur => apiFn(sur, `${apiPath}/MerAdmin/Coupon/sendCoupon`);

// 活动劵列表
export const getCouponList = sur => apiFn(sur, `${apiPath}/MerAdmin/Coupon/getCouponList`);
