require('../../scss/management/reply.scss');
import { getReplyList, replyFeedback } from '../model/index';
import { getDates } from '../modules/timestamp';
require('../bootstrap/modal');
const $ = window.$;
const conf = window.conf;
console.log(conf);
require('../modules/cookie')($);
const reply = {
  init() {
    this.initDom();
    this.initEvent();
    this.getReplyList();
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$content = $('.content');
    this.$replyok = $('.replyok');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$replyok.on('click', () => {
      this.replyFeedback();
    });
  },
  getReplyList() {
    getReplyList({
      key_admin: $.cookie('ukey'),
      id: conf.id,
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data.feedbacklist, (i, v) => {
        tr += `<tr><td width="300">${getDates(v.createtime)}</td>
        <td><div class="textcontent">${v.content}</div></td>
        <td>${v.usermember ? v.usermember : '客服'}</td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
    });
  },

  replyFeedback() {
    replyFeedback({
      key_admin: $.cookie('ukey'),
      id: conf.id,
      content: this.$content.val(),
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
reply.init();
