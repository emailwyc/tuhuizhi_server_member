require('../../scss/survey/groupadd.scss');
const $ = window.$;
const conf = window.conf;
require('../modules/cookie')($);
import { getQuesGroupById, questionGroup } from '../model/survey';

const main = {
  init() {
    this.initDom();
    this.initEvent();
    if (conf.id) this.getQuesGroupById();
  },
  initDom() {
    this.$needsome = $('#needsome');
    this.$title = $('#title');
    this.$subBtn = $('.subBtn .btn'); // 提交
  },
  initEvent() {
    this.$subBtn.on('click', () => {
      if (!this.$title.val()) {
        alert('请输入名称！');
        return false;
      }

      if (!this.$needsome.val()) {
        alert('请输入备注！');
        return false;
      }

      return this.questionGroup();
    });
  },
  getQuesGroupById() {
    getQuesGroupById({
      key_admin: $.cookie('ukey'),
      id: conf.id,
    }).then(result => {
      this.$title.val(result.data.group_name);
      this.$needsome.val(result.data.group_des);
    });
  },
  questionGroup() {
    questionGroup({
      key_admin: $.cookie('ukey'),
      id: conf.id,
      des: this.$needsome.val(),
      name: this.$title.val(),
    }).then(() => {
      location.href = '/survey/group';
    }, error => {
      alert(error.msg);
    });
  },
};
main.init();
