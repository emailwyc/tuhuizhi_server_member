require('../../scss/member/iconslist.scss');
require('../bootstrap/modal');
// import { out } from '../modules/out.js';
import { getSquaredList, delSquared, setShow, getShow } from '../model/';
const $ = window.$;
const iconslist = {
  init() {
    this.initDom();
    this.initEvent();
    this.getSquaredList();
    this.state = {
      ctg: {},
    };
    this.getShow();
  },
  initDom() {
    this.$table = $('.table');
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$msg = $('.msg');
    this.$myModal = $('#myModal');
    // this.$setShow = $('.setShow');
    this.$layoutbox = $('.layoutbox');
    this.$radiobox = $('.radiobox');
    this.$setBtn = $('.setBtn');
  },
  initEvent() {
    this.$setBtn.on('click', () => {
      this.setShow();
    });
    this.$tbody.on('click', ('a.del'), (e) => {
      const target = $(e.target);
      console.log(target);
      this.state.ctg = {
        id: target.data('id'),
      };
    });
    this.$delOk.on('click', () => {
      this.delSquared();
    });
    this.$tbody.on('click', ('a.see'), (e) => {
      const target = $(e.target);
      this.state.ctg = {
        url: target.data('url'),
      };
      alert(this.state.ctg.url);
    });
  },
  getSquaredList() {
    getSquaredList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        console.log(v);
        console.log(v.isverify ? '是' : '否');
        tr += `<tr><td>${i + 1}</td><td>${v.order}</td><td>${v.title}</td>
        <td><img src="${v.logo}" class="imglogo" /></td><td>
        <a href="javascript:;" data-url="${v.url}" class="see">查看</a></td>
        <td>${v.isverify === '1' ? '是' : '否'}</td>
        <td>${v.isopenedactivity === '1' ? '是' : '否'}</td>
        <td>${v.istwolevel === '1' ? '是' : '否'}</td>
        <td><a href="#" class="briefing" title="${v.content}">${v.content}</a></td>
        <td>
        <a href="/member/icons?id=${v.id}" class="edit">编辑</a>
        <a href="#" class="del" data-toggle="modal" data-target="#myModal"
        data-id="${v.id}">删除</a>
        </td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td colspan="9">${json.msg}</td></tr>`);
    });
  },
  delSquared() {
    delSquared({
      key_admin: $.cookie('ukey'),
      sid: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      // alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
      this.$myModal.modal('hide');
    });
  },
  setShow() {
    setShow({
      key_admin: $.cookie('ukey'),
      // type: $("input[name='radio1']").attr('checked', 'true'),
      type: $('input[name="setlist"]:checked').val(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      setTimeout(() => {
        location.reload();
      }, 1000);
    }, json => {
      console.log(json);
    });
  },
  getShow() {
    getShow({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      const myid = json.data.myid - 1; // eq 从 0 开始
      let label = '';
      $.each(json.data.list, (i, v) => {
        console.log(v);
        label += `<label><input type="radio" name="setlist" value="${v.id}"  />${v.name}</label>`;
      });
      this.$radiobox.html(label);
      console.log(myid);
      if (myid >= 0) {
        $("input:radio[name='setlist']").eq(myid).attr('checked', 'checked');
      }
    }, json => {
      console.log(json);
    });
  },
};
iconslist.init();
