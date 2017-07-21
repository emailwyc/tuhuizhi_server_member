require('../../scss/childAccount/addChildAccount.scss');
import { editAccountOne, getColumnList, getDetailById, getBuildidAll } from '../model/childAccount';
const $ = window.$;
const conf = window.conf;
const addChildAccount = {
  init() {
    this.getColumnList();
    this.initDom();
    this.initEvent();
    this.buildid = '';
  },
  initDom() {
    this.$name = $('#name');
    this.$buildid = $('#buildid');
    this.$password = $('#password');
    this.$passwords = $('#passwords');
    this.$submitBtn = $('.submitBtn');
    this.$columnright = $('.columnright');
    this.$hiddenpasw = $('.hiddenpasw');
    this.$hiddenpasw2 = $('.hiddenpasw2');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$submitBtn.on('click', () => {
      // alert(this.$buildid.val());
      if (this.$password.val() === this.$passwords.val()) {
        if (this.$password.val().length >= 6 && this.$passwords.val().length >= 6) {
          this.editAccountOne();
        } else {
          alert('最少输入6为数字密码');
        }
      } else {
        alert('两次密码输入不同');
      }
    });
  },
  getColumnList() {
    getColumnList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      let columnHTML = '';
      $.each(json.data, (i, v) => {
        // console.log(v);
        columnHTML += `<div class="form-check form-check-inline">
          <label class="form-check-label">
            <input class="form-check-input" data-columnApi="${v.column_api}" data-id="${v.id}"
            data-columnHtml="${v.column_html}"
            type="checkbox" value="${v.column_name}"> ${v.column_name}
          </label>
        </div>`;
      });
      this.$columnright.html(columnHTML);
      if (conf.id) {
        this.getDetailById();
      } else {
        this.getBuildidAll();
      }
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录已过期请重新登录');
        location.href = '/user/login';
        return;
      }
    });
  },
  // 添加或编辑
  editAccountOne() {
    const paramsList = [];
    this.$columnright.find('input').each((i, v) => {
      if (v.checked) {
        const columnName = $(v).val();
        const columnHtml = $(v).attr('data-columnHtml');
        const columnApi = $(v).attr('data-columnApi');
        const id = parseInt($(v).attr('data-id'), 10);
        // console.log($(v).data('id'));
        paramsList.push({ column_name: columnName, column_html: columnHtml,
          column_api: columnApi, id });
      }
    });
    editAccountOne({
      key_admin: $.cookie('ukey'),
      accid: conf.id || '',
      name: this.$name.val(),
      password: this.$password.val(),
      column: encodeURIComponent(JSON.stringify(paramsList)),
      buildid: this.$buildid.val(),
      passwords: this.$passwords.val(),
    }).then(json => {
      console.log(json);
      window.location.href = '/childAccount';
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
      if (json.code === 1001) {
        alert('登录已过期请重新登录');
        location.href = '/user/login';
        return;
      }
    });
  },
  // 获取单个账号详情
  getDetailById() {
    getDetailById({
      key_admin: $.cookie('ukey'),
      accid: conf.id,
    }).then(json => {
      console.log(json);
      this.$name.val(json.data.name);
      this.$password.val(json.data.password);
      this.$passwords.val(json.data.password);
      this.$hiddenpasw.val(json.data.password);
      const column = JSON.parse(json.data.column);
      this.buildid = json.data.buildid;
      $.each(column, (i, v) => {
        this.$columnright.find('input').each((index, el) => {
          const ele = el;
          if (v.column_name === $(ele).val()) {
            ele.checked = true;
          }
        });
      });
      if (conf.id) {
        this.getBuildidAll();
      }
    }, json => {
      console.log(json);
    });
  },
  // 本商户下所有建筑物id
  getBuildidAll() {
    getBuildidAll({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      let option = '';
      console.log(json);
      $.each(json.data, (i, v) => {
        option += `<option value="${v.id}"
        ${v.id === this.buildid ? 'selected' : ''}>${v.name}</option>`;
      });
      this.$buildid.html(option);
    }, json => {
      console.log(json);
    });
  },
};
addChildAccount.init();
