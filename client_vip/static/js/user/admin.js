require('../../scss/user/admin.scss');
import { modificarpwd, jurisdictionList } from '../model';
const $ = window.$;
const hogan = require('hogan.js');
const nav = require('../modules/navbar.html');
require('../modules/cookie')($);
import { out } from '../modules/out.js';
const admin = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      location.href = '/user/login';
      return;
    }
    // this.navbar();
  },
  initDom() {
    this.$out = $('.out');
    this.$form = $('#form');
    this.$newpwd = $('#new_pwd');
    this.$repwd = $('#re_pwd');
    this.$oldpwd = $('#oldpassword');
    this.$navbar = $('.navbar-nav');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$form.on('submit', (e) => {
      e.preventDefault();
      if (this.$newpwd.val() === this.$repwd.val()) {
        modificarpwd({
          key_admin: $.cookie('ukey'),
          pwd: this.$oldpwd.val(),
          new_pwd: this.$newpwd.val(),
        }).then(json => {
          console.log(json);
          alert('修改成功');
        }, json => {
          console.log(json);
          $('.msg').html(json.msg);
        });
      } else {
        console.log('密码错误');
        $('.msg').html('原密码和新密码不匹配');
      }
    });
  },
  navbar() {
    jurisdictionList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      const navBar = hogan.compile(nav);
      this.$navbar.html(navBar.render({ navbar: json.data }));
    }, json => {
      console.log(json);
    });
  },
};
admin.init();
