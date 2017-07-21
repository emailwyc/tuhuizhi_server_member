require('../../scss/donationpoint/setpoint.scss');
import { setTing, getSetTing } from '../model/donationpoint';
// const conf = window.conf;
const $ = window.$;
require('../modules/cookie')($);

const setpoint = {
  init() {
    this.initDom();
    this.initEvent();
    this.getSetTing();
  },
  initDom() {
    this.$validhour = $('.validhour');
    this.$least = $('.least');
    this.$most = $('.most');
    this.$lapse = $('.lapse');
    this.$setBtn = $('.setBtn');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$setBtn.on('click', () => {
      this.setTing();
    });
  },
  setTing() {
    setTing({
      key_admin: $.cookie('ukey'),
      urlexpirydate: this.$validhour.val(),
      mixscore: this.$least.val(),
      maxscore: this.$most.val(),
      timeinterval: this.$lapse.val(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },

  getSetTing() {
    getSetTing({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      const data = json.data;
      this.$validhour.val(data.urlexpirydate);
      this.$least.val(data.mixscore);
      this.$most.val(data.maxscore);
      this.$lapse.val(data.timeinterval);
    });
  },
};
setpoint.init();
