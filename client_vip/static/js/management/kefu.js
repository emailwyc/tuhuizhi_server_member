require('../../scss/management/kefu.scss');
require('../bootstrap/modal');
const $ = window.$;
import { contactustType, contactus, contactusDel, feedback } from '../model/';
// feedbackDel

// contactusCode ,contactustType ,getUploadToken

import { feeds } from './feeds';
let count = 1;
const MaxInputs = 3;

const UM = window.UM;

const um = UM.getEditor('myEditor', {
  toolbar: [],
  initialFrameWidth: 600,
  initialFrameHeight: 150,
  imagePopup: false,
  zIndex: 100,
  scaleEnabled: false,
  minFrameWidth: 200,
  autoHeightEnabled: false,
});
console.log(um);
const kefu = {
  init() {
    this.initDom();
    this.initEvent();
    this.page = 1;
    this.lines = 10;
    this.contactustType();
    feeds.init();
    // this.feedback();
  },
  initDom() {
    this.$feedback = $('.feedback');
    this.$wechatservice = $('.wechatservice');
    this.$phoneservice = $('.phoneservice');
    this.$servicedescription = $('.servicedescription');
    this.$phonename = $('.phonename');
    this.$phone = $('.phone');
    this.$subBtn = $('.subBtn');
    this.$servicedes = $('.servicedes');
    this.$wechatPhone = $('.wechatPhone');
    this.$qrcode = $('.qrcode');
    this.$myModal = $('#myModal');
    // tup
    this.$imghead = $('#imghead');
  },
  initEvent() {
    $('.add_1').on('click', () => {
      this.addForm();
    });
    this.$subBtn.on('click', () => {
      this.contactus();
    });
    $('.phonebox').on('click', 'a.edit', (e) => {
      const $target = $(e.target);
      $target.parents('.phoneservice_1').find('input').removeAttr('disabled');
    });

    $('.imges').on('click', 'a.edit', (e) => {
      const $target = $(e.target);
      $target.parents('.wechatservice_1').find('input').removeAttr('disabled');
    });
    $('.phonebox').on('click', 'a.btn', (e) => {
      const $target = $(e.target);
      this.$myModal.on('click', '.del', () => {
        this.$myModal.modal('hide');
        const data = {
          name: $target.parents('.phoneservice_1').find('input').eq(0).val(),
          customer_name: 'phoneservice',
        };
        this.contactusDel(data);
      });
    });
    $('.imges').on('click', 'a.btn', (e) => {
      const $target = $(e.target);
      console.log($target);
      this.$myModal.on('click', '.del', () => {
        this.$myModal.modal('hide');
        const data = {
          name: $target.parents('.wechatservice_1').find('input').eq(0).val(),
          customer_name: 'wechatservice',
        };
        this.contactusDel(data);
      });
    });

    $(':radio').change(
      () => {
        if ($(':radio')[0].checked === false) {
          $('.condition').attr('disabled', 'disabled');
          $('.sdate').removeAttr('disabled');
          $('.edate').removeAttr('disabled');
        } else {
          $('.condition').removeAttr('disabled');
          $('.sdate').attr('disabled', 'disabled');
          $('.edate').attr('disabled', 'disabled');
        }
      }
    );
  },
  contactus() {
    const arr = [];
    const arr1 = [];
    $('.phoneservice_1').each(
      function phoneservice(i) {
        arr[i] = { name: $(this).find(':text').val(), phoneno: $(this).find('.phone').val() };
      }
    );
    $('.wechatservice_1').each(
      function wechatservice(i) {
        arr1[i] = { name: $(this).find(':text').val(), qrcode: $(this).find('img').attr('src') };
      }
    );
    const forwechatservice = {
      wechatservice: {
        enable: $('.wechatservice').val() === 'true',
        server: arr1,
      },
    };
    const forphoneservice = {
      phoneservice: {
        enable: $('.phoneservice').val() === 'true',
        server: arr,
      },
    };
    contactus({
      key_admin: $.cookie('ukey'),
      feedback: JSON.stringify({ feedback: {
        enable: this.$feedback.val() === 'true',
        form: '',
      },
    }),
      wechatservice: JSON.stringify(forwechatservice),
      phoneservice: JSON.stringify(forphoneservice),
      servicedescription: JSON.stringify({ servicedescription: {
        enable: this.$servicedescription.val() === 'true',
        // description: $('#textarea').val(),
        description: um.getContent(),
      },
      }),
    }).then(json => {
      console.log(json);
      $('.msg').html(json.msg);
      setTimeout(() => {
        location.reload();
      }, 1000);
    }, json => {
      console.log(json);
      setTimeout(() => {
        $('.msg').html(json.msg);
      }, 1000);
    });
  },
  contactustType() {
    contactustType({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      this.$feedback.val(`${json.data.feedback.enable ? 'true' : 'false'}`);
      this.$wechatservice.val(`${json.data.wechatservice &&
         json.data.wechatservice.enable ? 'true' : 'false'}`);

      this.$phoneservice.val(`${json.data.phoneservice &&
        json.data.phoneservice.enable ? 'true' : 'false'}`);
      this.$servicedescription.val(
        `${json.data.servicedescription.enable ? 'true' : 'false'}`);
      um.setContent(json.data.servicedescription && json.data.servicedescription.description);
      $.each(json.data.phoneservice && json.data.phoneservice.server, (i, v) => {
        // console.log(v);
        $('.add_1').parents('.phoneservice1').before(`<div class="form-group row phoneservice_1">
         <div class="col-sm-offset-2 col-sm-2">
           <input type="text" value="${v.name}"
           class="form-control phonename${i}" disabled="true"  placeholder="填写电话名称">
         </div>
         <div class="col-sm-3">
           <input type="text" value="${v.phoneno}"
           class="form-control phone phonenum${i}" disabled="true" placeholder="填写电话号码">
         </div>
         <div class="editbox">
           <a data-edit="${i}" class="btn edit" href="javascript:;">编辑</a>
         </div>
         <div class="delbox">
           <a href="#" class="btn" data-toggle="modal" data-id="${i}" data-target="#myModal"
           class="del">删除</a>
         </div>
        </div>`);
      });
      if (json.data.wechatservice) {
        $.each(json.data.wechatservice.server, (i, v) => {
          console.log(json.data.wechatservice.server);
          $('.add').parents('.wechatservice1').before(`
            <div class="form-group row wechatservice_1">
              <div class="col-sm-offset-2 col-sm-2 colmarg">
                <input type="text" value="${v.name}"
                class="form-control wechat${count}" disabled="true" placeholder="填写客服名称">
              </div>
              <div class="col-sm-3 wechatqrcode">
                <img src="${v.qrcode}" class="img${count}" />
              </div>
              <div class="col-sm-3">
              <a href="#" class="btn" data-toggle="modal" data-id="${i}" data-target="#myModal"
              class="del">删除</a>
              </div>
            </div>
          `);
        });
      }
    });
  },
  addForm() {
    const x = $('.phoneservice_1').length;
    if (x <= MaxInputs) {
      count ++;
      $('.add_1').parents('.phoneservice1').before(`<div class="form-group row phoneservice_1">
       <div class="col-sm-offset-2 col-sm-2">
         <input type="text" value="${this.$phonename.val()}"
         class="form-control phonename${count}"  placeholder="填写电话名称">
       </div>
       <div class="col-sm-3">
         <input type="text" value="${this.$phone.val()}"
         class="form-control phone phonenum${count}"  placeholder="填写电话号码">
       </div>
      </div>`);
      this.$phonename.val('');
      this.$phone.val('');
    } else {
      alert('最多只能添加4个');
    }
  },
  contactusDel(prams) {
    console.log(prams);
    contactusDel({
      key_admin: $.cookie('ukey'),
      name: prams.name,
      customer_name: prams.customer_name,
    }).then(json => {
      console.log(json);
      $('.msg').html(json.msg);
      setTimeout(() => {
        location.reload();
      }, 1000);
    }, json => {
      console.log(json);
    });
  },
  feedback() {
    feedback({
      key_admin: $.cookie('ukey'),
      page: this.page,
      lines: this.lines,
      start_time: $('.sdata').val(),
      end_time: $('.edata').val(),
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        tr += `<tr>
          <td><input type="checkbox" value="${v.id}"/></td>
          <td>${v.usermember}</td>
          <td>${v.content}</td>
          <td>${v.createtime}</td>
          <td><a href="/management/reply?id=${v.id}" class="reply" >回复</a>
          <a href="" class="details" data-id="${v.id}">详情</a>
          <a href="" class="del" data-id="${v.id}">删除</a></td>
        </tr>`;
      });
      $('.table tbody').html(tr);
      $('.currentpage').html(`当前第${json.page}页`);
    }, json => {
      console.log(json);
    });
  },
};
kefu.init();
