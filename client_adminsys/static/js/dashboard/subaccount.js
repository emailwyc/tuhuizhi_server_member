require('../../scss/dashboard/subaccount.scss');
import { getPayChildList, delPayChild } from './model/';
import { out } from '../modules/out.js';
require('../modules/bootstrap/modal');
const $ = window.$;
const conf = window.conf;
const subaccount = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.getPayChildList();
    this.state = {
      ctg: {},
    };
    console.log(conf.id);
  },
  initDom() {
    this.$out = $('.out');
    this.$tbody = $('.table tbody');
    this.$gridSystemModal = $('#gridSystemModal');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$tbody.on('click', 'a.del', (e) => {
      const target = $(e.target);
      this.state.ctg = {
        subid: target.data('subid'),
      };
    });
    this.$gridSystemModal.on('click', ('.Okdel'), () => {
      this.delPayChild();
    });
  },
  getPayChildList() {
    getPayChildList({
      ukey: $.cookie('ukey'),
      adminid: conf.id,
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        console.log(v);
        tr += `<tr><td>${conf.name}</td><td>${v.buildid}</td><td>${v.floor}</td><td>${v.poi_no}</td>
        <td>${v.poi_name}</td><td>${v.pay_child_account}</td>
        <td><a href="/dashboard/editsubaccount?name=${conf.name}&id=${conf.id}&subid=${
          v.id}">修改 </a><a href="#" data-toggle="modal"
          data-target="#gridSystemModal" data-subid="${v.id}" class="del"> 删除</a></td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td colspan="7">${json.msg}</td></tr>`);
    });
  },

  delPayChild() {
    delPayChild({
      ukey: $.cookie('ukey'),
      id: this.state.ctg.subid,
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      this.$gridSystemModal.modal('hide');
    });
  },
};
subaccount.init();
