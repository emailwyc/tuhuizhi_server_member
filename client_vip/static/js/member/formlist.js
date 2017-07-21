require('../../scss/member/formlist.scss');
const $ = window.$;
import { formList, setMyAutoForm } from '../model';
const key = $.cookie('ukey');
console.log(key);
const memberFormlist = {
  init() {
    this.changeKey = 1;
    this.initDom();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$register = $('.register');
    this.$modificat = $('.modificat');
    this.$setwarp = $('.setwarp');
    this.$cancelp = $('.cancelp');
    this.$cancelbtn = $('.cancelbtn');
    this.$submitbtn = $('.submitbtn');
  },
  initEvent() {
    this.getregister(this.changeKey);
    this.$register.on('change', () => {
      this.getregister(1);// 注册
    });
    this.$modificat.on('change', () => {
      this.getregister(0);//  修改
    });
    this.$tbody.on('click', '.set', (event) => {
      $('.submitbtn').prop('id', event.target.id);
      console.log($(event.target).attr('data-type'));
      this.setdom(this.backups, event.target.id, $(event.target).attr('data-type'));
    });
    this.$cancelp.on('click', () => {
      this.$setwarp.fadeOut('fast');
    });
    this.$cancelbtn.on('click', () => {
      this.$setwarp.fadeOut('fast');
    });
    this.$submitbtn.on('click', (event) => {
      console.log(this.changeKey);
      setMyAutoForm({
        key_admin: key,
        form_key_id: event.target.id,
        isenable: $('input[name=switch]').prop('checked') ? 1 : 0,
        isrequired: $('input[name=must]').prop('checked') ? 1 : 0,
        ischange: $('input[name=updatej]:checked').val(),
        placeholder: $('.hintmsg').val(),
        sub: $('.markingmsg').val(),
        value: $('.defaultmsg').val(),
        minlength: $('.minlen').val(),
        maxlength: $('.maxlen').val(),
        sort: $('.sort').val(),
        type: this.changeKey,
      }).then(json => {
        console.log(json);
        alert(json.msg);
        // window.location.reload();
        this.$setwarp.fadeOut('fast');
        this.getregister(this.changeKey);
      }, json => {
        console.log(json);
        alert(json.msg);
      });
    });
  },
  getregister(type) {
    this.changeKey = type;
    // 调用注册接口列表
    this.$tbody.html('');
    formList({
      key_admin: key,
      type: this.changeKey,
    }).then(json => {
      console.log(json);
      this.handledom(json.data);
      this.backups = json.data.mylist;
    }, json => {
      console.log(json);
      this.$tbody.html(json.msg);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
      }
    });
  },
  getmodificate() {
    // 调用修改接口列表
    this.$tbody.html('接口未出，暂不处理');
  },
  handledom(data) {
    $.map(data.form, n => {
      let selecthtml = '';
      let datasort = '';
      if (data.mylist.length >= 1) {
        $.map(data.mylist, v => {
          if (n.id === v.form_key_id) {
            selecthtml = n;
            console.log(v.sort);
            datasort = v.sort;
          }
        });
      }
      if (selecthtml !== '') {
        console.log(selecthtml);
        selecthtml = `<tr>
                   <td>${selecthtml.content}</td>
                   <td style="color:green">已选</td>
                   <td style="color:green">${datasort}</td>
                   <td><a href="javascript:;" class="set" id=${selecthtml.id}
                   data-type="${n.content_type}">设置</a></td>
                   </tr>`;
      } else {
        selecthtml = `<tr>
                   <td>${n.content}</td>
                   <td style="color:#ccc">未选</td>
                   <td style="color:green">${datasort}</td>
                   <td><a href="javascript:;" class="set" id=${n.id}
                   data-type="${n.content_type}">设置</a></td>
                   </tr>`;
      }
      this.$tbody.append(selecthtml);
    });
  },
  setdom(data, id, type) {
    let onlydata = '';
    $.map(data, n => {
      if (n.form_key_id === id) {
        onlydata = n;
      }
    });
    console.log(onlydata);
    console.log(type);
    if (type === 'radio' || type === 'checkbox') {
      $('.defaultmsg').parent().attr('style', 'display:none');
      $('.minlen').parent().attr('style', 'display:none');
      $('.maxlen').parent().attr('style', 'display:none');
    } else {
      $('.defaultmsg').parent().attr('style', 'display:block');
      $('.minlen').parent().attr('style', 'display:block');
      $('.maxlen').parent().attr('style', 'display:block');
    }
    if (onlydata !== '') {
      if (onlydata.isrequired === '1') {
        $('.ismust').prop('checked', true);
        $('.nomust').removeAttr('checked');
      } else if (onlydata.isrequired === '0') {
        $('.nomust').prop('checked', true);
        $('.ismust').removeAttr('checked');
      }
      if (onlydata.ischange === '0') {
        $('.updatey').prop('checked', true);
        $('.updaten').removeAttr('checked');
      } else if (onlydata.ischange === '1') {
        $('.updaten').prop('checked', true);
        $('.updatey').removeAttr('checked');
      }
      $('.switchyes').prop('checked', true);
      $('.switchno').removeAttr('checked');
      $('.hintmsg').val(onlydata.placeholder);
      $('.markingmsg').val(onlydata.sub);
      // if (type !== 'radio' || type !== 'checkbox') {
      //   $('.defaultmsg').prop('display', 'block');
      //   $('.minlen').prop('display', 'block');
      //   $('.maxlen').prop('display', 'block');
      // } else {
      //   $('.defaultmsg').prop('display', 'none');
      //   $('.minlen').prop('display', 'none');
      //   $('.maxlen').prop('display', 'none');
      // }
    } else {
      $('.switchno').prop('checked', true);
      $('.switchyes').removeAttr('checked');
      $('.nomust').prop('checked', true);
      $('.ismust').removeAttr('checked');
      $('.updatey').prop('checked', true);
      $('.updaten').removeAttr('checked');
      $('.hintmsg').val('');
      $('.markingmsg').val('');
      $('.defaultmsg').val('');
      $('.minlen').val('');
      $('.maxlen').val('');
    }
    this.$setwarp.fadeIn('fast');
  },
};
memberFormlist.init();
