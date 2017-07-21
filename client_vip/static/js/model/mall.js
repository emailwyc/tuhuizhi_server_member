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

// 积分商城查询banner列表
export const bannerList = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/banner_list`);

// 积分商城获取单个banner信息
export const bannerFind = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/banner_find`);

// 积分商城banner添加和修改接口
export const bannerSave = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/banner_save`);

// 积分商城banner置顶接口
export const bannerUp = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/banner_up`);

// 积分商城删除banner接口
export const bannerDel = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/banner_del`);

// 获取建筑物列表
export const buildidList = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/builid_list`);

// 修改banner上线下线
export const bannerStatus = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/banner_status`);

// 获取活动ID
export const obtainAct = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/obtain_act`);

// 添加或修改活动ID
export const actAdd = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/act_add`);

// 查询所有奖品列表
export const integralList = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/integral_list`);

// 礼品上线下线接口
export const prizeOffline = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/prize_offline`);

// 查询奖品详细信息
export const integralListOnce = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/integral_list_once`);

// 修改或添加奖品信息
export const integralOperation = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/integral_operation`);

// 获取卡类别列表
export const cardTypeList = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/card_type_list`);

// 查询所有分类接口
export const integralTypeList = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/integral_type_list`);

// 获取颜色
export const getIntegralColor = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/get_integral_color`);

// 设置颜色
export const integralColorAdd = sur => apiFn(sur, `${apiPath}/MerAdmin/Scoremall/integral_color_add`);
