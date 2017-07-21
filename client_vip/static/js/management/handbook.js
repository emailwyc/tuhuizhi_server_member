require('../../scss/management/handbook.scss');
import { getManualList, mamnualSortUp, mamnualSortDown, manualDel } from '../model';
const $ = window.$;
require('../modules/cookie')($);
const handbook = {
  init() {
    this.initDom();
    this.initEvent();
    this.handbookList();
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$table = $('.table tbody');
    this.$content = $('.handbook_content');
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$subBtn = $('.subBtn');
  },
  initEvent() {
    // sort： 排序 id： 条数
    this.$table.on('click', '.prev', (e) => {
      const $target = $(e.target);
      if ($target.hasClass('de')) {
        return;
      }
      this.state.ctg = {
        id: $target.data('id') || 0,
        sort: $target.data('sort'),
      };
      this.mamnualSortUp();
    });
    this.$table.on('click', '.next', (e) => {
      const $target = $(e.target);
      if ($target.hasClass('de')) {
        return;
      }
      this.state.ctg = {
        id: $target.data('id') || 0,
        sort: $target.data('sort'),
      };
      this.mamnualSortDown();
    });

    this.$table.on('click', '.edit', (e) => {
      const $target = $(e.target);
      this.state.ctg = {
        id: $target.data('id') || 0,
        sort: $target.data('sort'),
      };
      this.mamnualSortDown();
    });

    this.$table.on('click', '.del', (e) => {
      const $target = $(e.target);
      this.state.ctg = {
        id: $target.data('id') || 0,
        sort: $target.data('sort'),
      };
      this.manualDel();
    });
  },
  handbookList() {
    getManualList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let list = '';
      $.each(json.data, (i, v) => {
        list += `<tr">
          <td>${v.title}</td>
          <td>
            <div class="handbook_content">${v.content}</div>
          </td>
          <td>
            <div class="operation">
              <div class="edit">
                <a href="/management/edit?id=${v.id}" data-id="${v.id}" >编辑</a>
              </div>
              <div class="move">
                <ul>
                  <li>
                    <a href="javascript:;"
                    data-sort="${v.sort}" data-id="${v.id}"
                    class="prev">上移</a>
                  </li>
                  <li>
                    <a href="javascript:;" class="next"
                    data-sort="${v.sort}" data-id="${v.id}"
                    >下移</a>
                  </li>
                </ul>
              </div>
              <div class="del">
                <a href="javascript:;" data-id="${v.id}">删除</a>
              </div>
            </div>
          </td>
        </tr>`;
        this.$table.html(list);
      });
      $('tbody tr').first().find('.prev').addClass('de');
      $('tbody tr').last().find('.next').addClass('de');
    }, json => {
      console.log(json);
      // this.$subBtn.css('display', 'none');
      this.$table.html(`<td colspan="3">${json.msg}</td>`);
    });
  },
  manualDel() {
    manualDel({
      key_admin: $.cookie('ukey'),
      id: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
    });
  },
  mamnualSortUp() {
    mamnualSortUp({
      key_admin: $.cookie('ukey'),
      id: this.state.ctg.id,
      sort: this.state.ctg.sort,
    }).then(json => {
      console.log(json);
      location.reload();
    });
  },
  mamnualSortDown() {
    mamnualSortDown({
      key_admin: $.cookie('ukey'),
      id: this.state.ctg.id,
      sort: this.state.ctg.sort,
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
    });
  },
};
handbook.init();
