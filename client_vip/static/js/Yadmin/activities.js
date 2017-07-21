require('../../scss/Yadmin/activities.scss');
import { obtainAct, actAdd } from '../model/Ycurrency';
const $ = window.$;
require('../modules/cookie')($);
const activities = {
  init() {
    this.initDom();
    this.initEvent();
    this.obtainAct();
  },
  initDom() {
    this.$activity = $('.activity');
    this.$btn = $('.btn');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$btn.on('click', () => {
      this.actAdd();
    });
  },
  obtainAct() {
    obtainAct({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      this.$activity.val(json.data.activity);
    }, json => {
      console.log(json);
    });
  },
  actAdd() {
    actAdd({
      key_admin: $.cookie('ukey'),
      activity: this.$activity.val(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      location.href = '/Yadmin';
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },
};
activities.init();
