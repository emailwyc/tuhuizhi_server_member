require('../../scss/message/templateMsgList.scss');
import { templateList, delTemplate, sendTemplateMessage } from '../model/message';
const $ = window.$;
require('../modules/cookie')($);
let bOk = true;
const templateMsgList = {
  init() {
    this.page = 1;
    this.lines = 10;
    this.templateList();
    this.status = {
      ctg: {},
    };
    this.initDom();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$pageBox = $('.pager');
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$gopageBtn = $('.gopage_btn');
    this.$gopage = $('.gopage');
    this.$currentpage = $('.currentpage');
    this.$total = $('.total');
  },
  initEvent() {
    this.$tbody.on('click', 'a.del', (e) => {
      const target = $(e.target);
      console.log(target);
      this.status.ctg = {
        id: target.data('id'),
        templateid: target.data('templateid'),
      };
      if (confirm('确定删除吗？') === true) {
        alert('删除成功');
        this.delTemplate();
      } else {
        alert('取消删除');
      }
    });

    this.$tbody.on('click', 'a.pushid', (e) => {
      const target = $(e.target);
      this.status.ctg = {
        id: target.data('id'),
        templateid: target.data('templateid'),
      };
      if (bOk) {
        bOk = false;
        target.addClass('de');
        this.sendTemplateMessage();
      }
    });

    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.templateList(this.page);
      }
    });
    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.templateList(this.page);
      }
    });
    this.$gopageBtn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = this.$gopage.val();
        this.templateList(this.$gopage.val());
      }
    });
  },
  templateList(pagenum) {
    templateList({
      key_admin: $.cookie('ukey'),
      page: pagenum || 1,
      lines: this.lines,
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data.data, (i, v) => {
        tr += `<tr>
          <td>${i + 1}</td>
          <td>${v.templateid}</td>
          <td>${v.title}</td>
          <td><a href="javascript:;" data-id="${v.id}" class="pushid"
          data-templateid="${v.templateid}">推送</a>
            <a href="/Message/addTemplateMsg?id=${v.id}">编辑</a>
            <a href="javascript:;" data-id="${v.id}"
            data-templateid="${v.templateid}" class="del">删除</a>
          </td>
        </tr>`;
      });
      this.$tbody.html(tr);
      if (json.data.data.length > 0 && json.data.page_count > 1) {
        this.$pageBox.css('display', 'block');
        this.$currentpage.html(`当前第${json.data.page}页`);
        this.$total.html(`共${json.data.count}条记录`);
        this.totalPage = json.data.page_count;
        this.$gopage.val('');
      } else {
        this.$pageBox.css('display', 'none');
      }
    }, json => {
      console.log(json);
    });
  },

  delTemplate() {
    delTemplate({
      key_admin: $.cookie('ukey'),
      templateid: this.status.ctg.templateid,
      id: this.status.ctg.id,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      this.templateList();
    }, json => {
      console.log(json);
    });
  },

  sendTemplateMessage() {
    sendTemplateMessage({
      key_admin: $.cookie('ukey'),
      id: this.status.ctg.id,
      templateid: this.status.ctg.templateid,
    }).then(json => {
      console.log(json);
      $('.pushid').removeClass(' de');
      bOk = true;
    }, json => {
      console.log(json);
      $('.pushid').removeClass(' de');
      bOk = true;
    });
  },
};
templateMsgList.init();
