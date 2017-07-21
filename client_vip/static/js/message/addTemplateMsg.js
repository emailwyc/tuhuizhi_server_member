require('../../scss/message/addTemplateMsg.scss');
import { searchTemplateId, createTemplate,
  switchowTemplateInfo, editTemplate } from '../model/message';
const $ = window.$;
require('../modules/cookie')($);
const conf = window.conf;
console.log(conf);
const addTemplateMsg = {
  init() {
    this.initDom();
    this.initEvent();
    if (conf.id) {
      console.log(1);
      this.switchowTemplateInfo();
    }
  },
  initDom() {
    this.$templateid = $('.templateid');
    this.$confirmed = $('.confirmed');
    this.$openid = $('.openid');
    this.$subBtn = $('.subBtn .btn');
    this.$formkey = $('.formkey');
    this.$keyscontent = $('.keys_content');
  },
  initEvent() {
    if (conf.id) {
      this.$confirmed.attr('disabled', 'disabled');
    }
    this.$confirmed.on('click', () => {
      if (this.$templateid.val() === '') {
        alert('请输入模版ID');
        return;
      }
      this.$templateid.attr('disabled', 'disabled');
      this.searchTemplateId();
    });
    this.$subBtn.on('click', () => {
      if (conf.id) {
        this.editTemplate();
      } else {
        this.createTemplate();
      }
    });
  },
  createTemplate() {
    const data = {};
    for (let i = 0; i < $('.formkey label').length; i++) {
      const formkey = $('.formkey label').eq(i).text();
      const value1 = [];
      for (let j = 0; j < $('.formkey .form-control').length; j++) {
        const formvalue = $('.formkey .form-control').eq(j).val();
        value1.push(formvalue);
      }
      data[formkey] = value1[i];
    }
    data.key_admin = $.cookie('ukey');
    data.templateid = this.$templateid.val();
    data.openid = this.$openid.val();
    createTemplate(data).then(json => {
      console.log(json);
      $('.msg').html(json.msg);
      location.href = '/Message/templateMsgList';
    }, json => {
      console.log(json);
      $('.msg').html(json.msg);
    });
  },
  searchTemplateId() {
    searchTemplateId({
      key_admin: $.cookie('ukey'),
      templateid: this.$templateid.val(),
    }).then(json => {
      console.log(json);
      let html = '';
      $.each(json.data.keys, (i, v) => {
        html += `
        <div class="form-group">
          <label for="">${v}</label>
          <input type="text" class="form-control" placeholder="" value="">
        </div>
        `;
      });
      this.$formkey.html(html);
      this.$keyscontent.html(json.data.template.content);
    }, json => {
      console.log(json);
      $('.msgsearch').css('color', 'red').html(json.msg);
    });
  },

// 获取一个模板消息
  switchowTemplateInfo() {
    switchowTemplateInfo({
      key_admin: $.cookie('ukey'),
      id: conf.id,
    }).then(json => {
      console.log(json);
      this.$templateid.val(json.data.params.templateid).attr('disabled', 'disabled');
      this.$openid.val(json.data.params.openid);
      let html = '';
      $.each(json.data.params.keys, (i, v) => {
        // console.log(v);
        html += `
        <div class="form-group">
          <label for="">${i}</label>
          <input type="text" class="form-control" placeholder="" value="${v}">
        </div>
        `;
      });
      this.$formkey.html(html);
    }, json => {
      console.log(json);
    });
  },

  editTemplate() {
    const data = {};
    for (let i = 0; i < $('.formkey label').length; i++) {
      const formkey = $('.formkey label').eq(i).text();
      const value1 = [];
      for (let j = 0; j < $('.formkey .form-control').length; j++) {
        const formvalue = $('.formkey .form-control').eq(j).val();
        value1.push(formvalue);
      }
      data[formkey] = value1[i];
    }
    data.key_admin = $.cookie('ukey');
    data.templateid = this.$templateid.val();
    data.openid = this.$openid.val();
    data.id = conf.id;
    editTemplate(data).then(json => {
      console.log(json);
      $('.msg').html(json.msg);
      location.href = '/Message/templateMsgList';
    }, json => {
      console.log(json);
      $('.msg').html(json.msg);
    });
  },
};
addTemplateMsg.init();
