require('../../scss/peoplelist/userdetails.scss');
import { userdetails, getArea, editMember, delMember, scoreDetailed,
  scoreDel, renewScore, memScord, scoreSub, addScore, reasonList } from '../model/';
const $ = window.$;
const conf = window.conf;
let datadiv = '';
// let bOk = true;
console.log(conf.cardno);
require('../modules/cookie')($);
const userDetails = {
  init() {
    userDetails.lines = 3;
    this.startpage = 1;
    this.page2 = 0;
    this.initDom();
    this.userdetails();
    this.scoreList();
    this.memScord();
    this.reasonList();
    this.state = {
      ctg: {},
      stateid: '',
      delorrenew: '',
    };
    // this.lines = 5;
  },
  initDom() {
    this.$details = $('.details');
    this.$name = $('.name');
    this.$idcard = $('.idcard');
    this.$mobile = $('.mobile');
    this.$level = $('.level');
    this.$career = $('.career');
    this.$address = $('.address');
    this.$remark = $('.remark');
    this.$wechat = $('.wechat');
    this.$subBtn = $('.subBtn');
    this.$msg = $('.msg');
    this.$delUser = $('.delUser');
    this.$tbody = $('.table tbody');
    this.$score = $('.score');
    this.$total = $('.total');
    this.$currentpage = $('.currentpage');
    this.$scoreDel = $('.scoreDel');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$screenbox = $('.screen_box');
    this.$day = $('.day');
    this.$start = $('.start');
    this.$end = $('.end');
    this.$count = $('.count');
    this.$addscore = $('.addscore');
    this.$getselecte = $('.getselecte');
    this.$oksubBtn = $('.oksubBtn');
    this.$reducescore = $('.reducescore');
    this.$export = $('.export'); // 导出数据
    // 增加删除提示浮层
    this.$moda = $('.moda_2');
    this.$deleall = $('.deleall');
    this.$cancel = $('.cancel');
    this.$modayes = $('.moda_4');
    // 结束
    const date = new Date();
    const year = date.getFullYear();
    const mon = date.getMonth() + 1;
    const da = date.getDate();
    console.log(`${year}-${mon}-${da}`);
    this.$end.val(`${year}-${mon}-${da}`);
  },
  userdetails() {
    userdetails({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
    }).then(json => {
      const data = json.data.data;
      $.each(data, (i, v) => {
        // console.log(v);
        if (v.fromtype === 'input') {
          if (v.type === 'wechat') {
            datadiv += `<div class="form-group row">
              <label for="inputEmail3" class="col-sm-2
              form-control-label">${v.content} ${v.required ? '*' : ''}</label>
              <div class="col-sm-10">
                <input type="text" class="form-control ${v.type}"
                ${v.save ? 'disabled="disabled"' : ''} name="${v.type}"
                value="${v.default === '' ? '未绑定' : '已绑定'}" data-value="${v.default}"
                placeholder="${v.placeholder ? v.placeholder : ''}">
            </div></div>`;
          } else {
            this.formDom(v);
          }
        } else if (v.fromtype === 'select') {
          let select = '';
          let option = '';
          $.each(v.option, (item, op) => {
            $.each(op.value, (k, va) => {
              if (v.content !== '星座') {
                option += `<option value="${va.value}"
              ${parseInt(op.default, 10) === va.value ? 'selected' : ''}
              ${op.save ? 'disabled="disabled"' : ''}>${va.text}</option>`;} else {
                option += `<option value="${va.text}"
                ${op.default === va.text ? 'selected' : ''}
                ${op.save ? 'disabled="disabled"' : ''}>${va.text}</option>`;
              }
            });
            if (v.num === 3) {
              const sub = op.sub.substring(5, 6);
              $.each(op.optionvalue, (s, opt) => {
                option = `<option value="${opt.value}"
                ${parseInt(op.default, 10) === opt.value ? 'selected' : ''}
                ${op.save ? 'disabled="disabled"' : ''}>${opt.text}</option>`;
              });
              select += `<select class="${op.type}" value="${op.default}"
              isrequired="${op.required === true}"
              ${v.save ? 'disabled="disabled"' : ''}>${option}</select>
              <span>${sub}</span>
              `;
            } else {
              select += `<select class="${op.type}" value="${op.default}"
              ${op.save ? 'disabled="disabled"' : ''}
              isrequired="${op.required === true}" >${option}</select>`;
            }
          });
          datadiv += `<div class="form-group row">
            <label for="inputEmail3" class="col-sm-2
            form-control-label">${v.content}</label>
            <div class="col-sm-10">
            ${select}
          </div></div>`;
        } if (v.fromtype === 'hidden') {
          datadiv += `
            <div class="form-group row">
              <label for="inputEmail3" class="col-sm-2
              form-control-label">${v.content}</label>
              <div class="col-sm-10">
              <input type="hidden" class="form-control ${v.type}" name="${v.type}"
              value="${v.default}" placeholder="${v.placeholder ? v.placeholder : ''}">
            </div></div>
            `;
        } else if (v.fromtype === 'date') {
          datadiv += `
            <div class="form-group row">
              <label for="inputEmail3" class="col-sm-2
              form-control-label">${v.content}</label>
              <div class="col-sm-10">
              <input type="date" class="form-control ${v.type}" name="${v.type}"
              value="${v.default}" placeholder="${v.placeholder ? v.placeholder : ''}">
            </div></div>
            `;
        }
      });
      this.$details.html(datadiv);
      $.each($('.col-sm-10 select'), (i, v) => {
        if ($(v).attr('isrequired') === 'true') {
          $(v).parents('.form-group').find('.form-control-label').append(' * ');
        }
      });
      this.getArea();
    });
  },

  formDom(dom) {
    datadiv += `<div class="form-group row">
      <label for="inputEmail3" class="col-sm-2
      form-control-label">${dom.content} ${dom.required ? '*' : ''}</label>
      <div class="col-sm-10">
        <input type="text" class="form-control ${dom.type}"
        ${dom.save ? 'disabled="disabled"' : ''} name="${dom.type}"
        value="${dom.default}" placeholder="${dom.placeholder ? dom.placeholder : ''}">
    </div></div>`;
  },
  getArea() {
    getArea({
      key_admin: $.cookie('ukey'),
      province: $('.province').val(),
    }).then(json => {
      const data = json.data;
      let option = '';
      $.each(data, (i, v) => {
        option += `<option value="${v.code}"
        ${$('.province').attr('value') === v.code ? 'selected' : ''}>${v.name}</option>`;
      });
      $('.province').append(`${option}`);
      if (!($('.province').val() === '')) {
        this.getCity();
      }
    });
    this.initEvent();
  },
  getCity() {
    getArea({
      key_admin: $.cookie('ukey'),
      province: $('.province').val(),
      city: $('.city').val(),
    }).then(json => {
      const data = json.data;
      let option = '';
      $.each(data, (i, v) => {
        option += `<option value="${v.code}"
        ${$('.city').attr('value') === v.code ? 'selected' : ''}>${v.name}</option>`;
      });
      $('.city').append(`${option}`);
      this.getDistrict();
    });
  },
  getDistrict() {
    getArea({
      key_admin: $.cookie('ukey'),
      city: $('.city').val(),
    }).then(json => {
      const data = json.data;
      let option = '';
      $.each(data, (i, v) => {
        option += `<option value="${v.code}"
        ${$('.district').attr('value') === v.code ? 'selected' : ''}>${v.name}</option>`;
      });
      $('.district').append(`${option}`);
    });
  },
  initEvent() {
    $('.province').on('change', () => {
      $('.city').html('');
      $('.district').html('<option>--请选择区--</option>');
      getArea({
        key_admin: $.cookie('ukey'),
        province: $('.province').val(),
        city: $('.city').val(),
      }).then(json => {
        const data = json.data;
        let option = '';
        $.each(data, (i, v) => {
          option += `<option value="${v.code}">${v.name}</option>`;
        });
        $('.city').append(`<option>--请选择市--</option>${option}`);
      });
    });
    $('.city').on('change', () => {
      $('.district').html('');
      getArea({
        key_admin: $.cookie('ukey'),
        city: $('.city').val(),
      }).then(json => {
        const data = json.data;
        let option = '';
        $.each(data, (i, v) => {
          option += `<option value="${v.code}">${v.name}</option>`;
        });
        $('.district').append(`<option>--请选择区--</option>${option}`);
      });
    });
    $('#form').on('submit', (e) => {
      console.log(e);
      e.preventDefault();
      // const data = $('#form').serialize();
      editMember({
        key_admin: $.cookie('ukey'),
        name: $('.name').val(),
        idcard: $('.idcard').val(),
        mobile: $('.mobile').val(),
        level: $('.level').attr('disabled') ?
        $('.level option[selected]').val() : $('.level').val(),
        sex: `${$('.sex').attr('disabled') ? $('.sex option[selected]').val() : $('.sex').val()}`,
        star: $('.star').attr('disabled') ? $('.star option[selected]').val() : $('.star').val(),
        province: $('.province').val(),
        city: $('.city').val(),
        district: $('.district').val(),
        career: $('.career').val(),
        is_del: $('.is_del').attr('disabled') ?
        $('.is_del option[selected]').val() : $('.is_del').val(),
        remark: $('.remark').val(),
        address: $('.address').val(),
        wechat: `${$('.wechat').val() === '未绑定' ? '' : $('.wechat').attr('data-value')}`,
        cardno: $('.cardno').val(),
        email: $('.email').val(),
        birth: $('.birth').val(),
      }).then(json => {
        console.log(json);
        this.$msg.html(json.msg);
        setTimeout(() => {
          location.href = '/peoplelist/userlist';
        }, 1000);
      }, json => {
        console.log(json);
        this.$msg.html(json.msg);
      });
    });
    this.$delUser.on('click', () => {
      this.delUser();
    });
    this.$tbody.on('click', 'a.scoreDel', (e) => {
      const $target = $(e.target);
      this.state.ctg = {
        id: $target.data('id'),
      };
      if ($target.text() === '删除') {
        // this.scoreDel();
        $('.remindSuccess').html('删除成功');
        $('.remind').html('确定要删除吗');
        this.state.delorrenew = 'del';
      } else {
        $('.remindSuccess').html('恢复成功');
        $('.remind').html('确定要恢复吗');
        this.state.delorrenew = 'renew';
        // this.renewScore();
      }
      this.$moda.show();
    });
    this.$deleall.on('click', () => {
      this.$moda.hide();
      if (this.state.delorrenew === 'del') {
        this.scoreDel();
      } else {
        this.renewScore();
      }
    });
    this.$cancel.on('click', () => {
      this.$moda.hide();
    });
    this.$prev.on('click', () => {
      if (this.startpage <= 1) {
        alert('已经是第一页了');
        this.startpage = 1;
      } else {
        this.startpage --;
        this.scoreList(this.startpage);
      }
    });
    this.$next.on('click', () => {
      if (this.startpage >= Math.ceil(this.$total.html() / userDetails.lines)) {
        alert('已经是最后一页了');
        this.startpage = Math.ceil(this.$total.html() / userDetails.lines);
      } else {
        this.startpage ++;
        this.scoreList(this.startpage);
      }
    });
    this.$export.on('click', () => {        // 导出数据
      this.exportdata(this.startpage);
    });
    $(':radio').change(
      () => {
        if ($(':radio')[0].checked === false) {
          $('.day').attr('disabled', 'disabled');
          $('.start').removeAttr('disabled');
          $('.end').removeAttr('disabled');
        } else {
          $('.day').removeAttr('disabled');
          $('.start').attr('disabled', 'disabled');
          $('.end').attr('disabled', 'disabled');
        }
      }
    );
    this.$day.on('change', () => {
      this.scoreList();
    });
    this.$start.on('change', () => {
      this.scoreList2();
    });
    this.$end.on('change', () => {
      this.scoreList2();
    });
    this.$oksubBtn.on('click', () => {
      if (this.$reducescore.val() === '') {
        this.addScore();
      } else {
        this.scoreSub();
      }
    });
  },
  delUser() {
    delMember({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.href = '/peoplelist/userlist';
    }, json => {
      console.log(json);
    });
  },
// 积分列表
  scoreList(pagenum) {
    const day = new Date();
    day.setDate(day.getDate() - $('.day').val());
    scoreDetailed({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
      page: pagenum || '1',
      lines: userDetails.lines || 3,
      start_time: `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`,
      end_time: `${new Date().getFullYear()}-${new Date().getMonth() + 1}-${new Date().getDate()}`,
    }).then(json => {
      console.log(json);
      this.$currentpage.html(`当前第${json.data.page}页`);
      this.$total.html(json.data.total);
      const data = json.data.data;
      this.state.stateid = json.data.data;
      this.scoreListDom(data);
      // if (max === 'reduce') {
      //   if (this.startpage <= 1) {
      //     // alert(1);
      //     this.startpage = 1;
      //   } else {
      //     this.startpage --;
      //   }
      // } else {
      //   if (this.startpage >= Math.ceil(this.$total.html() / userDetails.lines)) {
      //     // alert('已经是最后一页了');
      //     this.startpage = Math.ceil(this.$total.html() / userDetails.lines);
      //   } else {
      //     this.startpage ++;
      //   }
      // }
    }, json => {
      if (json.code === 102) {
        this.$score.html('0');
        this.$tbody.html(`<tr><td colspan="5">${json.msg}</td></tr>`);
        $('.pager').css('display', 'none');
      }
      console.log(json);
    });
  },
  // 按照时间段搜索积分列表
  scoreList2(pagenum) {
    scoreDetailed({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
      page: pagenum || 1,
      lines: userDetails.lines || 3,
      start_time: $('.start').val(),
      end_time: $('.end').val(),
    }).then(json => {
      console.log(json);
      this.$currentpage.html(`当前第${json.data.page}页`);
      this.$total.html(`${json.data.total}`);
      const data = json.data.data;
      this.scoreListDom(data);
      $('.pager').css('display', 'block');
    }, json => {
      console.log(json);
      this.$tbody.html(`<td colspan="6">${json.msg}</td>`);
      $('.pager').css('display', 'none');
    });
  },

  // 导出数据
  exportdata(pagenum) {
    const day = new Date();
    day.setDate(day.getDate() - $('.day').val());
    let commingtime = '';
    if ($(':radio')[0].checked === false) {
      commingtime = $('.start').val();
    } else {
      commingtime = `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`;
      console.log(commingtime);
    }
    const data = {
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
      page: pagenum || '1',
      lines: userDetails.lines || 3,
      start_time: commingtime,
      end_time: `${new Date().getFullYear()}-${new Date().getMonth() + 1}-${new Date().getDate()}`,
      export: 'yes',
    };
    console.log(data);
    scoreDetailed(data).then(json => {
      console.log(json);
      window.location.href = json.data.url;
      alert('已下载');
    }, json => {
      alert(json.msg);
      console.log(json);
    });
  },
  // 积分列表dom
  scoreListDom(data) {
    let tr = '';
    $.each(data, (i, v) => {
      tr += `<tr>
        <td>
          <div class="user" data-cardno="${v.cardno}">
            <a href="/peoplelist/userdetails?cardno=${v.cardno}">
              <p class="username">${v.datetime}</p>
            </a>
          </div>
        </td>
        <td>${v.why}</td>
        <td>${v.store}</td>
        <td>${v.scorecode}</td>
        <td>
          <a href="javascript:;" data-id="${v.id}"
          class="scoreDel">${v.is_del === '1' ? '删除' : '恢复'}</a>
        </td>
      </tr>`;
    });
    this.$tbody.html(tr);
  },
  scoreDel() {
    console.log(conf.id);
    scoreDel({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
      ID: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      this.$modayes.show().fadeOut(1000);
      this.scoreList();
      // location.reload();
    }, json => {
      console.log(json);
    });
  },
  renewScore() {
    renewScore({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
      ID: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      this.$modayes.show().fadeOut(1000);
      this.scoreList();
    });
  },
  memScord() {
    memScord({
      key_admin: $.cookie('ukey'),
      cardno: conf.cardno,
    }).then(json => {
      console.log(json.data);
      this.$score.html(`${json.data.score_num}`);
      this.$count.html(`${json.data.count}`);
    });
  },

  reasonList() {
    reasonList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      let option = '';
      $.each(json.data, (i, v) => {
        option += `<option value="${i}">${v}</option>`;
      });
      this.$getselecte.html(option);
    }, json => {
      console.log(json);
    });
  },
  scoreSub() {
    scoreSub({
      key_admin: $.cookie('ukey'),
      score: this.$reducescore.val(),
      cardno: conf.cardno,
      type: this.$getselecte.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    });
  },
  addScore() {
    addScore({
      key_admin: $.cookie('ukey'),
      score: this.$addscore.val(),
      cardno: conf.cardno,
      type: this.$getselecte.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    });
  },
};
userDetails.init();
