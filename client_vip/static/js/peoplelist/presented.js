require('../../scss/peoplelist/presented.scss');
import { giveScore, getGiveScoreSetting } from '../model';  // 接口配置
import { getDatesInput } from '../modules/timestamp';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
// console.log(keyadmin);
const presented = {
  init() {
    this.initDom();
    this.initEvent();
    this.getGiveScoreSetting();
  },
  initDom() {
    this.$sure = $('.sure');                      // 确定按钮
    this.$preinput = $('.preinput');              // 赠送积分输入框
    this.$inputs = $("input[type='radio']");      // 赠送积分输入框
    this.$opend = $('.opend');                    // 开启
    this.$closeinput = $('.closeinput');          // 关闭
    this.$birthdayscorenum = $('.birthdayscorenum');
    this.$starttime = $('.starttime');
    this.$endtime = $('.endtime');
    this.$msg = $('.msg');
  },
  initEvent() {
    // 确定
    this.$sure.on('click', () => {
      const inputnum = this.$preinput.val();
      const isswitch = $('input:checked').val();
      console.log($('input:checked').val());
      console.log(keyadmin);
      if ($('.firstbox').find('input[name="first"]:checked').val() === '0') {
        giveScore({
          key_admin: keyadmin,
          isenable: isswitch,
          scorenum: inputnum,
          isbirthdayenable: $('.birthdaybox').find('input[name="birthdayoff"]:checked').val(),
          birthdayscorenum: this.$birthdayscorenum.val(),
          istimetotimeenable: $('.timebox').find('input[name="temi"]:checked').val(),
          timetotimescorenum: $('.timenum').val(),
          starttime: $('.starttime').val(),
          endtime: $('.endtime').val(),
        }).then(json => {
          console.log(json);
          alert(json.msg);
        }, json => {
          console.log(json);
          // alert(json.msg);
          this.$msg.html(json.msg);
        });
      } else {
        if (inputnum === '') {
          alert('请输入赠送的积分');
        } else {
          if (!this.regnum(inputnum)) {
            alert('输入必须是纯数字');
          } else {
            // 调用接口   传入key_admin 是否开启 1：开启  0：关闭
            giveScore({
              key_admin: keyadmin,
              isenable: isswitch,
              scorenum: inputnum,
              isbirthdayenable: $('.birthdaybox').find('input[name="birthdayoff"]:checked').val(),
              birthdayscorenum: this.$birthdayscorenum.val(),
              istimetotimeenable: $('.timebox').find('input[name="temi"]:checked').val(),
              timetotimescorenum: $('.timenum').val(),
              starttime: $('.starttime').val(),
              endtime: $('.endtime').val(),
            }).then(json => {
              console.log(json);
              alert(json.msg);
            }, json => {
              console.log(json);
              // alert(json.msg);
              this.$msg.html(json.msg);
            });
          }
        }
      }
    });
  },

  getGiveScoreSetting() {
    // 初始化数据
    getGiveScoreSetting({
      key_admin: keyadmin,
    }).then(json => {
      console.log(json);
      // if (json.data.isenable === 1) {
      //   this.$opend.attr({ checked: 'checked' });
      // } else if (json.data.isenable === 0) {
      //   this.$closeinput.attr({ checked: 'checked' });
      // }
      // this.$preinput.val(json.data.scorenum);

      $('.firstbox').find(`input[name="first"][value="${json.data.givescore.isenable}"]`)
      .attr('checked', true);
      $('.birthdaybox').find(`input[name="birthdayoff"][value="${json.data.birthday.isenable}"]`)
      .attr('checked', true);
      $('.timebox').find(`input[name="temi"][value="${json.data.timetotime.isenable}"]`)
      .attr('checked', true);
      this.$preinput.val(json.data.givescore.scorenum);
      this.$birthdayscorenum.val(json.data.birthday.scorenum);
      $('.timenum').val(json.data.timetotime.scorenum);
      this.$starttime.val(getDatesInput(json.data.timetotime.time.start));
      this.$endtime.val(getDatesInput(json.data.timetotime.time.endtime));
    }, json => {
      console.log(json);
      // alert(json.msg);
      // this.$msg.html(json.msg);
    });
  },
  /* 判断是否是数字 */
  regnum(num) {
    const reg = /^[0-9]*$/;
    const result = reg.test(num);
    return result;
  },
};
presented.init();
