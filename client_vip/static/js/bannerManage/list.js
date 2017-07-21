require('../../scss/bannerManage/list.scss');
require('../bootstrap/modal');
import { out } from '../modules/out.js';
const $ = window.$;
require('../modules/cookie')($);

const list = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
  },
  initDom() {
    this.$out = $('.out');
    this.$save = $('.save');
    this.$tbody = $('.list .table tbody');
    this.$addBtn = $('.add-btn');
    this.$myModal = $('#myModal');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$save.on('click', () => {
      // this._bannerDel(this.$bannerId);
      this.$myModal.modal('hide');
    });
  },
};

list.init();
