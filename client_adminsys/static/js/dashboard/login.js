require('../../scss/dashboard/login.scss');
const loading = require('rtloading');
// const validate = require('../modules/validate');
const $ = window.$;
import { apiLogin } from './model';
import { cookieTime } from '../modules/cookieTime';
const login = {
  init() {
    this.initDom();
    this.initEvent();
  },
  initDom() {
    this.$inputEmail = $('#inputEmail');
    this.$inputPassword = $('#inputPassword');
    this.$form = $('#form');
    // validate.init(this.$form);
  },
  initEvent() {
    this.$form.on('submit', e => {
      e.preventDefault();
      loading.show();
      const data = this.$form.serialize();
      const da = data;
      apiLogin(da).then((json) => {
        cookieTime(json.ukey);
        location.href = '/dashboard';
      }, (json) => {
        console.log(json);
        console.log('登录失败。');
        alert(json.msg);
        loading.hide();
        if (json.code === 500) {
          alert(json.msg);
          return;
        }
        if (json.code === 1030) {
          alert('用户名或密码不能为空，请重新输入');
          return;
        }
      });
    });
  },
};
login.init();
