require('../../scss/Yadmin/Yadmin.scss');
import { prizeList, integralOperation } from '../model/Ycurrency';
const $ = window.$;
require('../modules/cookie')($);
require('../bootstrap/modal');
const Yadmin = {
  init() {
    this.initDom();
    this.initEvent();
    this.prizeList();
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$prizeName = $('.prize_name');
    this.$des = $('.des');
    this.$integral = $('.integral');
    this.$save = $('.save');
  },
  initEvent() {
    this.$tbody.on('click', 'a.edit', (e) => {
      const target = $(e.target);
      this.state.ctg = {
        pid: target.data('pid'),
        main: target.data('name'),
        integral: target.data('integral'),
        des: target.data('des'),
      };

      this.$prizeName.val(this.state.ctg.main);
      this.$des.val(this.state.ctg.des);
      this.$integral.val(this.state.ctg.integral);
    });
    this.$save.on('click', () => {
      this.integralOperation();
    });
  },

  prizeList() {
    prizeList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        console.log(v);
        tr += `<tr>
          <td>${i + 1}</td>
          <td><img src="${v.imgUrl}" /></td>
          <td>${v.main}</td>
          <td>${v.startTime}</td>
          <td>${v.endTime}</td>
          <td>${v.pid}</td>
          <td>${v.integral}</td>
          <td><a href="javascript;:" class="edit" data-pid="${v.pid}" data-des="${v.des}"
          data-name="${v.main}" data-integral="${v.integral}" data-toggle="modal"
          data-target="#myModal" >编辑</a></td>
        </tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td colspan="8" style="color:red;">${json.data}</td></tr>`);
    });
  },

  integralOperation() {
    integralOperation({
      key_admin: $.cookie('ukey'),
      pid: this.state.ctg.pid,
      des: this.$des.val(),
      integral: this.$integral.val(),
      prize_name: this.$prizeName.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      $('#myModal').modal('hide');
      this.prizeList();
    }, json => {
      console.log(json);
    });
  },
};
Yadmin.init();
