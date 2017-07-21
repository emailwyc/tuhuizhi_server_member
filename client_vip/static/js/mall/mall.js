require('../../scss/mall/mall.scss');
import { integralTypeList, integralTypeSave, integralDel } from '../model';
import { out } from '../modules/out.js';
// import { code } from '../modules/code502';
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
const mall = {
  init() {
    this.initDom();
    this.initEvent();
    this.classifyList();
    this.state = {
      ctg: {},
    };
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
  },
  initDom() {
    this.$table = $('.table tbody');
    this.$gridModal = $('#gridSystemModal');
    this.$myModal = $('#myModal');
    this.$out = $('.out');
    this.$addBtn = $('.add-btn');
    this.$inputType = $('#inputType');
    this.$save = $('.save');
    this.$del = $('.del');
    this.$modifyInput = $('.modify-input');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$addBtn.on('click', (e) => {
      const status = $(e.target).attr('data-type');
      const typeName = this.$inputType.val();
      if (typeName === '') {
        alert('请填写分类名称');
        return;
      }
      this.addClassify(status, typeName);
    });

    this.$table.on('click', '.modify', (e) => {
      const id = $(e.target).attr('data-id');
      const status = $(e.target).attr('data-type');
      this.$typeId = id;
      this.$status = status;
    });

    this.$save.on('click', () => {
      const typeName = this.$modifyInput.val();
      const typeId = this.$typeId;
      const status = this.$status;
      this.$gridModal.modal('hide');
      this.addClassify(status, typeName, typeId);
    });

    this.$table.on('click', '.del-btn', (e) => {
      const id = $(e.target).attr('data-id');
      this.$typeId = id;
    });

    this.$del.on('click', () => {
      const typeId = this.$typeId;
      this.$myModal.modal('hide');
      this.integralDel(typeId);
    });
  },
  classifyList() {
    integralTypeList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      let td = '';
      $.each(json.data, (i, v) => {
        td += `<tr>
          <td scope="row">${i + 1}</td>
          <td>${v.type_name}</td>
          <td class="textleft">
            <a href="#" data-toggle="modal" data-type="M" data-id="${v.id}"
            data-target="#gridSystemModal" data-name="${v.type_name}" class="modify">修改</a>
            <a href="#" data-toggle="modal" data-id="${v.id}"
            data-target="#myModal" class="del-btn">删除</a>
          </td>
        </tr>`;
      });
      this.$table.html(td);
    }, json => {
      console.log(json);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
      // this.code();
    });
  },
  addClassify(status, typeName, typeId) {
    integralTypeSave({
      key_admin: $.cookie('ukey'),
      type_id: typeId,
      type_name: typeName,
      status,
    }).then((json) => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
    });
  },
  integralDel(typeId) {
    integralDel({
      key_admin: $.cookie('ukey'),
      type_id: typeId,
    }).then((json) => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
    });
  },

};
mall.init();
