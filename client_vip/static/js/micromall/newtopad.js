require('../../scss/micromall/newtopad.scss');
require('../bootstrap/modal');
import { getUploadToken, adOperate } from '../model/micromall';
import { upload } from '../modules/qiniuupload';
const UM = window.UM;
const $ = window.$;
const um = UM.getEditor('myEditor', {
  initialFrameWidth: 870,
  initialFrameHeight: 360,
  autoHeightEnabled: false,
  focus: true,
});
const newtopad = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(d => {
      upload(d.data);
      console.log(d.data);
      window.QINIU_TOKEN = d.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
  },
  initDom() {
    this.$name = $('.name');
    this.$link = $('.link');
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
    adOperate({
      key_admin: $.cookie('ukey'),
      status: '1',
      ad_id: '',
      position: 'top',
      name: this.$name.val(),
      link: this.$link.val(),
      property: this.$image.attr('src'),
      content: um.getContent(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      location.href = '/tinymall/topad';
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },
};
newtopad.init();
