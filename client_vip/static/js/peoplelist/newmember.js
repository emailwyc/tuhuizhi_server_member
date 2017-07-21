require('../../scss/peoplelist/newmember.scss');
import { wechatFieldList, getArea, regForm } from '../model/';
import input from './form/input';
// import sel from './form/select';
const $ = window.$;
require('../modules/cookie')($);
const newmember = {
  init() {
    this.initDom();
    this.getDynamicForm();
  },
  initDom() {
    this.$regdata = $('.regdata');
    this.$msg = $('.msg');
  },
  getDynamicForm() {
    wechatFieldList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      const data = json.data.function_name;
      let datadiv = '';
      $.each(data, (i, v) => {
        console.log(v);
        if (v.fromtype === 'input') {
          datadiv += input(v);
        } else if (v.formtype === 'date') {
          datadiv += input(v);
        } else if (v.fromtype === 'select') {
          let selectList = '';
          $.each(v.option, (o, p) => {
            let select = '';
            if (v.num !== 3) {
              let option = '';
              if (v.content !== '星座') {
                $.each(p.value, (item, ind) => {
                  if (p.type === 'is_del') {
                    option += `<option selected="selected"
                     value="${ind.value}">${ind.text}</option>`;
                  } else {
                    option += `<option value="${ind.value}">${ind.text}</option>`;
                  }
                });
                select += `<select class="c-select ${p.type}"
                >${option}</select>`;
                datadiv += `<div class="form-group row">
                 <label for="inputEmail3"
                 class="col-sm-2 form-control-label">
              ${v.content} ${p.required ? '*' : ''}</label>
                 <div class="col-sm-10">
                 ${select}
                 </div>
              </div>`;} else {
                $.each(p.value, (item, ind) => {
                  if (p.type === 'is_del') {
                    option += `<option selected="selected"
                     value="${ind.text}">${ind.text}</option>`;
                  } else {
                    option += `<option value="${ind.text}">${ind.text}</option>`;
                  }
                });
                select += `<select class="c-select ${p.type}"
                >${option}</select>`;
                datadiv += `<div class="form-group row">
                   <label for="inputEmail3"
                   class="col-sm-2 form-control-label">
                ${v.content} ${p.required ? '*' : ''}</label>
                   <div class="col-sm-10">
                   ${select}
                   </div>
                </div>`;
              }
              // datadiv += sel;
            } else {
              let option2 = '';
              const sub = p.sub.substring(5, 6);
              $.each(p.optionvalue, (item, ind) => {
                option2 += `<option value="">${ind.text}</option>`;
              });
              selectList += `<select class="c-select ${p.type}"
              isrequired="${p.required === true}">${option2}</select>
              <span style="margin-right:20px">${sub}</span>`;
            }
          });
          if (v.num === 3) {
            datadiv += `<div class="form-group row">
               <label for="inputEmail3" class="col-sm-2
               form-control-label">${v.content} ${v.required ? '*' : ''}</label>
               <div class="col-sm-10">
               ${selectList}
               </div>
            </div>`;
          }
        }
        this.$regdata.html(datadiv);
      });
      $.each($('.col-sm-10 select'), (i, v) => {
        if ($(v).attr('isrequired') === 'true') {
          $(v).parents('.form-group').find('.form-control-label').append(' * ');
        }
      });
      this.getArea();
    });
  },

  getArea() {
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
      $('.province').append(`${option}`);
      this.initEvent();
    });
  },
  initEvent() {
    $('.province').on('change', () => {
      $('.city').html('');
      getArea({
        key_admin: $.cookie('ukey'),
        province: $('.province').val(),
        city: $('.city').val(),
      }).then(json => {
        const data = json.data;
        let option = '';
        $.each(data, (i, v) => {
          // <option>--请选择市--</option>
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
          console.log(v);
          option += `<option value="${v.code}">${v.name}</option>`;
        });
        $('.district').append(`${option}`);
      });
    });
    $('#form').on('submit', (e) => {
      console.log(e);
      e.preventDefault();
      regForm({
        key_admin: $.cookie('ukey'),
        name: $('.name').val(),
        idcard: $('.idcard').val(),
        mobile: $('.mobile').val(),
        level: $('.level').val(),
        sex: $('.sex').val(),
        star: $('.star').val(),
        province: $('.province').val(),
        city: $('.city').val(),
        district: $('.district').val(),
        career: $('.career').val(),
        is_del: $('.is_del').val(),
        remark: $('.remark').val(),
        address: $('.address').val(),
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
  },
};
newmember.init();
