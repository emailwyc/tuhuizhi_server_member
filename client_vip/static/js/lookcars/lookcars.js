require('../../scss/lookcars/lookcars.scss');
import { parkingsave, parkingfind } from '../model/lookcars';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
let time = '';
const lookcars = {
  init() {
    this.initDom();
    this.initEvent();
  },
  initDom() {
    this.lookcarsSure = $('.lookcarsSure');  // 确认按钮
    this.jumpUrl = $('.jumpUrl'); // 跳转地址
  },
  initEvent() {
    // 获取跳转地址
    parkingfind({
      key_admin: keyadmin,
    }).then(json => {
      console.log(json);
      this.jumpUrl.val(json.data.function_name);
    }, json => {
      console.log(json);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
    });
    // 确认提交
    this.lookcarsSure.on('click', () => {
      parkingsave({
        key_admin: keyadmin,
        url: this.jumpUrl.val() || '',
      }).then(json => {
        console.log(json);
        $('.msgContent').html(json.msg);
        $('.successBox').css({ display: 'block' });
        time = setTimeout(() => {
          $('.successBox').css({ display: 'none' });
          location.reload();
          clearTimeout(time);
        }, 1000);
      }, json => {
        console.log(json);
        $('.msgContent').html(json.msg);
        $('.successBox').css({ display: 'block' });
        time = setTimeout(() => {
          $('.successBox').css({ display: 'none' });
          clearTimeout(time);
        }, 1000);
      });
    });
  },
};
lookcars.init();
