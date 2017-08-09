// 新建帐号
const loading = require('rtloading');
require('../../scss/dashboard/createaccount.scss');
import { out } from '../modules/out.js';
import { addAdmin } from './model';
const validate = require('../modules/validate');
const $ = window.$;
const createaccount = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$username = $('#username');
    this.$password = $('#password');
    this.$okPassword = $('#okPassword');
    this.$prefix = $('#prefix');
    this.$describe = $('#describe');
    this.$form = $('#form');
    validate.init(this.$form);
    this.$out = $('.out');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$form.on('submit', e => {
      e.preventDefault();
      // const data = this.$form.serialize();
      // console.log(data);
      console.log($.cookie('ukey'));
      loading.show();
      if (this.$password.val() === this.$okPassword.val()) {
        addAdmin({
          ukey: $.cookie('ukey'),
          username: this.$username.val(),
          password: this.$password.val(),
          re_pwd: this.$okPassword.val(),
          pre_table: this.$prefix.val(),
          describe: this.$describe.val(),
        }).then((json) => {
          console.log(json);
          loading.hide();
          location.href = '/dashboard';
        }, json => {
          loading.hide();
          if (json.code === 2001) {
            alert('用户已存在');
            location.reload();
            return;
          }
          if (json.code === 1001) {
            alert('登录超时请重新登录');
            location.href = '/dashboard/login';
            return;
          }
          alert(json.msg);
        });
      } else {
        loading.hide();
        alert('密码不一致');
      }
      // setTimeout(() => {
      //   location.href = `http://www.baidu.com?${this.$form.serialize()}`;
      // }, 1000);

      // return true;
    });
  },
};
createaccount.init();
