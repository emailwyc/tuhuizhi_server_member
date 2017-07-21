require('../../scss/management/clause.scss');
import { getUploadToken, memberTerms, memberTermsOne } from '../model';
const $ = window.$;
const stor = sessionStorage;
console.log(stor);
require('../modules/cookie')($);
const UM = window.UM;
const clause = {
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
  },
  initEvent() {
    const um = UM.getEditor('myEditor', {
      initialFrameHeight: 500,
      autoHeightEnabled: false,
      focus: true,
    });
    this.$subBtn.on('click', () => {
      memberTerms({
        key_admin: $.cookie('ukey'),
        content: um.getContent(),
      }).then(json => {
        console.log(json);
        alert(json.msg);
        location.reload();
      });
    });
    memberTermsOne({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      um.ready(() => {
        console.log('json', json);
        um.setContent(json.data.content || '');
      });
    });
  },
};
clause.init();
