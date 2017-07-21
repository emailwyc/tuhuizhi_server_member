require('../../scss/point/point.scss');
import { integrallist, integraltype, getintegralone, integralSave, getStore } from '../model';
import { out } from '../modules/out.js';
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
const integral = {
  init() {
    // this.getintegralone();
    this.initDom();
    this.initEvent();
    this.state = {
      ctg: {},
    };
    const dayBefore = new Date();
    dayBefore.setDate(dayBefore.getDate() - 30);
    const getLocalTime = (day) => {
      const y = day.getFullYear();
      const moon = (day.getMonth() + 1) < 10 ? `0${day.getMonth() + 1}` : day.getMonth() + 1;
      const d = day.getDate() < 10 ? `0${day.getDate()}` : day.getDate();
      return `${y}-${moon}-${d}`;
    };
    this.$start[0].value = getLocalTime(dayBefore);
    this.$end[0].value = getLocalTime(new Date());
    this.integralList();
  },
  initDom() {
    this.$out = $('.out');
    this.$myModal = $('#myModal');
    this.$gridSystemModal = $('#gridSystemModal');
    // this.$addBtn = $('.addBtn');
    this.$table = $('.table tbody');
    this.$content = $('.modal-content');
    this.$modalbody = $('.modal-body');
    this.$container = $('.container-fluid');
    this.$integralpage = $('.page_data');
    this.$prepage = $('.pre_page');
    this.$dowpage = $('.dow_page');
    this.$mobile = $('.mobile');
    this.$store = $('.store');
    this.$money = $('.money');
    this.$submBtn = $('.submbtn');
    this.$msg = $('.msg');
    this.$start = $('.start');
    this.$end = $('.end');
    this.$pageout = $('.pageout');
    this.$query = $('.query');
    this.$total = $('.total');
    this.$currentppage = $('.currentpage');
    this.$loading = $('.loading');
    this.$gopage = $('.gopage');
    this.$gopage_btn = $('.gopage_btn');
    this.$storelist = $('.storelist ul');
    this.$getList = $('.getList');
    this.$queryBtn = $('.queryBtn');
    this.$querystatus = $('.querystatus');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$store.on('keyup', () => {
      this.getStore();
    });
    this.$getList.on('click', 'li span', (e) => {
      const target = $(e.target);
      const tartext = target.text();
      this.$store.val(tartext);
      this.$getList.hide();
    });
    this.$store.focus(() => {
      this.$getList.show();
    });
    this.$submBtn.on('click', () => {
      this.integralSave();
    });
    this.$gopage_btn.on('click', () => {
      this.integralList('', this.$gopage.val() - 0);
    });

    // 添加搜索查询
    this.$queryBtn.on('click', () => {
      this.integralList();
    });
    this.$myModal.on('click', '.del', () => {
      this.$optionsRadios = $('.radio input[name="optionsRadios"]:checked').val();
      this.$Scorenumber = $('#Scorenumber').val();
      this.$idnumberInputEmail = $('#idnumberInputEmail').val();
      this.$cardno = $('.user_mobile').val();
      this.$usermember = $('.usermember').val();
      this.$id = $('.type_id').val();
      this.$loading.css('display', 'block');
      this.integraltype();
      // alert(this.$id);
    });
    this.$container.on('click', '.examine', (e) => {
      const $target = $(e.target);
      getintegralone({
        key_admin: $.cookie('ukey'),
        id: $target.data('id'),
      }).then((json) => {
        console.log(json);
        let con = '';
        con += `<div style="float:left;"><img src="${json.data.img_src}"
        style="width:300px;height:250px;"/></div>`;
        con += `<div style="float:right;">
        <input type="hidden" value="${json.data.username}" class="usermember"/>
        用户名:
        <span><input type="hidden" value="${json.data.cardno}"
        class="user_mobile"/>${json.data.user_mobile}</span></br>
        <input type="hidden" value="${json.data.id}" class="type_id"/>
        申请时间:
        ${json.data.createtime}</br>
        <div class="radio">
          <label>
            <input type="radio" name="optionsRadios" class="optionsRadios1"
            value="2" checked>
            补录
          </label>
          <label >
            <input type="radio" name="optionsRadios" class="optionsRadios1"
            value="3">
            不补录
          </label>
        </div>
        订单号:<input type="text" class="form-control ordernumber"
        value="${json.data.ordernumber} "><br/>
        门店名称:<input type="text" class="form-control store"
        value="${json.data.store ? json.data.store : ''}"><br/>
        金额:<input type="text" class="form-control money"
        value="${json.data.money ? json.data.money : '0.00'}"><br/>
        `;
        con += `<span class="noMakeup">积分数:</span>
        <input type="text" class="form-control noMakeup score_number"
        id="Scorenumber" value="${json.data.score_number}"
        style="width:100px;height=10px;"><br/>`;
        this.$modalbody.html(con);
        $(':radio').change(
          () => {
            console.log(1111);
            if ($(':radio')[0].checked === false) {
              $('.noMakeup').hide();
            } else {
              $('.noMakeup').show();
            }
          }
        );
      }, json => {
        if (json.code === 502) {
          alert(json.msg);
          location.href = '/user/login';
          return;
        }
        if (json.code === 1030) {
          alert('可能网络过慢,请刷新页面再重试');
        }
      });
    });
    this.$prepage.on('click', () => {
      this.$numpage = this.$integralpage.val();
      this.$page = this.$numpage - 1;
      this.integralList();
    });
    this.$dowpage.on('click', () => {
      this.$numpage = this.$integralpage.val();
      const pars = +this.$numpage + 1;
      this.$page = pars;
      this.integralList();
    });
    this.$pageout.on('click', () => {
      this.integralList(1);
    });
    this.$query.on('click', () => {
      this.integralList();
    });
  },
  integralList(ex = '', page) {
    integrallist({
      key_admin: $.cookie('ukey'),
      starttime: this.$start.val() ? this.$start.val() : '',
      endtime: this.$end.val() ? this.$end.val() : '',
      page: page || this.$page || 1,
      export: ex,
      status: this.$querystatus.val(),
    }).then((json) => {
      console.log(json);
      if (json.data.path) {
        window.location.href = json.data.path;
      } else {
        let td = '';
        let act = '';
        let pageid = 1;
        $.each(json.data.data, (i, v) => {
          const dt = v;
          if (dt.status === '1') {
            dt.status = '<font style="color:#1C86EE">等待审核</font>';
            act = `<a href="javascript:;" class="examine" style="color:#1C86EE"
            data-toggle="modal" data-target="#myModal" data-id="${v.id}">审核</a>`;
          } else if (dt.status === '2') {
            dt.status = '<font style="color:#666">已补录</font>';
            act = '<span style="color:#666"></span>';
          } else if (dt.status === '3') {
            dt.status = '<font style="color:#666">取消补录</font>';
            act = '<span style="color:#666"></span>';
          }
          td += `<tr>
          <td>${pageid++}</td>
          <td scope="row">${v.createtime}</td>
          <td>${v.cardno}</td>
          <td class="textleft">${v.user_mobile}</td>
          <td>${dt.status}</td>
          <td>${v.score_number}</td>
          <td>${v.money ? v.money : '0.00'}</td>
          <td>${v.store ? v.store : ''}</td>
          <td>${v.ordernumber}</td>
          <td>${v.opertime}</td>
          <td>${v.backend_user}</td>
          <td>${act}</td>
          </td></tr>`;
          this.$table.html(td);
        });
        this.$integralpage.val(json.data.page);
        this.$currentppage.html(`第${json.data.page}页/共${json.data.count}页`);
        $('.page_type_count').html(`第${json.data.page}页/共${json.data.count}页`);
        this.$total.html(`共${json.data.total}条记录`);
      }
    }, json => {
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
      if (json.code === 102) {
        this.$table.empty();
      }
    });
  },

  integraltype() {
    integraltype({
      key_admin: $.cookie('ukey'),
      type_id: this.$id,
      order_number: $('.ordernumber').val(),
      score_number: $('.score_number').val(),
      username: this.$usermember,
      status: this.$optionsRadios,
      cardno: this.$cardno,
      money: $('.money').val(),
      store: $('.store').val(),
    }).then((json) => {
      console.log(json);
      this.$loading.css('display', 'none');
      alert('成功');
      this.$myModal.modal('hide');
      // location.href();
      this.$page = this.$integralpage.val();
      this.integralList();
      // this.$table.html(td);
    }, json => {
      this.$loading.css('display', 'none');
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
      if (json.code === 1008) {
        alert('订单号已存在');
      }
      if (json.code === 104) {
        alert('失败,请重试');
        this.$myModal.modal('hide');
      }
      if (json.code === 1030) {
        $('#idnumberInputEmail').val('');
        $('#Scorenumber').val();
        alert('订单号和积分数不能为空');
      }
      if (json.code === 1019) {
        $('#idnumberInputEmail').val('');
        alert('订单号不能为空');
      }
    });
  },
  integralSave() {
    integralSave({
      key_admin: $.cookie('ukey'),
      mobile: this.$mobile.val(),
      store: this.$store.val(),
      money: this.$money.val(),
      order_number: $('.order_number').val(),
      score_number: $('.scorenumber').val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      this.$gridSystemModal.modal('hide');
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 2000) {
        alert(`${json.msg},请让用户重新注册会员`);
        this.$gridSystemModal.modal('hide');
        return;
      }
      if (json.code === 1030) {
        this.$msg.html('请输入手机号');
        return;
      }
      this.$msg.html(json.msg);
    });
  },
  getStore() {
    getStore({
      key_admin: $.cookie('ukey'),
      name: this.$store.val(),
    }).then(json => {
      console.log(json);
      let list = '';
      $.each(json.data, (i, v) => {
        console.log(v);
        list += `<li>门店名称：<span class="storename">${v.poi_name}</span>  </li>`;
      });
      this.$storelist.html(list);
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },
};
integral.init();
