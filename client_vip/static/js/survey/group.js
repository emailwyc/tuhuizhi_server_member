require('../../scss/survey/group.scss');
require('../bootstrap/modal');
import { getQuesGroup, delQuesGroup } from '../model/survey.js';
const $ = window.$;
const main = {
  init() {
    // this.page = 1;
    this.initDom();
    this.initEvent();
    this.getQuesGroup();
    this.state = {};
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$myModal = $('#myModal');

    // this.pageBox = $('.page_box');
    // this.$gopage = $('.gopage');
    // this.$currentppage = $('.currentpage');
    // this.$total = $('.total');
    // this.$next = $('.next');
    // this.$prev = $('.prev');
    // this.$gopage_btn = $('.gopage_btn');
  },
  initEvent() {
    this.$tbody.on('click', ('a.del'), (e) => {
      main.$myModal.modal('show');
      const target = $(e.target);
      this.state.id = target.data('id');
    });
    this.$delOk.on('click', () => {
      this.delQuesGroup();
    });

    // this.$prev.on('click', () => {
    //   if (this.page === 1) {
    //     alert('已经是第一页了');
    //   } else {
    //     this.page --;
    //     this.getQuesGroup();
    //   }
    // });
    // this.$next.on('click', () => {
    //   console.log(this.page);
    //   if (this.page === this.totalPage) {
    //     alert('已经是最后一页了');
    //   } else {
    //     this.page ++;
    //     this.getQuesGroup();
    //   }
    // });
    // this.$gopage_btn.on('click', () => {
    //   if (this.$gopage.val() > this.totalPage) {
    //     alert('已超过总页数！');
    //     this.$gopage.val('');
    //   } else {
    //     this.page = parseInt(this.$gopage.val(), 10);
    //     this.getQuesGroup();
    //   }
    // });
  },
  getQuesGroup() {
    getQuesGroup({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      if (result.data.length > 0) {
        let tr = '';
        $.each(result.data, (i, v) => {
          tr += `<tr><td>${v.group_name}</td>
          <td>${v.group_des}</td>
          <td>
          <a href="/survey/groupadd?id=${v.id}" class="edit">编辑</a>
          <a href="javascript:;" class="del delete${v.id}" data-toggle="modal" data-id="${v.id}"
          >删除</a>
          </td></tr>`;
        });
        this.$tbody.html(tr);
        // if (result.data.data.length > 0 && result.data.pageall > 1) {
        //   this.pageBox.css('display', 'block');
        //   this.$currentppage.html(`当前第${result.data.curpage}页`);
        //   this.$total.html(`共${result.data.pageall}页`);
        //   this.totalPage = result.data.pageall;
        //   this.$gopage.val('');
        // } else {
        //   this.pageBox.css('display', 'none');
        // }
        // this.$gopage.val('');
      } else {
        this.$tbody.html('<tr><td colspan="3">没有数据</td></tr>');
      }
    }, error => {
      this.$tbody.html(`<tr><td colspan="3">${error.msg}</td></tr>`);
      // this.pageBox.css('display', 'none');
    });
  },
  delQuesGroup() {
    delQuesGroup({
      key_admin: $.cookie('ukey'),
      id: this.state.id,
    }).then(() => {
      this.$myModal.modal('hide');
      $(`.delete${main.state.id}`).parents('tr').remove();
    }, error => {
      alert(error.msg);
      this.$myModal.modal('hide');
    });
  },
};
main.init();
