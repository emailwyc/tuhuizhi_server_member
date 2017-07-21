require('../../scss/evaluate/edituser.scss');
import { getUploadToken } from '../model';
import { getClassAll, getStaffOne, editStaffOne } from '../model/evaluate';
import { out } from '../modules/out.js';
const $ = window.$;
const conf = window.conf;
const Qiniu = window.Qiniu;
require('../modules/cookie')($);
const main = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(d => {
      this.uploader(d.data);
    });
    this.file = '';
    this.getClassAll();
  },
  initDom() {
    this.$out = $('.out');
    this.$image = $('#image');
    this.$staffName = $('#staff-name');
    this.$staffId = $('#staff-id');
    this.$phoneNumber = $('#phone-number');
    this.$subBtn = $('.subBtn .btn'); // 提交
    this.$checkBox = $('.checkbox-select');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$subBtn.on('click', () => {
      if (!this.$staffName.val()) {
        alert('请输入员工姓名');
        return false;
      }
      if (!this.$staffId.val()) {
        alert('请输入员工工号');
        return false;
      }
      if (!this.$phoneNumber.val()) {
        alert('请输入联系电话');
        return false;
      }
      const checkBoxs = this.$checkBox.find('input');
      let num = 0;
      checkBoxs.each((i, item) => {
        if (!$(item).prop('checked')) {
          num ++;
        }
      });
      if (num === checkBoxs.length) {
        alert('请选择分类');
        return false;
      }
      if (!this.$image.attr('src')) {
        alert('请选择上传图片');
        return false;
      }
      return this.editStaffOne();
    });
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
      uptoken: token, // 若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
      unique_names: true, // 默认 false，key为文件名。若开启该选项，SDK为自动生成上传成功后的key（文件名）。
      // save_key: true,   // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK会忽略对key的处理
      domain: 'http://qiniu-plupload.qiniudn.com/',   // bucket 域名，下载资源时用到，**必需**
      get_new_uptoken: false,  // 设置上传文件的时候是否每次都重新获取新的token
      // container: 'container',           // 上传区域DOM ID，默认是browser_button的父元素
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
  editStaffOne() {
    const tagList = [];
    $.each(this.$checkBox.find('input'), (i, item) => {
      if ($(item).prop('checked')) {
        tagList.push($(item).attr('data-id'));
      }
    });
    editStaffOne({
      key_admin: $.cookie('ukey'),
      staff_id: conf.id ? conf.id : 0,
      name: this.$staffName.val(),
      number: this.$staffId.val(),
      mobile: this.$phoneNumber.val(),
      class: tagList,
      qrcode: '二维码',
      avatar: this.file || this.$image.attr('src'),
    }).then(() => {
      location.href = '/evaluate/staff';
    }, error => {
      alert(error.msg);
    });
  },
  getStaffOne() {
    getStaffOne({
      key_admin: $.cookie('ukey'),
      staff_id: conf.id,
    }).then(result => {
      this.$staffName.val(result.data.name);
      this.$staffId.val(result.data.number);
      this.$phoneNumber.val(result.data.mobile);
      this.$image.attr('src', result.data.avatar);
      this.$checkBox.find('input').each((i, item) => {
        const dataId = $(item).attr('data-id');
        if (result.data.class[dataId]) {
          $(`#inlineCheckbox${dataId}`).prop('checked', true);
        }
      });
    }, error => {
      alert(error.msg);
    });
  },
  getClassAll() {
    getClassAll({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      let html = '';
      $.each(result.data, (i, item) => {
        html += `<label class="checkbox-inline">
          <input type="checkbox" id="inlineCheckbox${item.id}" data-id="${item.id}"
           value="option1"> ${item.name} </label>`;
      });
      this.$checkBox.html(html);
      if (conf.id) this.getStaffOne();
    }, error => {
      if (error.code === 102) {
        if (conf.id) this.getStaffOne();
      } else {
        alert(error.msg);
      }
    });
  },
};
main.init();
