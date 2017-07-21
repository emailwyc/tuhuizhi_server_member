const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// 登录接口
export const apilogin = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Index/login`;
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

// 修改密码
export const modificarpwd = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Index/Modificar_pwd`;
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

// 查询奖品详细信息
export const girtInfo = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_list_once`;
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
// 查询所有奖品列表
export const integralList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_list`;
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

// 查询所有分类接口
export const integralTypeList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_type_list`;
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

// 删除分类接口
export const integralDel = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_type_del`;
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

// 添加或修改活动ID
export const actAdd = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/act_add`;
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

// 获取活动id
export const obtainact = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/obtain_act`;
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

// 修改或添加分类接口
export const integralTypeSave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_type_save`;
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

// 修改或添加奖品信息
export const modifyAdd = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_operation`;
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

export const jurisdictionList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Index/jurisdiction_list`;
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

// 获取七牛上传token
export const getUploadToken = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = 'https://mem.rtmap.com/PublicApi/Qiniu/get_upload_token';
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

export const memberTermsOne = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/member_terms_one`;
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

export const memberTerms = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/member_terms`;
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

export const getManualList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/get_manual_list`;
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

// 会员手册/会员权益上移下移接口
export const mamnualSortUp = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/manual_sort_up`;
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

// 会员手册/会员权益下移接口
export const mamnualSortDown = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/manual_sort_down`;
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

// 会员手册/会员权益下移接口
export const manualContentOne = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/manual_content_one`;
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

// 会员手册/会员权益添加与修改
export const manualSave = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/manual_save`;
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

// 会员手册/会员权益物理删除
export const manualDel = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/manual_del`;
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

// 会员手册/会员权益物理删除
export const wechatFieldList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/wechat_field_list`;
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

export const getArea = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/getarea`;
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

// http://backend.rtmap.com/MerAdmin/Wechatconfig/wechat_field

export const regForm = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/createMember`;
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
// 查询会员权益接口
export const memberRights = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/staticpagedetails`;
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
// 添加会员权益接口
export const staticpage = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/staticpage`;
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

// dw
// const VIP_PRE_URL = 'https://backend.rtmap.com/MerAdmin';
// const QIUNIU = 'https://backend.rtmap.com/PublicApi';
// const PUB_PRE_URL = 'https://mem.rtmap.com/PublicApi';
// console.log(PUB_PRE_URL);
// require('../modules/cookie')($);
// export const notLog = arg => typeof arg;
// const DATETYPE = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
// const common = (path, params, method) => {
//   const time = +(new Date);
//   notLog(time);
//   const _params = params || {};
//   return new Promise((resolve, reject) => {
//     const url = path;
//     _params.key_admin = $.cookie('ukey');
//     // console.log('_params', _params);
//     $.ajax({
//       url,
//       data: _params,
//       type: method || 'get',
//       xhrFields: {
//         withCredentials: true,
//       },
//       crossDomain: true,
//       cache: true,
//       dataType: DATETYPE,
//       success: (res) => {
//         if (res.code === 200) {
//           resolve(res.data);
//         } else {
//           // alert(res.msg);
//           reject(res);
//         }
//         // dt('api', { url, params, res, time: (+(new Date) - time) });
//       },
//       error: (json) => reject(json),
//     });
//   });
// };
// notLog(common);
// export const memberRights = {
//   get: common(`${VIP_PRE_URL}/Configure/staticpagedetails`, { tid: 1 }),
//   update: params => {
//     const _params = params;
//     _params.tid = 1;
//     return common(`${VIP_PRE_URL}/Configure/staticpage`, _params, 'post');
//   },
// };
// export const pubApi = {
//   // ${PUB_PRE_URL} 正式
//   getQinNiuToken: common(`${QIUNIU}/Qiniu/get_upload_token`),
// };

// 获取所有未审核列表
export const integrallist = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/IntegralMake/integral_list`;
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

// 获取所有门店
export const getStore = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/IntegralMake/integral_getstore`;
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

export const getintegralone = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/IntegralMake/get_integral_one`;
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

export const integraltype = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/IntegralMake/integral_type`;
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


// 会员管理 列表
// https://backend.rtmap.com/MerAdmin/Member/Lists

export const lists = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/Lists`;
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

export const searchList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/user_lists`;
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

// 获取会员单条信息
export const userdetails = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/showMember`;
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


// 会员修改接口
export const editMember = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/editMember`;
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

// 会员删除接口
export const delMember = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/real_delMember`;
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

// 积分明细--根据卡号查询
export const scoreDetailed = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/score_Detailed`;
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

// 积分记录--删除
export const scoreDel = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/score_del`;
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

// 积分记录--删除
export const renewScore = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/renew_score`;
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
// 积分记录--删除
export const memScord = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/mem_scord`;
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
// 微信客服页面--返回是否开启
export const contactustType = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/contactus_type`;
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

// 会员下载导出
export const memberExport = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/member_export`;
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

// 微信客服页面配置 添加/修改
export const contactus = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/contactus`;
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
// 微信页面配置--上传客服单条二维码
export const contactusCode = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/contactus_code`;
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

// 获取意见反馈列表

export const feedback = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/member_feedback`;
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

// 删除意见反馈
export const feedbackDel = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/member_feedback_del`;
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

// 删除意见反馈
export const contactusDel = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/contactus_del`;
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

// 添加积分录入信息
export const integralSave = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/IntegralMake/integral_save`;
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
// 积分扣减
export const scoreSub = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/score_sub`;
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

// 积分增加接口
export const addScore = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/score_add`;
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
// 获取所有积分原由列表
export const reasonList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/reason_list`;
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
// 添加会员卡样接口
export const vipCard = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/addvipcardtpl`;
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
// 删除和修改卡样接口
export const delOrsave = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/savevipcardtpl`;
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

// icons 添加
export const setSquared = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/SetSquared`;
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

// icons 获取列表
export const getSquaredList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetSquaredList`;
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

// icons 获取列表
export const delSquared = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/DelSquared`;
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

// icons 获取列表
export const getSquaredOrderNum = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetSquaredOrderNum`;
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

// icons 配置商户C端功能列表样式
export const setShow = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/SetShow`;
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
// icons 获取ICONS呈现方式 目前 list 和 九宫格 值 ： 1，2
export const getShow = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetShow`;
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

// 获取单个功能列表信息
export const getOneSquared = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetOneSquared`;
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

// 修改是否只能修改一次身份证号和生日
export const setChangeConfig = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/SetChangeConfig`;
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

// 获取是否只能修改一次身份证号和生日
export const getChangeConfig = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetChangeConfig`;
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


// 获取静态资源列表
export const getStaticList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/getStaticList`;
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

// 添加或修改兑换状态和提示语句
export const integralsave = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_save`;
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

// 查看积分兑换状态
export const integralstatus = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/integral_status`;
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

// 会员卡样等级列表
export const memberLevelList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/MemberLevelList`;
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

// 添加会员卡样配置
export const createMemberLevel = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/CreateMemberLevel`;
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

// 删除会员卡样配置 DelCardLevel
export const delCardLevel = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/DelCardLevel`;
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

// 是否赠送积分设置
export const giveScore = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/GiveScore`;
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

// 搜索兑奖记录
export const getprizesearch = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/get_prize_search`;
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

// 获取活动ID
export const getid = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/obtain_act`;
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
// 获取会员卡等级
export const memcardlist = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Scoremall/MemberLevelList`;
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

// 获取全部积分列表
export const getScoreList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/GetScoreList`;
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
// 赠送积分设置初始化
export const getGiveScoreSetting = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/GetGiveScoreSetting`;
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

// 意见反馈回复列表
export const getReplyList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/FeedbackReplayList`;
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

// 意见反馈回复接口
export const replyFeedback = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Wechatconfig/ReplayFeedback`;
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

// 获取子栏目模板列表
export const getColumnList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/get_column_list`;
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
// 卡样等级编辑接口
export const editCardLevel = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Member/EditCardLevel`;
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

// 获取注册表单列表
export const formList = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/FormList`;
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

// 设置表单列表属性
export const setMyAutoForm = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/SetMyAutoForm`;
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

// 获取签到积分配置
export const getSignSetting = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetSignSetting`;
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

// 签到积分配置
export const setSignScoreRule = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/SetSignScoreRule`;
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
// 签到积分配置
export const getSignSettingImg = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/GetSignSettingImg`;
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

// 获取会员卡样（新）
export const getMemberCode = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/get_member_code`;
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

// 修改会员卡样(新)
export const saveMemberCode = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/save_member_code`;
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

// 获取单个会员卡样详细信息
export const onceMemberCode = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/once_member_code`;
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

// 删除会员卡样
export const deleteMemberCode = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/Configure/delete_member_code`;
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

// 页面统计数据
export const pagepvData = params => {
  const time = + (new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/DevAdmin/Member/pagepv_data`;
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
