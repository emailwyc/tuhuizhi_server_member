require('../../scss/dashboard/columnadvertisement.scss');
import { out } from '../modules/out.js';
require('../modules/bootstrap/modal');
const validate = require('../modules/validate');
const $ = window.$;
require('../modules/cookie')($);
const columnAdvertisement = {
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
    this.$tbody = $('.table tbody');
    this.$gridSystemModal = $('#gridSystemModal');
    this.$addModal = $('#add-modal');
    this.$addColumnName = $('#add-column-name');
    this.$table = $('.table-responsive');
    this.$form = $('#form');
    validate.init(this.$form);
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$table.on('click', 'a', (e) => {
      console.log(e.target);
      const $target = $(e.target);
      this.state.ctg = {
        id: $target.data('id'),
      };
      console.log($target.data('id'));
    });
    this.$addModal.on('click', '.add-save', () => {
      console.log('添加');
      console.log(this.$addcolumnName);
    });

    this.$gridSystemModal.on('click', '.del', () => {
      location.reload();
    });
  },
  // jurisdiction() {
  //   jurisdictionadd({
  //     ukey: $.cookie('ukey'),
  //     column_name: this.$columnName1.val(),
  //     column_api: this.$columnApi1.val(),
  //     column_html: this.$columnHtml1.val(),
  //   }).then(json => {
  //     console.log(json);
  //     location.reload();
  //   }, json => {
  //     console.log(json);
  //     if (json.code === 1001) {
  //       alert('登录超时请重新登录');
  //       location.href = '/dashboard/login';
  //     }
  //   });
  // },
};

columnAdvertisement.init();
