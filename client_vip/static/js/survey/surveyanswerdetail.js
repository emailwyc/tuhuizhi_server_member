/* 大体逻辑
  因为这个页面只是显示数据，我们可以考虑用表格展示。处理起来比较简单。
  请求数据之后，根据每条数据的题目类型进行dom的渲染。单选和双选为一种类型，问答另外一种类型。
  选项默认为灰色，选中的为常规色；
  所有选项均不可更改。
  1.单双选时，只要是被标记为选中的，就设置为常规，（将单双选的类型显示在后面）；
  2.问答时，用户的答案显示在标签当中。（考虑用户的输入字体的长度，超出文本框进行滑动处理）。

  问卷问题可考虑采用表格的方式进行显示。整体看着比较整齐。
  问卷汇总详情需要更改：选项的高度不够出现滑动现象；
  选项的长度过长的时候出现和占比重叠现象。
  问答过多时，滚动处理
 */

require('../../scss/survey/surveyanswerdetail.scss');
import { useranswerdetail } from '../model/survey';
const $ = window.$;
require('../modules/cookie')($);
const answerdetail = {
  init() {
    this.initDom();
    this.getParameter();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
  },
  initEvent() {
    useranswerdetail({
      key_admin: this.keyadmin,
      paperid: this.paperid,
      userid: this.userid,
    }).then(json => {
      console.log(json);
      this.judgequesttiontype(json.data);
    }, json => {
      console.log(json);
      this.$tbody.append(json.msg);
    });
  },
  judgequesttiontype(data) {
    console.log(data);
    $.map(data, (val, i) => {
      if (val.questionType === '0' || val.questionType === '1') {
        this.selectDom(val, i);
        console.log(i);
        this.$tbody.append('<br/>');
      } else {
        this.answerDom(val, i);
        this.$tbody.append('<br/><br/>');
      }
    });
  },
  selectDom(data, i) {
    if (data.questionType === '0') {
      const questionname = `<tr>
                      <td style="color:black">${i + 1}.${data.questionTitle}<span>(单选)</span></td>
                            </tr>`;
      this.$tbody.append(questionname);
    } else {
      const questionname = `<tr>
                      <td style="color:black">${i + 1}.${data.questionTitle}<span>（多选)</span></td>
                            </tr>`;
      this.$tbody.append(questionname);
    }
    $.map(data.optionList, (val, j) => {
      if (val.status === 0) {
        const html = `<tr><td style="padding-left:10px">
        (${j + 1})${val.contents}&nbsp;&nbsp;[${val.optionId}]
        </td></tr>`;
        this.$tbody.append(html);
      } else {
        const html = `<tr>
            <td style="padding-left:10px;color:blue;font-weight:600">
            (${j + 1})${val.contents}&nbsp;&nbsp;[${val.optionId}]
            </td></tr>`;
        this.$tbody.append(html);
      }
    });
  },
  answerDom(data, i) {
    const questionname = `<tr>
                        <td style="color:black">${i + 1}.${data.questionTitle}<span>(问答)</span></td>
                          </tr>`;
    this.$tbody.append(questionname);
    const textarea = `<textarea disabled="disabled" style="margin-left:10px">${data.answer}
                      </textarea>`;
    this.$tbody.append(textarea);
  },
  getParameter() {                                              // 获取需要的参数
    this.keyadmin = $.cookie('ukey');
    this.paperid = this.getQueryString('paperId');
    this.userid = this.getQueryString('userid');
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
answerdetail.init();
