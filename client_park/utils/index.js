import './date';
import core from './core';
import qs from './qs';
import http from './http';
import loading from './loading';

const tools = {
  // 元转分
  transformFen: (val) => {
    const returnVal = (val / 100).toFixed(2);
    return returnVal;
  },
  // 获取url参数
  qs,
  // 核心库
  core,
  // 获取接口绝对路径
  api: (path) => {
    const orign = location.href.indexOf('h5.rtmap.com') > 0 ?
      'https://o2o.rtmap.com/park' : 'https://wumai.rtmap.com/park';
    return orign + path;
  },
  newApi: (path) => {
    const orign = location.href.indexOf('h5.rtmap.com') > 0 ?
      'http://groupon.rtmap.com/parking-web' : 'http://123.56.103.28/parking-web';
    return orign + path;
  },
  // 发送请求模块
  http: (cfg) => {
    // rewrite beforeSend
    const beforeSend = (xhr) => {
      tools.showLoading();
      if (typeof cfg.beforeSend === 'function') {
        cfg.beforeSend.call(this, xhr);
      }
    };
    // rewrite complete
    const complete = (xhr, state) => {
      tools.hideLoading();
      if (typeof cfg.complete === 'function') {
        cfg.complete.call(this, xhr, state);
      }
    };
    const _cfg = Object.assign({}, cfg, {
      beforeSend,
      complete,
    });
    return http(_cfg);
  },
  // 显示loading
  showLoading: () => {
    loading.show();
  },
  // 隐藏loading
  hideLoading: () => {
    loading.hide();
  },
};
// window.tools = tools;
export default tools;
