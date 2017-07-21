require('../../scss/member/rights.scss');
import { getUploadToken, memberRights, staticpage } from '../model';
import { out } from '../modules/out.js';
const $ = window.$;
require('../modules/cookie')($);
const stor = sessionStorage;
// pubApi.getQinNiuToken.then(token => {
// const UM = window.UM;
const UM = window.UM;
const um = UM.getEditor('myEditor', {
  initialFrameHeight: 500,
  autoHeightEnabled: false,
  focus: true,
});
const rights = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(json => {
      stor.setItem('token', json.data);
      window.QINIU_TOKEN = json.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this.getMemberRights();
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$out = $('.out');
    this.$title = $('.title');
    this.$subBtn = $('.subBtn');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$subBtn.on('click', () => {
      this.submMemberRights();
    });
  },
  getMemberRights() {
    memberRights({
      key_admin: $.cookie('ukey'),
      tid: 1,
    }).then(json => {
      console.log(json);
      um.ready(() => {
        um.setContent(json.data.content || '');
      });
      this.$title.val(json.data.title);
      this.state.ctg = {
        id: json.data.id,
      };
    });
  },
  submMemberRights() {
    staticpage({
      key_admin: $.cookie('ukey'),
      tid: 1,
      title: this.$title.val(),
      content: um.getContent(),
      sid: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },
};
rights.init();
//
// window.QINIU_TOKEN = token;
// window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
// const $warpHeight = $('#container_fluid').height();
// const $dwSubmit = $('#dw_submit');
// const um = UM.getEditor('myEditor', {
//   // lang:/^zh/.test(navigator.language || navigator.browserLanguage ||
//   // navigator.userLanguage) ? 'zh-cn' : 'en',
//   // langPath:UMEDITOR_CONFIG.UMEDITOR_HOME_URL + "lang/",
//   initialFrameHeight: 250,
//   // autoHeightEnabled: false,
//   focus: true,
// });
// memberRights.get.then(json => {
//   um.ready(params => {
//     notLog(params);
//     // 设置编辑器的内容
//     console.log('json', json);
//     um.setHeight($warpHeight - 110);
//     um.setContent(json.content || '');
//     $('.title').val(json.title);
//     // 获取html内容，返回: <p>hello</p>
//     // 获取纯文本内容，返回: hello
//   });
// }, json => {
//   console.log(json);
// });
// $dwSubmit.click(e => {
//   notLog(e);
//   const content = um.getContent();
//   memberRights.update({
//     // tid: '1',
//     title: $('.title').val(),
//     content,
//   }).then(json => {
//     notLog(json);
//     location.reload();
//   }, json => {
//     console.log(json);
//   });
// });
// });
