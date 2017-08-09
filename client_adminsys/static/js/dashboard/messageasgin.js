require('../../scss/dashboard/message.scss');
const hogan = require('hogan.js');
const tplHtml = require('./tpls/message.html');
import { getMsgSign, getUnMsgSign, addMsgSign, getSignById,
   editMsgSign, delMsgSign } from './model';
import { out } from '../modules/out.js';
require('../modules/bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);

const main = {
  init() {
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.page = 1;
    this.initDom();
    this.getData();
    this.eventFun();
    this.state = {
      id: null,
    };
  },
  initDom() {
    this.$out = $('.out');
    this.$newadd = $('#newadd');
    this.$tbody = $('.table tbody');

    this.$myModal = $('#myModal');
    this.$myModal = $('#myModal'); // 修改弹框
    this.$merchantName = $('#merchantName');
    this.$columnApi = $('#column_api');
    this.$columnApiName = $('#column_api_name');

    this.$gridSystemModal = $('#gridSystemModal'); // 删除弹框
    this.$myModal1 = $('#myModal1'); // 添加弹框
    this.$columnApi1 = $('#column_api1');
    this.$columnApiName1 = $('#column_api_name1');
    this.$merchantName1 = $('#merchantName1');

    this.$table = $('.table-responsive');

    this.$search = $('.form-control'); // 搜索内容
    this.$searchBtn = $('.btn'); // 搜索按钮

    this.pageBox = $('.page_box');
    this.$gopage = $('.gopage');
    this.$currentppage = $('.currentpage');
    this.$total = $('.total');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$gopage_btn = $('.gopage_btn');
  },
  eventFun() {
    this.$out.on('click', out);
    this.$table.on('click', 'a', (e) => {
      const $target = $(e.target);
      this.state.id = $target.data('id');
    });
    this.$newadd.bind('click', this.addFun);
    this.$myModal1.on('click', '.save1', this.addMsgSign);
    this.$myModal.on('click', '.save', this.editMsgSign);
    this.$table.on('click', 'a.edit', this.getDataById);
    this.$table.on('click', 'a.delete_btn', this.deleteShowFun);
    this.$gridSystemModal.on('click', '.del', this.deleteFun);
    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.getData(this.page);
      }
    });
    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.getData(this.page);
      }
    });
    this.$searchBtn.on('click', () => {
      this.getData();
    });
    this.$gopage_btn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = this.$gopage.val();
        this.getData(this.$gopage.val());
      }
    });
  },
  addMsgSign() {
    const sign = main.$columnApi1.val();
    if (!sign) {
      alert('请输入商户签名！');
      return;
    }
    const interName = main.$columnApiName1.val();
    if (!interName) {
      alert('请输入短信接口名称！');
      return;
    }
    addMsgSign({
      id: main.$merchantName1.find('option:selected').val(),
      sign,
      interName,
      ukey: $.cookie('ukey'),
    }).then(() => {
      main.getData();
      main.$myModal1.modal('hide');
    }, error => {
      console.log(error);
      if (error.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      } else {
        alert(error.msg);
      }
    });
  },
  editMsgSign() {
    const sign = main.$columnApi.val();
    if (!sign) {
      alert('请输入商户签名！');
      return;
    }
    const interName = main.$columnApiName.val();
    if (!interName) {
      alert('请输入短信接口名称！');
      return;
    }
    editMsgSign({
      sign,
      id: main.state.id,
      interName,
      ukey: $.cookie('ukey'),
    }).then(() => {
      main.$myModal.modal('hide');
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      } else {
        alert(json.msg);
      }
    });
  },
  getDataById() {
    main.$myModal.modal('show');
    getSignById({
      id: main.state.id,
      ukey: $.cookie('ukey'),
    }).then(result => {
      main.$merchantName.html(`<option value="${result.data.admin_id}">${
        result.data.describe}</option>`);
      main.$columnApi.val(result.data.content);
      main.$columnApiName.val(result.data.interName);
    }, error => {
      if (error.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      } else {
        alert(error.msg);
      }
    });
  },
  addFun() {
    main.$myModal1.modal('show');
    main.$columnApi1.val('');
    main.$columnApiName1.val('zhihuitumsg');
    main.getUnMsgSign(main.$merchantName1);
  },
  deleteShowFun() {
    main.$gridSystemModal.modal('show');
  },
  getData(pageNum) {
    const tpllist = hogan.compile(tplHtml);
    getMsgSign({ // 获取列表数据接口
      ukey: $.cookie('ukey'),
      page: pageNum || 1,
      sign: main.$search.val(),
    }).then(result => {
      this.$tbody.html(tpllist.render({ list: result.data.data }));
      if (result.data.data.length > 0 && result.data.page.total_page > 1) {
        this.pageBox.css('display', 'block');
        this.$currentppage.html(`当前第${result.data.page.current_page}页`);
        this.$total.html(`共${result.data.page.total_page}页`);
        this.totalPage = result.data.page.total_page;
        this.$gopage.val('');
      } else {
        this.pageBox.css('display', 'none');
      }
      this.$gopage.val('');
    }, error => {
      console.log(error);
      if (error.code === 102) {
        this.$tbody.html(tpllist.render({ list: [] }));
        this.pageBox.css('display', 'none');
      } else if (error.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  getUnMsgSign(parentEl) {
    getUnMsgSign({ // 获取选择商户接口
      ukey: $.cookie('ukey'),
    }).then(result => {
      let html = '';
      result.data.forEach((el) => {
        html += `<option value="${el.id}">${el.describe}</option>`;
      });
      parentEl.html(html);
    }, error => {
      if (error.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      } else {
        alert(error.msg);
      }
    });
  },
  deleteFun() {
    delMsgSign({
      id: main.state.id,
      ukey: $.cookie('ukey'),
    }).then(() => {
      $(`.delete${main.state.id}`).parents('tr').remove();
      main.$gridSystemModal.modal('hide');
    }, error => {
      if (error.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      } else {
        alert(error.msg);
      }
    });
  },
};
main.init();
