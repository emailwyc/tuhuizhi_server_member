require('../../scss/survey/declare.scss');
import { getUploadToken } from '../model';
const $ = window.$;
require('../modules/cookie')($);
import { getDisclaimer, editDisclaimer } from '../model/survey';
const stor = sessionStorage;
const UM = window.UM;
const um = UM.getEditor('myEditor', {
  initialFrameHeight: 500,
  autoHeightEnabled: false,
  focus: true,
});

const main = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(json => {
      stor.setItem('token', json.data);
      window.QINIU_TOKEN = json.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this.getDisclaimer();
  },
  initDom() {
    this.$isSet = $('.is-set');
    this.$title = $('#title');
    this.$subBtn = $('.subBtn .btn'); // 提交
  },
  initEvent() {
    this.$subBtn.on('click', () => {
      if (!um.getContent()) {
        alert('请输入免责声明内容！');
        return false;
      }

      if (!this.$title.val()) {
        alert('请输入免责声明标题！');
        return false;
      }

      return this.editDisclaimer();
    });
  },
  getDisclaimer() {
    getDisclaimer({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      um.ready(() => {
        um.setContent(result.data.content || '');
      });
      this.$title.val(result.data.title);
      $(`#declareRadio${result.data.isshow}`).prop('checked', true);
    });
  },
  editDisclaimer() {
    editDisclaimer({
      key_admin: $.cookie('ukey'),
      isshow: this.$isSet.find('input:checked').val(),
      content: um.getContent(),
      title: this.$title.val(),
    }).then(() => {
      location.href = '/survey/survey';
    }, error => {
      alert(error.msg);
    });
  },
};
main.init();
