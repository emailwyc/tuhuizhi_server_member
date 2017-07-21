require('../../scss/survey/addsurvey.scss');

import { submit, getQuesGroup } from '../model/survey';
import { getUploadToken } from '../model/';   // 七牛token
const $ = window.$;
const Qiniu = window.Qiniu;
const conf = window.conf;
require('../modules/cookie')($);
let num = 1;
let titswitch = 0;
const record = {
  init() {
    this.initDom();
    this.file = '';
    getUploadToken().then(d => {
      // console.log(d);
      this.uploader(d.data);
    });
    this.initEvent();
    if (conf.paperId) {
      this.getQADetail();
    }
    this.getQuesGroup();
  },
  initDom() {
    this.$addtitle = $('#addtitle');
    this.$deltitle = $('#deltitle');
    this.$addselect = $('#addselect');
    this.$submit = $('#submit');
    this.$cancel = $('#cancel');
    this.$selection = $('.selection');
    this.$contentbox = $('#contentbox');
    this.$select = $('#select');
    this.$image = $('#image');
    this.groupBox = $('.group-box');
  },
  initEvent() {
    this.$contentbox.on('click', (event) => {
      const e = event || window.event;
      const target = e.target || e.srcElement;
      if (target.id === 'deltitle') {
        target.parentNode.parentNode.remove();
      } else if (target.id === 'addtitle') {
        num ++;
        const addhtml = `<div class="setContent other" id="setbox${num}">
                         <div class="titleName">
                           <label for="">题目</label>
                           <input type="text" name="" value="">
                           <img src="https://img.rtmap.com/del.png"
                           title="删除题目" alt="删除题目" id="deltitle">

                           <img src="https://img.rtmap.com/addtimu.png" title="添加题目" alt="添加题目"
                           id="addtitle">
                           </div>
                           <div class="type">
                            <label for="">类型</label>
                            <select class="select" name="" id="select${num}">
                              <option value="0"><a href="#">单选</a></option>
                              <option value="1"><a href="#">多选</a></option>
                              <option value="2"><a href="#">问答</a></option>
                            </select>
                          </div>
                          <ul class="selection" id="selectbox${num}">
                            <li>
                              <input type="text" name="" value="" placeholder="选项">
                              <span class="delselection">
                                <img src="https://img.rtmap.com/webwxgetmsgimg.png"
                                 title="删除选项" alt="删除">
                              </span>
                            </li>
                            <li>
                              <input type="text" name="" value="" placeholder="选项">
                              <span class="delselection">
                                <img src="https://img.rtmap.com/webwxgetmsgimg.png"
                                 title="删除选项" alt="删除">
                              </span>
                            </li>
                            <li>
                              <input type="text" name="" value="" placeholder="选项">
                              <span class="delselection">
                                <img src="https://img.rtmap.com/webwxgetmsgimg.png"
                                 title="删除选项" alt="删除">
                              </span>
                            </li>
                          </ul>
                          <div class="text" >
                            <textarea name="name" rows="8" cols="80"
                            placeholder="问答" disabled="disabled" id="text${num}"></textarea>
                          </div>
                          <div class="addselection" id="${num}">新建选项</div>
                        </div>`;
        this.$contentbox.append(addhtml);
      } else if (target.className === 'addselection') {
        const numid = target.id;
        const html = `<li>
                        <input type="text" name="" value="" placeholder="选项">
                        <span class="delselection">
                        <img src="https://img.rtmap.com/webwxgetmsgimg.png" title="删除选项" alt="删除">
                        </span>
                      </li>`;
        $(`#selectbox${numid}`).append(html);
      } else if (target.tagName === 'IMG') {
        target.parentNode.parentNode.remove();
      } else if (target.className === 'select') {
        const selectlen = $('.select').length;
        for (let i = 0; i < selectlen; i++) {
          $('.select').eq(i).on('change', () => {
            if ($('.select').eq(i).val() === '2') {
              $('.select').eq(i).parent().parent().find('ul').css({ display: 'none' });
              $('.select').eq(i).parent().parent().css({ paddingBottom: '200px' });
              $('.select').eq(i).parent().parent().find('.addselection').css({ display: 'none' });
              $('.select').eq(i).parent().parent().find('textarea').css({ display: 'block' });
            } else {
              $('.select').eq(i).parent().parent().find('ul').css({ display: 'block' });
              $('.select').eq(i).parent().parent().css({ paddingBottom: '20px' });
              $('.select').eq(i).parent().parent().find('.addselection').css({ display: 'block' });
              $('.select').eq(i).parent().parent().find('textarea').css({ display: 'none' });
            }
          });
        }
      }
    });
    this.$submit.on('click', () => {
      const url = `${this.file}`;
      console.log(this.file);
      let jumplink = '';
      const topic = $('.setContent');
      const topiclen = topic.length;
      const contents = [];
      const paperTitles = $('#paperTitle').val(); // 大标题
      const startTimes = $('#startTime').val();   // 开始时间
      const endTimes = $('#endTime').val();       // 结束时间
      for (let i = 0; i < topiclen; i++) {
        const obj = {};
        const topictitle = topic.eq(i).find('.titleName').find('input').val(); // 题目
        if (topictitle === '') {
          titswitch = 1;
        } else {
          titswitch = 0;
        }
        if (topic.eq(i).find('.type').find('select').val() === '2') {
          const topictype = topic.eq(i).find('.type').find('select').val();    // 类型
          obj.questionType = topictype;
        } else {
          const userselect = topic.eq(i).find('ul').find('input');
          const selections = [];
          const selectlen = userselect.length;
          const topictype = topic.eq(i).find('.type').find('select').val();
          for (let j = 0; j < selectlen; j++) {
            selections.push(userselect.eq(j).val());
          }
          obj.contents = selections;
          obj.questionType = topictype;
        }
        obj.questionTitle = topictitle;
        contents.push(obj);
      }
      if ($('.awardbox input[type="radio"]:checked').val() !== 4 &&
          $('.awardbox input[type="radio"]:checked').parents('p').find('input[name="inputValue"]').val() !== '') {
        jumplink = $('.awardbox input[type="radio"]:checked').parents('p').find('input[name="inputValue"]').val();
      } else if ($('.awardbox input[type="radio"]:checked').val() === 4) {
        jumplink = '';
      } else {
        alert('参与"问卷有礼"则必须输入有效值');
        return;
      }
      console.log($('.awardbox input[type="radio"]:checked').val());
      console.log(jumplink);
      if (paperTitles === '' || startTimes === '' || endTimes === '') {
        alert('标题、开始时间、结束时间都不能为空');
      } else if (titswitch === 1) {
        alert('有题目为空');
        // return false;
      } else if (url === '') {
        alert('为你的标题添加图片');
      } else if (titswitch === 0) {
        const keyadmin = $.cookie('ukey');
        // alert(keyadmin);
        const datas = {
          paperTitle: paperTitles,
          startTime: startTimes,
          endTime: endTimes,
          values: contents,
          key_admin: keyadmin,
          paperIcon: url,
          jumpLink: jumplink === undefined ? '' : jumplink,
          jumpType: $('.awardbox input[type="radio"]:checked').val(),
          group_id: this.groupBox.find('option:selected').val(),
        };
        console.log(datas);
        submit(datas).then((json) => {
          console.log(json);
          if (json.data === 0) {
            alert('提交成功');
            window.location.href = '/survey/survey';
          } else if (json.data === 2) {
            alert('有选项少于两项，提交失败');
          } else if (json.data === 1) {
            alert('提交失败');
          } else if (json.data === 4001) {
            alert('用户不存在或者登录超时，请重新登录');
            location.href = '/user/login';
          } else if (json.data === 4002) {
            location.href = '/user/admin';
            alert('你未被授权');
          }
        }, json => {
          console.log(json);
        });
      }
    });
    this.$cancel.hover(() => {
      this.$submit.css({ backgroundColor: 'white' });
      this.$cancel.css({ backgroundColor: 'gray' });
    }, () => {
      this.$submit.css({ backgroundColor: 'gray' });
      this.$cancel.css({ backgroundColor: 'white' });
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
  getQuesGroup() {
    getQuesGroup({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      let html = '';
      $.each(result.data, (i, item) => {
        html += `<option value="${item.id}">${item.group_name}</option>`;
      });
      this.groupBox.html(html);
    }, error => {
      alert(error.msg);
    });
  },
};
record.init();
