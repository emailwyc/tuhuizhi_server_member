require('../../scss/dashboard/resources.scss');
const hogan = require('hogan.js');
const resourcesList = require('./tpls/resourceslist.html');
import { jurisdictionList, jurisdictionSave, jurisdictionOne, jurisdictionadd } from './model';
import { out } from '../modules/out.js';
require('../modules/bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
const resources = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.merchantConFig();
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$out = $('.out');
    this.$tbody = $('.table tbody');
    this.$myModal = $('#myModal');
    this.$gridSystemModal = $('#gridSystemModal');
    this.$columnName = $('#column_name');
    this.$columnApi = $('#column_api');
    this.$columnHtml = $('#column_html');
    this.$myModal1 = $('#myModal1');
    this.$gridSystemModal1 = $('#gridSystemModal1');
    this.$columnName1 = $('#column_name1');
    this.$columnApi1 = $('#column_api1');
    this.$columnHtml1 = $('#column_html1');
    this.$table = $('.table-responsive');
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
        status: $target.data('status') || 's',
      };
      console.log($target.data('id'));
      console.log($target.data('status') || 's');
    });
    this.$myModal1.on('click', '.save1', () => {
      this.jurisdiction();
    });
    this.$myModal.on('click', '.save', () => {
      this.jurisdictionsave();
    });
    this.$table.on('click', 'a.edit', () => {
      this.getOrDel();
    });
    this.$gridSystemModal.on('click', '.del', () => {
      this.getOrDel();
      location.reload();
    });
  },
  merchantConFig() {
    const tpllist = hogan.compile(resourcesList);
    jurisdictionList({
      ukey: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      this.$tbody.html(tpllist.render({ list: json.data }));
    }, json => {
      if (json.code === 102) {
        this.$tbody.html(tpllist.render({ list: [] }));
        return;
      }
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
        return;
      }
    });
  },
  jurisdiction() {
    jurisdictionadd({
      ukey: $.cookie('ukey'),
      column_name: this.$columnName1.val(),
      column_api: this.$columnApi1.val(),
      column_html: this.$columnHtml1.val(),
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  jurisdictionsave() {
    jurisdictionSave({
      ukey: $.cookie('ukey'),
      column_name: this.$columnName.val(),
      column_api: this.$columnApi.val(),
      column_html: this.$columnHtml.val(),
      id: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      if (json.code === 1008) {
        alert(json.msg);
      }
    });
  },
  getOrDel() {
    console.log(this.state.ctg.status);
    jurisdictionOne({
      ukey: $.cookie('ukey'),
      id: this.state.ctg.id,
      status: this.state.ctg.status,
    }).then(json => {
      console.log(json);
      this.$columnName.val(json.data.column_name);
      this.$columnApi.val(json.data.column_api);
      this.$columnHtml.val(json.data.column_html);
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
resources.init();
