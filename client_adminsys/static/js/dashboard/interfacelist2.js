// 接口列表
require('../../scss/dashboard/interfacelist2.scss');
import { out } from '../modules/out.js';
import { getResponseKey, setResponseKey } from './model';
const hogan = require('hogan.js');
const validate = require('../modules/validate');
const tpladdinterface = require('./tpls/addinterface.html');
const $ = window.$;
const conf = window.conf;
const interfacelist = {
  init() {
    this.initDom();
    this.initEvent();
    this.getResponseKey();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$form = $('#form');
    validate.init(this.$form);
    this.$btnadd = $('.btn-secondary');
    this.$nums = $('#nums');
    this.$interlist = $('.interlist');
    this.$subbtn = $('.subbtn');
    this.$out = $('.out');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$form.on('submit', e => {
      e.preventDefault();
      const data = this.$form.serializeArray();
      console.log(data);
      const data2 = $.map(data, (n) => {
        console.log(n);
        return n.value;
      });
      const postData = { keys: data2 };
      console.log(postData);
      this.setResponseKey(postData);
      /*
      setTimeout(() => {
        // location.href = `http://www.baidu.com?${this.$form.serialize()}`;
        console.log(this.$form.serialize());
      }, 1000);
      */
    });
    this.$btnadd.on('click', () => {
      const tpl = hogan.compile(tpladdinterface);
      const num = parseInt(this.$nums.val(), 10);
      const rendered = tpl.render({ id: num + 1 });
      this.$nums.val(num + 1);
      this.$interlist.append(rendered);
      this.$subbtn.show();
    });
  },
  setResponseKey(postData) {
    setResponseKey({
      ukey: '202cb962ac59075b964b07152d234b70',
      apiid: conf.id,
      keys: postData,
    }).then(json => {
      console.log(json);
      location.href = '/dashboard/memberconfig';
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  getResponseKey() {
    getResponseKey({
      ukey: '202cb962ac59075b964b07152d234b70',
      apiid: conf.id,
    }).then(json => {
      console.log(json);
      if (!!json.keys) {
        let str = '';
        $.each(json.keys, (i) => {
          console.log(json.keys[i]);
          str += `<li>
            <div class="form-group row">
              <label for="inputEmail3" class="col-sm-2 form-control-label">参数</label>
              <div class="col-sm-10">
                <input type="text" name="keys" class="form-control" data-id="{{id}}"
                 placeholder="请输入参数" required data-required="请输入内容" value="${json.keys[i]}">
              </div>
            </div>
          </li>`;
          this.$interlist.html(str);
          this.$subbtn.show();
        });
      } else {
        this.$subbtn.hide();
      }
    }, json => {
      console.log(json);
      this.$subbtn.hide();
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
interfacelist.init();
