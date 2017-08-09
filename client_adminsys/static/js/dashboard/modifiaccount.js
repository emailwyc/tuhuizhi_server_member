// 帐号修改
require('../../scss/dashboard/modifiaccount.scss');
import { out } from '../modules/out.js';
import { modification, details } from './model';
const validate = require('../modules/validate');
const $ = window.$;
const conf = window.conf;
const modufyaccount = {
  init() {
    this.initDom();
    this.initEvent();
    this.getdetails();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$username = $('#username');
    this.$pretable = $('#pretable');
    this.$describe = $('#describe');
    this.$keyadmin = $('.key_admin');
    this.$signkey = $('.sign_key');
    this.$form = $('#form');
    this.$out = $('.out');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$form.on('submit', e => {
      e.preventDefault();
      // const data = this.$form.serialize();
      modification({
        ukey: $.cookie('ukey'),
        username: this.$username.val(),
        pre_table: this.$pretable.val(),
        describe: this.$describe.val(),
        id: conf.id,
        // data,
      }).then(json => {
        console.log(json);
        location.href = '/dashboard';
      }, json => {
        console.log(json);
        if (json.code === 1001) {
          alert('登录超时请重新登录');
          location.href = '/dashboard/login';
        }
        console.log('修改失败');
      });
    });
  },
  getdetails() {
    details({
      ukey: '202cb962ac59075b964b07152d234b70',
      id: conf.id,
    }).then(json => {
      const modJson = json;
      if (json.enable === '1') {
        modJson.enable = '已启用';
      } else {
        modJson.enable = '未启用';
      }
      this.$username.val(json.name);
      this.$pretable.val(json.pre_table);
      this.$describe.val(json.describe);
      this.$keyadmin.html(`key_admin：  ${json.ukey}`);
      this.$signkey.html(`sign_key：  ${json.signkey}`);
      validate.init(this.$form);
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
modufyaccount.init();
