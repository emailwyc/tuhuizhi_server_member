require('../../scss/dashboard/resources.scss');
import { out } from '../modules/out.js';
import { conFigColumn, conFigColumnList, jurisdictionList, adminList } from './model';
const $ = window.$;
const conf = window.conf;
const binding = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.merchantConFig();
    this.adminList();
  },
  initDom() {
    this.$out = $('.out');
    this.$subbtn = $('.subbtn');
    this.$table = $('.table tbody');
    this.$subheader = $('.sub-header');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$table.on('click', '.istd', (e) => {
      const $target = $(e.currentTarget);
      console.log($target.find('input')[0].checked);
      if ($target.find('input')[0].checked) {
        $target.next().find('input')[0].checked = true;
      } else {
        $target.next().find('input')[0].checked = false;
      }
    });
    this.$subbtn.on('click', () => {
      const paramsList = [];
      $('.table tbody tr').each((i, v) => {
        const jsona = {
          isSelect: false,
        };
        $(v).find('input').each((index, el) => {
          jsona[el.name] = el.checked ? el.value : '';
          if (el.checked) {
            jsona[el.name] = el.value;
            jsona.isSelect = true;
          } else {
            jsona[el.name] = '';
          }
        });
        if (jsona.isSelect) {
          jsona.id = $(v).data('id');
          delete jsona.isSelect;
          paramsList.push(jsona);
        }
      });
      console.log(paramsList);
      conFigColumn({
        ukey: $.cookie('ukey'),
        check_auth: encodeURIComponent(JSON.stringify(paramsList)),
        admin_id: conf.id,
      }).then(json => {
        console.log(json);
        location.reload();
      }, json => {
        if (json.code === 1001) {
          alert('登录超时请重新登录');
          location.href = '/dashboard/login';
        }
      });
    });
  },
  columnList() {
    conFigColumnList({
      ukey: $.cookie('ukey'),
      admin_id: conf.id,
    }).then(json => {
      console.log(json);
      if (json.data) {
        $.each(json.data, (i, v) => {
          // console.log(v);
          // console.log(v);
          // console.log(v.id);
          // console.log($(`tr[data-id="${v.id}"]`));
          // console.log(`tr[data-id="${v.id}"]`);
          $(`tr[data-id="${v.id}"]`).find('input').each((index, el) => {
            const ele = el;
            if (!!v[ele.name]) {
              ele.checked = true;
            }
          });
        });
      }
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  merchantConFig() {
    jurisdictionList({
      ukey: $.cookie('ukey'),
    }).then(json => {
      let str = '';
      $.each(json.data, (i, v) => {
        str += `<tr data-id="${v.id}">
          <td class="istd">
            <label class="checkbox-inline">
              <input type="checkbox" name="column_name" value="${v.column_name}">
              ${v.column_name}
            </label>
          </td>
          <td>
            <label class="checkbox-inline">
              <input type="checkbox" name="column_api" value="${v.column_api}"> ${v.column_api}
            </label>
          </td>
          <td>
            <label class="checkbox-inline">
              <input type="checkbox" name="column_html" value="${v.column_html}">
              ${v.column_html}
            </label>
          </td>
        </tr>`;
      });
      this.$table.html(str);
      this.columnList();
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  adminList() {
    adminList({
      ukey: $.cookie('ukey'),
      id: conf.id,
    }).then(json => {
      console.log(json);
      this.$subheader.html(`资源绑定-->${conf.name}`);
    }, json => {
      console.log(json);
    });
  },
};
binding.init();
