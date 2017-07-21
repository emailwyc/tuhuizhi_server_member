require('../../scss/message/messageList.scss');
import { getList, delMessage } from '../model/message';
const $ = window.$;
require('../modules/cookie')($);
const messageList = {
  init() {
    this.lines = 10;
    this.page = 1;
    this.getList();
    this.initDom();
    this.initEvent();
    this.status = {
      ctg: {},
    };
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$del = $('.del');
    this.$pageBox = $('.pager');
    this.$currentppage = $('.currentpage');
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$total = $('.total');
    this.$gopage = $('.gopage');
    this.$gopageBtn = $('.gopage_btn');
  },
  initEvent() {
    this.$tbody.on('click', 'a.see', (e) => {
      const target = $(e.target);
      this.status.ctg = {
        url: target.data('url'),
      };
      alert(this.status.ctg.url);
    });
    this.$tbody.on('click', 'a.del', (e) => {
      const target = $(e.target);
      this.status.ctg = {
        id: target.data('id'),
      };
      if (confirm('确定删除吗？') === true) {
        alert('删除成功');
        this.delMessage(this.status.ctg.id);
      } else {
        alert('已取消删除');
      }
    });
    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.getList(this.page);
      }
    });
    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.getList(this.page);
      }
    });
    this.$gopageBtn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = this.$gopage.val();
        this.getList(this.$gopage.val());
      }
    });
  },

  // 获取列表
  getList(pagenum) {
    getList({
      key_admin: $.cookie('ukey'),
      page: pagenum || 1,
      lines: this.lines,
    }).then(success => {
      console.log(success);
      let tr = '';

      $.each(success.data.data, (i, v) => {
        tr += `<tr>
          <td>${i + 1}</td>
          <td>
          <div class="title">${v.title}</div>
          </td>
          <td><img src="${v.picurl}" /></td>
          <td>${v.bigimg === '1' ? '是' : '否'}</td>
          <td>${v.isopen === '1' ? '是' : '否'}</td>
          <td><a href="javascript:;" data-url="${v.url}" class="see">查看</a></td>
          <td>${v.message_event_type === 'subscribe' ? '关注公众号事件' : '定位、进入公众号事件'}</td>
          <td>${v.sort}</td>
          <td>
            <a href="/Message/addMsg?id=${v.id}" data-id="${v.id}">修改</a>
            <a href="javascript:;" data-id="${v.id}" class="del">删除</a>
          </td>
        </tr>`;
      });
      this.$tbody.html(tr);
      console.log(success.data.data.length);
      if (success.data.data.length > 0 && success.data.count_page > 1) {
        this.$pageBox.css('display', 'block');
        console.log(success.data.page);
        this.$currentppage.html(`当前第${success.data.page}页`);
        this.$total.html(`共${success.data.count}条记录`);
        this.totalPage = success.data.count_page;
        this.$gopage.val('');
      } else {
        this.$pageBox.css('display', 'none');
      }
    }, error => {
      console.log(error);
      this.$currentppage.html('当前第0页');
    });
  },
  delMessage(id) {
    delMessage({
      key_admin: $.cookie('ukey'),
      id,
    }).then(json => {
      console.log(json);
      this.getList();
    }, json => {
      console.log(json);
    });
  },
};
messageList.init();
