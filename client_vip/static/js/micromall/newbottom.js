require('../../scss/micromall/newbottom.scss');
require('../bootstrap/modal');
import { getUploadToken, adOperate, adUpdate } from '../model/micromall';
import { upload } from '../modules/qiniuupload';
const UM = window.UM;
const $ = window.$;
const conf = window.conf;
const um = UM.getEditor('myEditor', {
  initialFrameWidth: 570,
  initialFrameHeight: 360,
  autoHeightEnabled: false,
  focus: true,
});
um.ready(() => {
  // um.setContent();
  um.getContent();
});

const newbottom = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(d => {
      upload(d.data);
      console.log(d.data);
      window.QINIU_TOKEN = d.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this.adUpdate();
  },
  initDom() {
    this.$name = $('.name');
    this.$link = $('.link');
    this.$author = $('.author');
    this.$image = $('.image');
    this.$msg = $('.msg');
    this.$subBtn = $('.subBtn');
  },
  initEvent() {
    this.$subBtn.on('click', () => {
      this.adOperate();
    });
  },
  adOperate() {
    // status: 1 添加， 2 修改
    adOperate({
      key_admin: $.cookie('ukey'),
      status: '2',
      ad_id: '',
      position: conf.position,
      name: this.$name.val(),
      link: this.$link.val(),
      author: this.$author.val(),
      property: this.$image.attr('src'),
      content: um.getContent(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      location.href = '/tinymall/micromall';
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },

  adUpdate() {
    adUpdate({
      key_admin: $.cookie('ukey'),
      ad_id: '',
      position: conf.position,
    }).then(json => {
      console.log(json);
      this.$name.val(json.data.name);
      this.$link.val(json.data.link);
      this.$author.val(json.data.author);
      this.$image.attr('src', json.data.property);
      um.ready(() => {
        um.setContent(json.data.content);
        // um.getContent();
      });
    }, json => {
      console.log(json);
    });
  },
};
newbottom.init();
