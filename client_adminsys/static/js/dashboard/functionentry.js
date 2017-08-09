require('../../scss/dashboard/functionentry.scss');
// const hogan = require('hogan.js');
// const resourcesList = require('./tpls/resourceslist.html');
import { catalogstatus, cataloglist, cataloginsert, catalogsave } from './model';
import { out } from '../modules/out.js';
require('../modules/bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
const functionentry = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.cataloglist();
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
    this.$url = $('#url');
    this.$verid = $('#verid');
    this.$columnApi = $('#column_api');
    this.$columnHtml = $('#column_html');
    this.$myModal1 = $('#myModal1');
    this.$gridSystemModal1 = $('#gridSystemModal1');
    this.$columnName1 = $('#column_name1');
    this.$url1 = $('#url1');
    this.$columnApi1 = $('#column_api1');
    this.$columnHtml1 = $('#column_html1');
    this.$table = $('.table-responsive');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$tbody.on('click', '.up', (e) => {
      const $td = $(e.target).parent().prevAll();
      console.log($td);
      // this.$category1.val($td.eq(2).html());
      // this.$vername1.val($td.eq(3).html());
      this.$url1.val($td.eq(1).text());
      this.$columnName1.val($td.eq(2).html());
      // this.$gourl1.val($td.eq(1).html());
      // this.$description1.val($td.eq(2).html());
      this.$verid.val($td.eq(3).text());
      $('#myModal1').modal('show');
      console.log($(e.target).parent().prevAll());
    });
    // this.$table.on('click', 'a', (e) => {
    //   console.log(e.target);
    //   const $target = $(e.target);
    //   this.state.ctg = {
    //     id: $target.data('id'),
    //     status: $target.data('status') || 's',
    //   };
    //   console.log($target.data('id'));
    //   console.log($target.data('status') || 's');
    // });
    this.$myModal1.on('click', '.save1', () => {
      this.catalogsave();
    });
    this.$myModal.on('click', '.save', () => {
      this.cataloginsert();
    });
    this.$table.on('click', 'a.do', (e) => {
      const doid = $(e.target).attr('id');
      // console.log(doid);
      e.preventDefault();
      this.catalogstatus(doid);
    });
    this.$gridSystemModal.on('click', '.del', () => {
      this.getOrDel();
      location.reload();
    });
  },
  cataloglist() {
    // const tpllist = hogan.compile(resourcesList);
    cataloglist({
      ukey: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let html = '';
      $.each(json.data, (i, n) => {
        html += `<tr>
                  <td>${n.id}</td>
                  <td>${n.name}</td>
                  <td>${n.url}</td>
                  <td>${n.status - 0 ? '开启' : '禁用'}</td>
                  <td><a href="/dashboard/versionlistall?doid=${n.id}&name=${n.name}"
                   class="getID">${n.status - 0 ? '版本管理' : ''}</a>
                   <a href="#" class=up id=${n.id}>${n.status - 0 ? '编辑' : ''}</a>
                  <a href="#" class=do id=${n.id}>${n.status - 0 ? '禁用' : '开启'}</a>
                  <a href="/dashboard/subcolumn?doid=${n.id}&name=${n.name}"
                   class="getID">${n.status - 0 ? '子栏目模板' : ''}</a></td>
                </tr>`;
      });
      this.$tbody.html(html);
    }, json => {
      if (json.code === 102) {
        // this.$tbody.html(tpllist.render({ list: [] }));
        return;
      }
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
        return;
      }
    });
  },
  cataloginsert() {
    cataloginsert({
      ukey: $.cookie('ukey'),
      name: this.$columnName.val(),
      url: this.$url.val(),
      // column_api: this.$columnApi1.val(),
      // column_html: this.$columnHtml1.val(),
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
  catalogsave() {
    catalogsave({
      ukey: $.cookie('ukey'),
      name: this.$columnName1.val(),
      url: this.$url1.val(),
      id: this.$verid.val(),
      // column_api: this.$columnApi1.val(),
      // column_html: this.$columnHtml1.val(),
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
  catalogstatus(doid) {
    catalogstatus({
      ukey: $.cookie('ukey'),
      catalog_id: doid,
      // column_api: this.$columnApi1.val(),
      // column_html: this.$columnHtml1.val(),
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
};
functionentry.init();
