require('../../scss/dashboard/pageview.scss');
import { out } from '../modules/out.js';
import { pagepvList, pagepvListDel, pagepvListAdd } from './model/pageview';
require('../modules/bootstrap/modal');
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
    this.store = {
      page: 1,
      count_page: 1,
      id: 0,
    };
    this._pagepvList();
  },
  initDom() {
    this.$out = $('.out');
    this.$tbody = $('.table tbody');
    this.$gridSystemModal = $('#gridSystemModal');
    this.$addModal = $('#add-modal');
    this.$editModal = $('#edit-modal');
    this.$table = $('.table-responsive');
    this.$addId = $('#add-id');
    this.$addName = $('#add-name');
    this.$addRote = $('#add-rote');
    this.$editId = $('#edit-id');
    this.$editName = $('#edit-name');
    this.$editRote = $('#edit-rote');
    this.$newadd = $('#newadd');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$addModal.on('click', '.add-save', () => {
      const name = this.$addName.val();
      const rote = this.$addRote.val();
      this.store.id = '';
      this.store.name = name;
      this.store.rote = rote;
      this._pagepvListAdd();
    });

    this.$editModal.on('click', '.edit-save', () => {
      const name = this.$editName.val();
      const rote = this.$editRote.val();
      this.store.name = name;
      this.store.rote = rote;
      this._pagepvListAdd();
    });

    this.$gridSystemModal.on('click', '.del', () => {
      this._pagepvListDel();
    });

    // this.$table.on('click', 'tr td', function () {
    //   const idx = $(this).index();
    //   if (idx === 4) return;
    //   window.location.href = 'pageviewdetails';
    // });

    this.$table.on('click', '.edit-btn', (e) => {
      const ele = $(e.target).parents('tr').children();
      const id = ele.eq(0).text();
      const name = ele.eq(1).text();
      const rote = ele.eq(2).text();
      this.$editName.val(name);
      this.$editRote.val(rote);
      this.store.id = id;
    });

    this.$table.on('click', '.del-btn', (e) => {
      const ele = $(e.target).parents('tr');
      const id = ele.children().eq(0).text();
      this.store.id = id;
    });
  },
  _pagepvList() {
    pagepvList({
      ukey: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      this.render(json.data.data);
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      this.$tbody.append(`<tr><td colspan="5">${json.msg}</td></tr>`);
    });
  },
  _pagepvListAdd() {
    const id = this.store.id;
    const name = this.store.name;
    const rote = this.store.rote;
    pagepvListAdd({
      ukey: $.cookie('ukey'),
      id,
      name,
      rote,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      alert(json.msg);
    });
  },
  _pagepvListDel() {
    pagepvListDel({
      ukey: $.cookie('ukey'),
      id: this.store.id,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      alert(json.msg);
    });
  },
  render(data) {
    if (data.length === 0) {
      this.$tbody.append('<tr><td colspan="4">找不到相关数据</td></tr>');
      return;
    }
    let tpl = '';
    $.each(data, (i, v) => {
      tpl += `<tr>
        <td>${v.id}</td>
        <td>${v.name}</td>
        <td>${v.rote}</td>
        <td>${v.ctime}</td>
        <td>
          <a href="javascript:;" data-toggle="modal"
            data-target="#edit-modal" class="edit-btn">编辑</a>
          <a href="javascript:;" data-toggle="modal"
            data-target="#gridSystemModal" class="del-btn">删除</a>
        </td>
      </tr>`;
    });
    this.$tbody.append(tpl);
    return;
  },
};

columnAdvertisement.init();
