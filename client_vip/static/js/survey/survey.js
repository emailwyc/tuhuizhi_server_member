require('../../scss/survey/survey.scss');
const $ = window.$;
const cPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? `http://h5.rtmap.com/zhihuitu/survey/declare?scanCode=code&key_admin=${$.cookie('ukey')}` : `http://h2.rtmap.com/zhihuitu/survey/declare?scanCode=code&key_admin=${$.cookie('ukey')}`;
require('../modules/qrcode')($);
import { delsurvey, initsurvey, checksurvey } from '../model/survey';
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
const page = {
  key_admin: keyadmin,
  page: 1,
};
const Interfaceswitch = { // 调取接口开关
  switchid: 0,
};
const record = {
  init() {
    this.initDom();
    this.initEvent();
    this.getlist(initsurvey);
  },
  initDom() {
    this.$ul = $('#contentul');
    this.$delsurvey = $('#delsurvey');
    this.$checkbtn = $('#checkbtn');
    this.$prev = $('#prev');
    this.$next = $('#next');
    this.$allnum = $('#allnum');
    this.$currentpage = $('#currentpage');
  },
  initEvent() {
    // 删出问卷操作
    this.$ul.on('click', (event) => {
      const e = event || window.event;
      const target = e.target || e.srcElement;
      const id = `#${target.id}`;
      const oli = $(id).parents('li');
      if (target.tagName === 'P') {
        if (confirm('确定要删除吗？')) {
          console.log(new Date());
          delsurvey({  // 删除问卷接口
            paperId: target.id,
            key_admin: keyadmin,
          }).then((back) => {  // 此处要添加用户是否登录状态的判断
            if (back.data === 0) {
              oli.remove();
              alert('删除成功');
              location.reload();
            } else {
              alert('删除失败,请检查服务器或者网络');
            }
            // console.log(back);
          });
        }
      } else if (target.tagName === 'A') {  // 查看详情
        location.href = `/survey/surveyuserlist?paperId=${target.id}`;
      }
    });
    // 查询问卷操作
    this.$checkbtn.on('click', () => {
      this.$titleName = $('#titleName').val();
      // this.$titleType = $('#titleType').val();
      this.$surveyState = $('#surveyState').val();
      this.$starTime = $('#starTime').val();
      this.$endTime = $('#endTime').val();
      console.log(this.$titleName, this.$surveyState, this.$starTime, this.$endTime);
      if (this.$titleName === '' && this.$surveyState === '请选择' &&
       this.$starTime === '' && this.$endTime === '') {
        alert('请输入查询条件');
        Interfaceswitch.switchid = 0;
        this.getlist(initsurvey);
      } else if ((this.$starTime !== '' && this.$endTime === '') ||
                 (this.$starTime === '' && this.$endTime !== '')) {
        alert('开始时间和结束时间不能一个为空');
        Interfaceswitch.switchid = 0;
        this.getlist(initsurvey);
      } else {
        console.log(this.$titleName, this.$surveyState, this.$starTime, this.$endTime);
        page.paperTitle = this.$titleName;
        page.status = this.$surveyState;
        page.startTime = this.$starTime;
        page.endTime = this.$endTime;
        Interfaceswitch.switchid = 1;
        this.getlist(checksurvey);
        this.$currentpage.html(`${page.page}`);
      }
    });
    // 下一页
    this.$next.on('click', () => {
      page.page++;
      if (page.page > $('#allnum').html()) {
        page.page = $('#allnum').html();
        alert('已经是最后一页了');
      }
      this.$currentpage.html(`${page.page}`);
      if (Interfaceswitch.switchid === 0) {
        this.getlist(initsurvey);
      } else {
        this.getlist(checksurvey);
      }
    });
    // 上一页
    this.$prev.on('click', () => {
      if (page.page <= 1) {
        alert('已经是第一页了');
        page.page = 1;
      } else {
        page.page--;
      }
      this.$currentpage.html(`${page.page}`);
      if (Interfaceswitch.switchid === 0) {
        this.getlist(initsurvey);
      } else {
        this.getlist(checksurvey);
      }
    });
  },
  loadQrcode() {
    $.each($('.qrcode-number'), (ind, item) => {
      const text = `${cPath}&paperId=${$(item).attr('data-number')}`;
      $(item).qrcode({ width: 140, height: 140, text });
    });
  },
  // 获取列表操作 ----初始化
  getlist(interfaces) {
    interfaces(page).then((json) => {
      console.log(json);
      const data = json.data;
      if (data === null) {
        this.$ul.html('未找到您要找到的数据');
        this.$allnum.html(0);
        this.$currentpage.html(0);
      } else if (data === 4001) {
        location.href = '/user/login';
        alert('用户不存在或者登录超时，请您重新登录');
      } else if (data === 4002) {
        location.href = '/user/admin';
        alert('你未被授权');
      } else {  // 此处要添加判断用户是否登录。else if(){}
        const len = data.length;
        const countpage = data[0].countPage;
        let cont = '';
        for (let i = 0; i < len; i++) {
          cont += `<li>
                    <label for="" class="titleName" id=${data[i].paperId}>
                    ${data[i].paperTitle}</label>
                    <div class="qrcode-number divfloat" data-number="${data[i].paperId}"
                    data-jumpLink="${data[i].jumpLink}"></div>
                    <div class="group-li divfloat">${data[i].group_name}</div>
                    <div class="surState divfloat">${data[i].status}</div>
                    <div class="surCreateTime divfloat">${data[i].createTime}</div>
                    <div class="surStarTime divfloat">${data[i].startTime}</div>
                    <div class="surEndTime divfloat">${data[i].endTime}</div>
                    <div class="options divfloat">
                      <a href="javascript:;" class="checkDetails" id=${data[i].paperId}>查看详情</a>
                      <a href="/survey/editsurver?paperId=${data[i].paperId}" class="surDel"
                      data-id="${data[i].paperId}">修改</a>
                      <p class="surDel" style="
                      display: inline-block; width: auto;
                      color: #0074d9" id="${data[i].paperId}">删除</p>
                    </div>
                  </li>`;
        }
        this.$ul.html(cont);
        this.$allnum.html(`${countpage}`);
        this.loadQrcode();
      }
    });
  },
};
record.init();
