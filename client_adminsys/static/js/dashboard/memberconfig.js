// 会员配置
require('../../scss/dashboard/memberconfig.scss');
import { out } from '../modules/out.js';
import { getApiList } from './model';
const hogan = require('hogan.js');
const tplGetApiList = require('./tpls/getapilist.html');
const $ = window.$;
const memberconfig = {
  init() {
    this.initDom();
    this.initEvent();
    this.getApiList();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$out = $('.out');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
  },
  getApiList() {
    getApiList({
      ukey: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      const apiList = hogan.compile(tplGetApiList);
      this.$tbody.html(apiList.render({ list: json }));
    }, json => {
      if (json.code === 502 || json.code === 1001) {
        alert('登录超时');
        location.href = '/dashboard/login';
      }
    });
  },
};
memberconfig.init();
