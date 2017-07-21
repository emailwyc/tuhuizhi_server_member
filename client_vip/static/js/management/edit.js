require('../../scss/management/edit.scss');
import { getUploadToken, manualContentOne, manualSave } from '../model';
// import { out } from '../modules/out.js';
const $ = window.$;
const conf = window.conf;
const stor = sessionStorage;
console.log();
require('../modules/cookie')($);
// const searchUrl = location.search;
// const conf = searchUrl.substring(1);
console.log(conf);
const UM = window.UM;
const um = UM.getEditor('myEditor', {
  initialFrameHeight: 500,
  autoHeightEnabled: false,
  focus: true,
});
const edit = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(json => {
      stor.setItem('token', json.data);
      window.QINIU_TOKEN = json.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
  },
  initDom() {
    this.$title = $('.title');
    this.$subBtn = $('#submit');
    // console.log(stor.getItem('token'));
  },
  initEvent() {
    this.$subBtn.on('click', () => {
      this.manualSave();
    });
    if (!!conf.id) {
      this.manualContentOne();
    }
  },

  manualSave() {
    manualSave({
      key_admin: $.cookie('ukey'),
      content: um.getContent(),
      title: this.$title.val(),
      id: conf.id || '',
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.href = '/management/handbook';
    }, json => {
      alert(json.msg);
      if (json.code === 502) {
        location.href = '/user/login';
      }
    });
  },

  manualContentOne() {
    manualContentOne({
      key_admin: $.cookie('ukey'),
      id: conf.id,
    }).then(json => {
      console.log(json);
      um.ready(() => {
        um.setContent(json.data.content || '');
      });
      this.$title.val(json.data.title);
    }, json => {
      if (json.code === 502) {
        location.href = '/user/login';
      }
      alert(json.msg);
    });
  },
};
edit.init();
