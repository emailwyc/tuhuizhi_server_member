// 帐号详情
require('../../scss/dashboard/accountdetails.scss');
import { details } from './model';
const $ = window.$;
const conf = window.conf;
import { out } from '../modules/out.js';
const accountdetails = {
  init() {
    this.initDom();
    this.initEvent();
    this.details();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$out = $('.out');
    this.$name = $('.name');
    this.$pwd = $('.password');
    this.$pre = $('.pre');
    this.$keyadmin = $('.key_admin');
    this.$signkey = $('.sign_key');
    this.$qiy = $('.qiy');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
  },
  details() {
    details({
      ukey: $.cookie('ukey'),
      id: conf.id,
    }).then(json => {
      const modJson = json;
      if (json.enable === '1') {
        modJson.enable = '已启用';
      } else {
        modJson.enable = '未启用';
      }
      this.$name.html(`用户名：${json.name}`);
      this.$pwd.html('密码：******');
      this.$pre.html(`表前缀：${json.pre_table}`);
      this.$keyadmin.html(`key_admin：${json.ukey}`);
      this.$signkey.html(`sign_key：${json.signkey}`);
      this.$qiy.html(`是否启用：${json.enable}`);
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
accountdetails.init();
