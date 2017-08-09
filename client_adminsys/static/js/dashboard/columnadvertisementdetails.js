require('../../scss/dashboard/columnadvertisementdetails.scss');
import { out } from '../modules/out.js';
require('../modules/bootstrap/modal');
const validate = require('../modules/validate');
const $ = window.$;
const conf = window.conf;
require('../modules/cookie')($);
const details = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$out = $('.out');
    this.$subHeader = $('.sub-header');
    this.$tbody = $('.table tbody');
    this.$gridSystemModal = $('#gridSystemModal');
    this.$addModal = $('#add-modal');
    this.$addName = $('.add-column-name');
    this.$addRemarks = $('.add-column-remarks');
    this.$addSave = $('.add-save');
    this.$editModal = $('#edit-modal');
    this.$editName = $('.edit-column-name');
    this.$editRemarks = $('.edit-column-remarks');
    this.$editSave = $('.edit-save');
    this.$table = $('.table-responsive');
    this.$addForm = $('#add-form');
    this.$editForm = $('#edit-form');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$subHeader.text(`栏目列表-->${conf.name}`);

    validate.init(this.$addForm);
    validate.init(this.$editForm);

    this.$table.on('click', 'a', (e) => {
      const $target = $(e.target);
      this.state.ctg = {
        id: $target.data('id'),
        status: $target.data('status') || 's',
      };
      console.log($target.data('id'));
      console.log($target.data('status') || 's');
    });

    this.$addModal.on('click', '.add-save', () => {
      console.log('添加');
    });

    this.$editModal.on('click', '.edit-save', () => {
      console.log('编辑');
    });

    this.$gridSystemModal.on('click', '.del', () => {
      location.reload();
    });
  },
};

details.init();
