require('../../scss/mall/banner.scss');
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
import { out } from '../modules/out.js';
import { bannerList, bannerDel, bannerStatus, buildidList } from '../model/mall.js';

const banner = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
    this.store = {};
    this._buildList();
    this._bannerList();
  },
  initDom() {
    this.$myModal = $('#myModal');
    this.$save = $('.save');
    this.$out = $('.out');
    this.$tbody = $('.list .table tbody');
    this.$addBtn = $('.add-btn');
    this.$build = $('.build');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$tbody.on('click', '.banner-del', (e) => {
      const bannerId = $(e.target).attr('data-id');
      this.$bannerId = bannerId;
    });

    this.$tbody.on('click', '.banner-status', (e) => {
      const bannerSta = $(e.target).attr('data-id');
      this._bannerStatus(bannerSta).then((json) => {
        console.log(json);
        const temp = $(e.target).text();
        if (temp === '上线') {
          $(e.target).text('下线');
        } else {
          $(e.target).text('上线');
        }
      }).catch(err => alert(err.msg));
    });

    // this.$addBtn.on('click', (e) => {
    //   let num = this.$tbody.find('tr').length;
    //   if (num >= 5) {
    //     alert("最多5个轮播图！")
    //     e.preventDefault();
    //   }
    // });

    this.$save.on('click', () => {
      this._bannerDel(this.$bannerId);
      this.$myModal.modal('hide');
    });

    this.$build.on('change', (e) => {
      const buildId = $(e.target).find('option:selected').attr('data-build');
      if (buildId === 'all') {
        this._bannerList();
        return;
      }
      try {
        const data = this.store.data.filter(v => v.buildid === buildId);
        this._render(data);
      } catch (err) {
        console.log(err);
      }
    });
  },
  _render(data) {
    this.$tbody.empty();
    if (data === null || data.length === 0) {
      this.$tbody.append('<td colspan="6">暂无数据</td>');
      return;
    }
    data.sort((a, b) => a.sort - b.sort);
    data.forEach((v) => {
      let status = '下线';
      if (v.status === 2 || v.status === '2') {
        status = '上线';
      }
      const tempT = `<tr>
        <td scope="row">${v.sort}</td>
        <td>${v.banner_name}</td>
        <td>${v.name}</td>
        <td><img src="${v.url}" alt=""></td>
        <td>${v.jump_url}</td>
        <td>
          <a href="javascript:;" class="banner-status" data-id="${v.id}">${status}</a>
          <a href="addBanner?id=${v.id}">编辑</a>
          <a href="javascript:;" class="banner-del" data-id="${v.id}" data-toggle="modal"
           data-target="#myModal">删除</a>
        </td>
      </tr>`;
      this.$tbody.append(tempT);
    });
  },
  _bannerList() {
    bannerList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
      const data = json.data;
      this.store.data = data;
      this._render(data);
    }).catch(err => console.log(err));
  },
  _bannerDel(bannerId) {
    bannerDel({
      key_admin: $.cookie('ukey'),
      banner_id: bannerId,
    }).then((json) => {
      alert(json.msg);
      location.reload();
    }).catch(err => alert(err.msg));
  },
  _bannerStatus(bannerId) {
    return new Promise((resolve, reject) => {
      bannerStatus({
        key_admin: $.cookie('ukey'),
        banner_id: bannerId,
      }).then((json) => {
        resolve(json);
      }).catch(err => reject(err));
    });
  },
  _buildList() {
    buildidList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      const data = json.data;
      if (data === null) {
        return;
      } else if (data.length === 1) {
        const tmp = `<option data-build=${data[0].buildid}>${data[0].name}</option>`;
        this.$build.append(tmp);
        return;
      }
      const tmpAll = '<option data-build="all">全部</option>';
      this.$build.append(tmpAll);
      data.forEach((v) => {
        const tmp = `<option data-build="${v.buildid}">${v.name}</option>`;
        this.$build.append(tmp);
      });
    }).catch((err) => {
      alert(err.msg);
    });
  },
};

banner.init();
