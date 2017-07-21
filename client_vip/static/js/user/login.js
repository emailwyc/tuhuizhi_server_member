require('../../scss/user/login.scss');
import { apilogin } from '../model';
const $ = window.$;
require('../modules/cookie')($);
const storage = window.sessionStorage;

const login = {
  init() {
    this.initDom();
    this.initEvent();
    // const date = new Date();
    // console.log(date.setTime(date.getTime() + 60 * 1800));
  },
  initDom() {
    this.$form = $('#form');
    this.$loginname = $('.login_name');
    this.$loginpassword = $('.login_password');
    this.$loginbtn = $('.login_btn');
  },
  initEvent() {
    this.$form.on('submit', e => {
      e.preventDefault();
      const data = this.$form.serialize();
      console.log(data);
      apilogin(data).then((json) => {
        console.log(json);
        // const date = new Date();
        // const expires = new Date(date.getTime() + 30 * 60 * 1000);
        $.cookie('ukey', json.data.ukey, { path: '/' }); // , domain: 'vip.rtmap.com'
        $.cookie('name', json.data.name, { path: '/' });
        storage.setItem('childid', json.data.childid);
        location.href = '/welcome';
      }, (json) => {
        console.log(json);
        if (json.code === 500) {
          alert('用户名或密码错误');
        } else {
          alert(json.msg);
        }
      });
    });
  },

};
login.init();
