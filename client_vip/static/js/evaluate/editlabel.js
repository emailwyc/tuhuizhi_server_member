require('../../scss/evaluate/editlabel.scss');
import { getClassAll, editTagsOne, getTagsOne } from '../model/evaluate';
import { out } from '../modules/out.js';
const $ = window.$;
const conf = window.conf;
require('../modules/cookie')($);
const main = {
  init() {
    this.initDom();
    this.initEvent();
    this.getClassAll();
  },
  initDom() {
    this.$out = $('.out');
    this.$className = $('#class-name');
    this.$subBtn = $('.subBtn .btn'); // 提交
    this.$checkBox = $('.checkbox-select');
    this.$selectBox = $('.select-box');
    this.$order = $('#order');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$subBtn.on('click', () => {
      if (!this.$className.val()) {
        alert('请输入分类名称');
        return false;
      }
      if (!this.$order.val()) {
        alert('请输入顺序');
        return false;
      }
      // const checkBoxs = this.$checkBox.find('input');
      // let num = 0;
      // checkBoxs.each((i, item) => {
      //   if (!$(item).prop('checked')) {
      //     num ++;
      //   }
      // });
      // if (num === checkBoxs.length) {
      //   alert('请选择该分类的标签');
      //   return false;
      // }
      return this.editTagsOne();
    });
  },
  getTagsOne() {
    getTagsOne({
      key_admin: $.cookie('ukey'),
      tags_id: conf.id,
    }).then(result => {
      this.$className.val(result.data.name);
      this.$order.val(result.data.order);
      if (result.data.star >= 1 && result.data.star <= 3) {
        this.$selectBox.find('option.star1').attr('selected', true);
      } else {
        this.$selectBox.find('option.star5').attr('selected', true);
      }
      this.$checkBox.find('input').each((i, item) => {
        const dataId = $(item).attr('data-id');
        if (result.data.class[dataId]) {
          $(`#inlineCheckbox${dataId}`).prop('checked', true);
        }
      });
    }, error => {
      alert(error.msg);
    });
  },
  editTagsOne() {
    const tagList = [];
    $.each(this.$checkBox.find('input'), (i, item) => {
      if ($(item).prop('checked')) {
        tagList.push($(item).attr('data-id'));
      }
    });
    editTagsOne({
      key_admin: $.cookie('ukey'),
      star: this.$selectBox.find('option:selected').val(),
      order: this.$order.val(),
      tags_id: conf.id ? conf.id : 0,
      name: this.$className.val(),
      class: tagList.length > 0 ? tagList : '',
    }).then(() => {
      location.href = '/evaluate/label';
    }, error => {
      alert(error.msg);
    });
  },
  getClassAll() {
    getClassAll({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      let html = '';
      $.each(result.data, (i, item) => {
        html += `<label class="checkbox-inline">
          <input type="checkbox" id="inlineCheckbox${item.id}" data-id="${item.id}"
           value="option1"> ${item.name} </label>`;
      });
      this.$checkBox.html(html);
      if (conf.id) this.getTagsOne();
    }, error => {
      if (error.code === 102) {
        if (conf.id) this.getTagsOne();
      } else {
        alert(error.msg);
      }
    });
  },
};
main.init();
