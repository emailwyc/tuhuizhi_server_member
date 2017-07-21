require('../../scss/survey/surveydetails.scss');
import { getsurveydetails } from '../model/survey';

const $ = window.$;
require('../modules/cookie')($);
const record = {
  init() {
    this.initDom();
    this.initEvent();
    this.getdetails();
  },
  initDom() {
    this.$details = $('#details');
  },
  initEvent() {
    // console.log('initEvent函数');
  },
  getdetails() {
    const url = window.location.href;
    const pId = url.substring(url.indexOf('=') + 1);
    const keyadmin = $.cookie('ukey');
    console.log(pId);
    getsurveydetails({
      key_admin: keyadmin,
      paperId: pId,
    }).then((json) => { // 判断用户是否登录，以及相关权限。
      console.log(json);
      const data = json.data;
      let listr = '';
      if (data === 4001) {
        location.href = '/user/login';
        alert('用户不存在或者登录超时，请重新登录');
      } else if (data === 4002) {
        location.href = '/user/admin';
        alert('你未被授权');
      } else {
        console.log(data.length);
        for (let i = 0; i < data.length; i++) {
          let totalnum = 0;
          if (data[i].questionType === '0') { // 单选
            listr += `<li class="detailsli">
                        <label for="" class="titleName">${i + 1}.${data[i].questionTitle}</label>
                        <span class="type">（单选）</span>`;
            for (let n = 0; n < data[i].answer.length; n++) {  // 计算总票数
              totalnum += parseInt(`${data[i].answer[n].ticket}`, 10);
            }
            if (totalnum === 0) {
              totalnum = 1;
            }
            for (let j = 0; j < data[i].answer.length; j++) {
              listr += `<p class="listp">
                          <span class="optionsName">${data[i].answer[j].contents}</span>
                          <span class="nummap" style="width:${data[i].answer[j].ticket}px"></span>
                          <span class="num">${data[i].answer[j].ticket}票/
                          占${Math.round(data[i].answer[j].ticket / totalnum * 100)}%</span>
                        </p>`;
            }
          } else if (data[i].questionType === '1') { // 多选
            listr += `<li class="detailsli">
                        <label for="" class="titleName">${i + 1}.${data[i].questionTitle}</label>
                        <span class="type">（多选）</span>`;
            for (let n = 0; n < data[i].answer.length; n++) {
              totalnum += parseInt(`${data[i].answer[n].ticket}`, 10);
            }
            if (totalnum === 0) {
              totalnum = 1;
            }
            for (let j = 0; j < data[i].answer.length; j++) {
              listr += `<p class="listp">
                          <span class="optionsName">${data[i].answer[j].contents}</span>
                          <span class="nummap" style="width:${data[i].answer[j].ticket}px"></span>
                          <span class="num">${data[i].answer[j].ticket}票/
                          占${Math.round(data[i].answer[j].ticket / totalnum * 100)}%</span>
                        </p>`;
            }
          } else if (data[i].questionType === '2') { // 问答
            listr += `<li class="detailsli answers">
                        <label for="" class="titleName">${i + 1}.${data[i].questionTitle}</label>
                        <span class="type">（问答）</span>`;
            for (let j = 0; j < data[i].answer.length; j++) {
              listr += `<p class="answer"><span>答：</span>${data[i].answer[j].answerContents}</p>`;
            }
          }
          listr += '</li>';
        }
        this.$details.append(listr);
      }
    });
  },
};
record.init();
