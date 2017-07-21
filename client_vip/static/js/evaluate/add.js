require('../../scss/evaluate/add.scss');
import { getTagsAll, editClassOne, getClassOne } from '../model/evaluate';
import { out } from '../modules/out.js';
const $ = window.$;
const conf = window.conf;
require('../modules/cookie')($);
const main = {
  init() {
    this.initDom();
    this.initEvent();
    this.getTagsAll();
  },
  initDom() {
    this.$out = $('.out');
    this.$className = $('#class-name');
    this.$subBtn = $('.subBtn .btn'); // 提交
    this.$checkBoxGood = $('.checkbox-good');
    this.$checkBoxBad = $('.checkbox-bad');
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
      // const checkBoxs = $('.label-input');
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
      return this.editClassOne();
    });
  },
  getClassOne() {
    getClassOne({
      key_admin: $.cookie('ukey'),
      class_id: conf.id,
    }).then(result => {
      this.$className.val(result.data.name);
      $('.label-input').each((i, item) => {
        const dataId = $(item).attr('data-id');
        if (result.data.tags[dataId]) {
          $(`#inlineCheckbox${dataId}`).prop('checked', true);
        }
      });
    }, error => {
      if (error.code !== 102) {
        alert(error.msg);
      }
    });
  },
  editClassOne() {
    const tagList = [];
    $.each($('.label-input'), (i, item) => {
      if ($(item).prop('checked')) {
        tagList.push($(item).attr('data-id'));
      }
    });
    editClassOne({
      key_admin: $.cookie('ukey'),
      class_id: conf.id ? conf.id : 0,
      name: this.$className.val(),
      tags: tagList.length > 0 ? tagList : '',
    }).then(() => {
      location.href = '/evaluate';
    }, error => {
      alert(error.msg);
    });
  },
  getTagsAll() {
    getTagsAll({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      $('.star-class').css('display', 'block');
      let htmlG = '';
      let htmlB = '';
      $.each(result.data, (i, item) => {
        const html = `<label class="checkbox-inline">
          <input type="checkbox" class="label-input" id="inlineCheckbox${item.id}"
           data-id="${item.id}" value="option1"> ${item.name} </label>`;
        if (item.star >= 1 && item.star <= 3) {
          htmlB += html;
        } else {
          htmlG += html;
        }
      });
      this.$checkBoxGood.html(htmlG);
      this.$checkBoxBad.html(htmlB);
      if (conf.id) this.getClassOne();
    }, error => {
      if (error.code === 102) {
        if (conf.id) this.getClassOne();
      } else {
        alert(error.msg);
      }
    });
  },
};
main.init();
