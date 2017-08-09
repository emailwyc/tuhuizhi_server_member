// 接口配置2
require('../../scss/dashboard/interfaceconfig2.scss');
import { out } from '../modules/out.js';
import { apiType, apiConFig, apiConFigOne, getApiList } from './model';
const hogan = require('hogan.js');
const apiConFigInfo = require('./tpls/apiconfiginfo.html');
const _ = window._;
const $ = window.$;
const conf = window.conf;
const interfaceconfig2 = {
  init() {
    this.initDom();
    this.initEvent();
    this.apiType();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$pager = $('.pager');
    this.$dropdown = $('#dropdown');
    this.$databox = $('.data_box');
    this.$apiType = $('.api_type');
    this.requestParamType = $('.request_param_type');
    this.$requestType = $('.request_type');
    this.$reponseDataType = $('.reponse_data_type');
    this.$apiUrl = $('.apiurl');
    this.$request = $('.request');
    this.$response = $('.response');
    this.$requestKeys = $('.request_keys');
    this.$responseKeys = $('.response_keys');
    this.$submitBtn = $('.btn');
    this.$inputRequestkey = $('.inputRequestkey');
    this.$inputResponsekey = $('.inputResponsekey');
    this.$keysbox = $('.keys_box');
    this.$out = $('.out');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$pager.on('click', 'li', (e) => {
      const $target = $(e.target);
      this.state.ctg = {
        id: $target.data('id') || 0,
      };
      this.$databox.show();
      this.apiList();
      this.conFigOne();
    });
    this.$dropdown.on('click', () => {
      this.$databox.show();
      const tpllist = hogan.compile(apiConFigInfo);
      this.$keysbox.html(tpllist.render({ info: 'info' }));
      this.apiList();
    });
    this.$submitBtn.on('click', () => {
      this.$inputRequestkey = $('.inputRequestkey');
      this.$inputResponsekey = $('.inputResponsekey');
      this.apiConFig();
    });
  },
  apiType() {
    apiType({
      ukey: $.cookie('ukey'),
      id: conf.id,
    }).then(json => {
      console.log(json);
      let liA = '';
      $.each(json.data, (i, v) => {
        liA += `<li><a href="#" data-id="${v.id}">${v.name}</a></li>`;
        this.$pager.html(liA);
      });
    }, json => {
      console.log(json);
      if (json.code === 502 || json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  conFigOne() {
    apiConFigOne({
      ukey: $.cookie('ukey'),
      admin_id: conf.id,
      id: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      $('.api_type').val(json.data.api_type);
      console.log(json.data.api_type);
      this.requestParamType.val(json.data.request_param_type);
      this.$requestType.val(json.data.request_type);
      this.$reponseDataType.val(json.data.response_data_type);
      this.$apiUrl.val(json.data.api_url);
      $(`.is_sign input[value="${json.data.is_sign}"]`).attr('checked', 1);
      const response = json.data.api_response;
      const request = json.data.api_request;
      let responseStr = '';
      _.keys(response).forEach((v) => {
        responseStr += `<div class="con_box">
          <span class="response_keys">${response[v]}</span>
          <div class="con_url">
          <input type="text" class="form-control name-val inputResponsekey" placeholder="接口地址"
          name="${response[v]}" value="${response[v]}">
          </div>
        </div>`;
        this.$response.html(responseStr);
      });
      let requestStr = '';
      _.keys(request).forEach((v) => {
        requestStr += `<div class="con_box">
          <span class="request_keys">${request[v]}</span>
          <div class="con_url">
          <input type="text" class="form-control name-val inputRequestkey" placeholder="接口地址"
          name="${request[v]}" value="${request[v]}">
          </div>
        </div>`;
        this.$request.html(requestStr);
      });
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  apiList() {
    getApiList({
      ukey: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let option = '';
      $.each(json, (i, v) => {
        option += `<option value="${v.id}">${v.name}</option>`;
        this.$apiType.html(option);
      });
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  apiConFig() {
    apiConFig({
      ukey: $.cookie('ukey'),
      total_id: conf.id,
      id: this.state.ctg.id,
      api_type: this.$apiType.val(),
      request_param_type: this.requestParamType.val(),
      request_type: this.$requestType.val(),
      response_data_type: this.$reponseDataType.val(),
      api_url: this.$apiUrl.val(),
      is_sign: $('input[name="inlineRadioOptions"]:checked').val(),
      request_keys: this.$inputRequestkey.val(),
      response_keys: this.$inputResponsekey.val(),
    }).then(json => {
      console.log(json);
      alert('添加成功');
      location.reload();
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
interfaceconfig2.init();
