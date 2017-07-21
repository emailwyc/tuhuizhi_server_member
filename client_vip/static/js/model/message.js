const $ = window.$;
// const dt = window.dt;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';

// 1、消息列表
export const getList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/MessageList`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 2、新建消息
export const createMessage = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/CreateMessage`;
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
      },
      error: (json) => reject(json),
    });
  });
};


// 3、消息修改
export const editMessage = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/EditMessage`;
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
      },
      error: (json) => reject(json),
    });
  });
};
// 获取单个消息详细信息
export const getOneMessage = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/GetOneMessage`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 消息删除
export const delMessage = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/DelMessage`;
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
      },
      error: (json) => reject(json),
    });
  });
};


// 创建模板消息
export const createTemplate = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/CreateTemplate`;
    $.ajax({
      url,
      dataType: 'json',
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
      },
      error: (json) => reject(json),
    });
  });
};

// 模板消息列表
export const templateList = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/TemplateList`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 按模板id搜索模板信息
export const searchTemplateId = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/SearchTemplateId`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 获取一个模板消息
export const switchowTemplateInfo = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/ShowTemplateInfo`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 获取一个模板消息
export const delTemplate = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/DelTemplate`;
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
      },
      error: (json) => reject(json),
    });
  });
};

// 编辑模板消息
export const editTemplate = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/EditTemplate`;
    $.ajax({
      url,
      dataType: 'json',
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

// 模板消息推送
export const sendTemplateMessage = params => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/MerAdmin/WechatMessage/SendTemplateMessage`;
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
      },
      error: (json) => reject(json),
    });
  });
};
