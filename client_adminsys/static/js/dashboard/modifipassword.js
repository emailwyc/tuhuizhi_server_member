require('../../scss/dashboard/modifipassword.scss');
import { out } from '../modules/out.js';
import { savePwd } from './model';
const $ = window.$;
const conf = window.conf;
const savapwd = {
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
    this.$inputpassword = $('#inputPassword');
    this.$newpassword = $('#newPassword');
    this.$okpassword = $('#okPassword');
    this.$form = $('#form');
    this.$out = $('.out');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$form.on('submit', e => {
      e.preventDefault();
      if (this.$newpassword.val() === this.$okpassword.val()) {
        savePwd({
          ukey: $.cookie('ukey'),
          id: conf.id,
        }).then((json) => {
          console.log(json);
          alert('修改成功');
          // location.href = '/dashboard';
          location.reload();
        }, (json) => {
          console.log(json);
          // alert('原密码错误');
          // if (json.code === 1001) {
          //   alert('登录超时请重新登录');
          //   location.href = '/dashboard/login';
          // }
        });
      } else {
        console.log('密码不一致');
      }
    });
  },
};
savapwd.init();
