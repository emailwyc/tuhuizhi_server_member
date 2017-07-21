require('../../scss/survey/surveyuserlist.scss');
import { userList } from '../model/survey';
const $ = window.$;
require('../modules/cookie')($);
const exportPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
const userlist = {
  init() {
    this.initDom();
    this.getParameter();
    this.getuserlist();
    this.initEvent();
  },
  initDom() {
    this.$summarize = $('.summarize');                          // 查看汇总详情
    this.$exportdetail = $('#exportdetail');                    // 导出答题详情
    this.$tbody = $('tbody');                                   // tbody
    this.$titlename = $('.titlename');                          // 问卷标题
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$sure = $('.surenum');
    this.$currentval = $('.currentval');
    this.$totalnum = $('.totalnum');
    this.$currentnum = $('.currentnum');
  },
  initEvent() {
    this.$summarize.on('click', () => {
      location.href = `/survey/surveydetails?paperid=${this.paperid}`;
    });
    this.$exportdetail.attr('href', `${exportPath}/wenjuan/public/?s=Admin/QA/exportAnswer&key_admin=${this.keyadmin}&paperId=${this.paperid}`);
    this.$sure.on('click', () => {
      this.currentpage = this.$currentval.val();
      console.log(this.currentpage);
      this.getuserlist();
    });
    this.$prev.on('click', () => {
      this.currentpage--;
      if (this.currentpage >= 1) {
        this.getuserlist();
      } else {
        this.currentpage = 1;
        alert('已经是第一页了');
      }
    });
    this.$next.on('click', () => {
      this.currentpage++;
      if (this.currentpage <= parseInt(this.$totalnum.html(), 10)) {
        this.getuserlist();
      } else {
        this.currentpage = parseInt(this.$totalnum.html(), 10);
        alert('已经是最后一页了');
      }
    });
  },
  getuserlist() {
    const data = {
      key_admin: this.keyadmin,
      paperid: this.paperid,
      page: this.currentpage,
    };
    console.log(data);
    userList(data).then(json => {
      console.log(json);
      this.$titlename.html(json.data.ext.paperTitle);
      this.$totalnum.html(json.data.pageAll);
      $('.countAll').html(json.data.countAll);
      this.handledom(json.data.result);
      const datalen = json.data.result.length;
      if (datalen === 0) {
        this.$tbody.html('暂无数据');
      } else if (json.data.countAll > '10') {
        $('.pages').css({ display: 'block' });
      }
    }, json => {
      console.log(json);
      this.$tbody.html(json.msg);
    });
  },
  handledom(data) {
    this.$tbody.html('');
    $.map(data, (val) => {
      const html = `<tr>
        <td class="headpic"><img src="${val.headimg}" alt=""></td>
        <td class="uname">${val.nickname}</td>
        <td class="uopenid">${val.openid}</td>
        <td class="options">
        <a href="/survey/surveyanswerdetail?paperId=${this.paperid}&userid=${val.id}">查看答题详情</a>
        </td>
      </tr>`;
      this.$tbody.append(html);
      this.$currentnum.html(this.currentpage);
    });
  },
  getParameter() {                                              // 设置需要的参数
    this.keyadmin = $.cookie('ukey');
    this.paperid = this.getQueryString('paperId');
    this.currentpage = 1;
  },
  getQueryString(name) {
    const reg = new RegExp(`(^|&)${name}=([^&]*)(&|$)`);
    const r = window.location.search.substr(1).match(reg);
    if (r !== null) {
      return unescape(r[2]);
    }
    return null;
  },
};
userlist.init();
