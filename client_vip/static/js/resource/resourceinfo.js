require('../../scss/resource/resourceinfo.scss');
import { getUploadToken } from '../model';
import { staticpagedetails, staticpageAU } from '../model/resource';
const $ = window.$;
const qs = require('../modules/qs.js');
const stor = sessionStorage;
const sId = qs('sid');

const um = window.UM.getEditor('myEditor', {
  initialFrameHeight: 500,
  autoHeightEnabled: false,
  focus: true,
});
const main = {
  init() {
    this.initDom();
    getUploadToken().then(json => {
      stor.setItem('token', json.data);
      window.QINIU_TOKEN = json.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this.loadData();
    this.eventFun();
  },
  initDom() {
    this.$title = $('.title');
    this.$subBtn = $('#dw_submit');
  },
  loadData() {
    if (sId) {
      staticpagedetails({
        key_admin: $.cookie('ukey'),
        tid: 4,
        sid: sId,
      }).then(json => {
        um.ready(() => {
          um.setContent(json.data.content || '');
        });
        this.$title.val(json.data.title);
      }, json => {
        console.log(json);
      });
    }
  },
  eventFun() {
    this.$subBtn.on('click', () => {
      if (!this.$title.val()) {
        alert('标题不能为空！');
        return false;
      }
      if (!um.getContent()) {
        alert('内容不能为空！');
        return false;
      }
      this.staticpageFun();
      return '';
    });
  },
  staticpageFun() {
    staticpageAU({
      key_admin: $.cookie('ukey'),
      tid: 4,
      title: this.$title.val(),
      content: um.getContent(),
      sid: sId && sId,
    }).then(result => {
      console.log(result.msg);
      location.href = '/resource';
    }, error => {
      console.log(error);
    });
  },
};

main.init();
