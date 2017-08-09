const $ = window.$;
require('../../modules/cookie')($);
import { cookieTime } from '../../modules/cookieTime';
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('dashboard.rtmap.com') > 0 || location.href.indexOf('dashboardvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
// 测试地址  https://backend.rtmap.com
// 正式地址  http://mem.rtmap.com

// 部署时需要 在本地分支 sh ./bin/pub.sh
// const apiPath = 'https://mem.rtmap.com';

const apiFn = (params, url) => {
  const time = +(new Date);
  return new Promise((resolve, reject) => {
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

// 页面访问量统计列表
export const pagepvList = params => apiFn(params, `${apiPath}/DevAdmin/Member/pagepv_list`);

// 页面访问量统计数据
export const pagepvData = params => apiFn(params, `${apiPath}/DevAdmin/Member/pagepv_data`);

// 访问数据信息表_删除
export const pagepvListDel = params => apiFn(params, `${apiPath}/DevAdmin/Member/pagepv_list_del`);

// 插入&编辑
export const pagepvListAdd = params => apiFn(params, `${apiPath}/DevAdmin/Member/pagepv_list_add`);
