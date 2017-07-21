/* 添加广告 */
require('../../scss/wxCoupon/wxCouponAd.scss');
const $ = window.$;
import { addBanner, bannerList, delbanner } from '../model/wxCoupon';
import { getUploadToken } from '../model/';   // 七牛token
import { buildList } from '../model/buildManage';
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
const storage = window.sessionStorage;
const Qiniu = window.Qiniu;
const wxCouponAd = {
  init() {
    this.initDom();
    this.file = '';
    getUploadToken().then(d => {
      // console.log(d);
      this.uploader(d.data);
    });
    this.buildList();
    this.initPage();
    this.initEvent();
  },
  initDom() {
    this.$cancel = $('.cancel'); // 弹框取消
    this.$addWarp = $('.addWarp'); // 添加-弹框
    this.$cofigAdd = $('.cofigAdd'); // 添加按钮
    this.$addSure = $('.addSure'); // 确认按钮
    this.$order = $('.order'); // 排序
    this.$jumlurl = $('.jumlurl'); // 跳转路径
    this.$image = $('#image');
    this.$buildSelect = $('.buildSelect');
  },
  initEvent() {
    this.$cancel.on('click', () => {
      this.$addWarp.css({ display: 'none' });
    });
    this.$cofigAdd.on('click', () => {
      this.$addWarp.css({ display: 'block' });
      console.log(11);
    });
    this.$addSure.on('click', () => {
      if (this.$buildSelect.val() === '0') {
        alert('请选择建筑物');
        return;
      } else if (this.$image.attr('src').indexOf('http') >= 0) {
        this.addBanner();
      } else {
        alert('请您添加广告图');
      }
    });
    $('tbody').on('click', '.delBanner', (event) => {
      const e = event || window.event;
      const target = e.target || e.srcElement;
      console.log(target.id);
      this.delBanner(target.id);
    });
  },
  initPage() {
    bannerList({
      key_admin: keyadmin,
      childid: storage.getItem('childid') || '',
    }).then(json => {
      console.log(json);
      this.rederDom(json.data);
    }, json => {
      console.log(json);
    });
  },
  addBanner() {
    addBanner({
      key_admin: keyadmin,
      imgurl: this.$image.attr('src'),
      sort: this.$order.val(),
      jumpurl: this.$jumlurl.val(),
      buildid: this.$buildSelect.val(),
    }).then(json => {
      console.log(json);
      window.location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
    });
  },
  delBanner(id) {
    if (confirm('确定执行此操作？')) {
      delbanner({
        key_admin: keyadmin,
        id,
      }).then(json => {
        console.log(json);
        window.location.reload();
      }, json => {
        console.log(json);
        alert(json.msg);
      });
    }
  },
  buildList() {
    buildList({
      key_admin: keyadmin,
    }).then(json => {
      console.log(json);
      let options = '';
      $.map(json.data, n => {
        options += `<option value="${n.buildid}">${n.name}</option>`;
      });
      this.$buildSelect.append(options);
    }, json => {
      console.log(json);
    });
  },
  rederDom(data) {
    console.log(data);
    let html = '';
    if (data.length < 0) {
      $('tbody').html('无数据');
      return;
    }
    $.map(data, (n, i) => {
      html += `<tr>
          <!-- <td>${i + 1}</td> -->
          <td>${n.sort}</td>
          <td>${n.buildid || ''}</td>
          <td><img src="${n.imgurl}" alt=""></td>
          <td>../../shopdetails/details?id=${n.jumpurl && n.jumpurl !== null ? n.jumpurl : ''}</td>
          <td class="options">
          <!-- <a href="javascript:;">编辑</a> -->
          <a href="javascript:;" id="${n.id}" class="delBanner">删除</a>
          </td>
        </tr>`;
    });
    $('tbody').html(html);
  },
  uploader(token) {
    // domain 为七牛空间（bucket)对应的域名，选择某个空间后，可通过"空间设置->基本设置->域名设置"查看获取
    // uploader 为一个plupload对象，继承了所有plupload的方法，参考http://plupload.com/docs
    this.qiniuupload(token);
  },
  qiniuupload(token) {
    Qiniu.uploader({
      runtimes: 'html5,flash,html4',    // 上传模式,依次退化
      browse_button: 'pickfiles',       // 上传选择的点选按钮，**必需**
      // uptoken_url: token,            // Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
      uptoken: token,                   // 若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
      unique_names: true,               // 默认 false，key为文件名。若开启该选项，SDK为自动生成上传成功后的key（文件名）。
      // save_key: true,                // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK
                                        // 会忽略对key的处理
      domain: 'http://qiniu-plupload.qiniudn.com/',   // bucket 域名，下载资源时用到，**必需**
      get_new_uptoken: false,           // 设置上传文件的时候是否每次都重新获取新的token
      // container: 'container',        // 上传区域DOM ID，默认是browser_button的父元素
      max_file_size: '100mb',           // 最大文件体积限制
      flash_swf_url: 'js/plupload/Moxie.swf',  // 引入flash,相对路径
      max_retries: 3,                   // 上传失败最大重试次数
      dragdrop: true,                   // 开启可拖曳上传
      drop_element: 'container',        // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
      chunk_size: '4mb',                // 分块上传时，每片的体积
      auto_start: true,                 // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
      init: {
        FilesAdded: (up, files) => {
          console.log(files);
            // plupload.each(files, function(file) {
            //     // 文件添加进队列后,处理相关的事情
            // });
        },
        BeforeUpload: (up, file) => {
          console.log(file);
               // 每个文件上传前,处理相关的事情
          // this.contactusCode(file);
        },
        UploadProgress: (up, file) => {
          // const timestamp =Date.parse(new Date());
          console.log(file);
               // 每个文件上传时,处理相关的事情
        },
        FileUploaded: (up, file, info) => {
          console.log(info);
          this.file = `https://img.rtmap.com/${file.target_name}`;
          this.$image.attr('src', this.file);
          console.log(`${this.file}`);
        },
        Error: (up, err, errTip) => {
          console.log(errTip);
               // 上传出错时,处理相关的事情
        },
        UploadComplete: (up, file, data) => {
               // 队列文件处理完毕后,处理相关的事情
          console.log(file);
          console.log(data);
        },
      },
    });
  },
};
wxCouponAd.init();
