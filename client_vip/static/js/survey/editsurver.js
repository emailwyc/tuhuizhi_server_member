require('../../scss/survey/editsurver.scss');

import { getQADetail, editQuestionnaire, getQuesGroup } from '../model/survey';
import { getUploadToken } from '../model/';   // 七牛token
const $ = window.$;
const conf = window.conf;
const Qiniu = window.Qiniu;
require('../modules/cookie')($);
let num = 1;
// let titswitch = 0;
const editsurver = {
  init() {
    this.initDom();
    this.file = '';
    getUploadToken().then(d => {
      // console.log(d);
      this.uploader(d.data);
    });
    this.initEvent();
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
                           title="删除题目" alt="删除题目" id="deltitle" style="display:none">

                           <img src="https://img.rtmap.com/addtimu.png" title="添加题目" alt="添加题目"
                           id="addtitle" style="display:none">
                           </div>
                           <div class="type">
                            <label for="">类型</label>
                            <select class="select" name="" id="select${num}" disabled>
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
                                 title="删除选项" alt="删除" style="display:none">
                              </span>
                            </li>
                            <li>
                              <input type="text" name="" value="" placeholder="选项">
                              <span class="delselection">
                                <img src="https://img.rtmap.com/webwxgetmsgimg.png"
                                 title="删除选项" alt="删除" style="display:none">
                              </span>
                            </li>
                            <li>
                              <input type="text" name="" value="" placeholder="选项">
                              <span class="delselection">
                                <img src="https://img.rtmap.com/webwxgetmsgimg.png"
                                 title="删除选项" alt="删除" style="display:none">
                              </span>
                            </li>
                          </ul>
                          <div class="text" >
                            <textarea name="name" rows="8" cols="80"
                            placeholder="问答" disabled="disabled" id="text${num}"></textarea>
                          </div>
                          <div class="addselection" id="${num}" style="display:none">新建选项</div>
                        </div>`;
        this.$contentbox.append(addhtml);
      } else if (target.className === 'addselection') {
        const numid = target.id;
        const html = `<li>
                        <input type="text" name="" value="" placeholder="选项">
                        <span class="delselection">
                        <img src="https://img.rtmap.com/webwxgetmsgimg.png" title="删除选项" alt="删除" style="display:none">
                        </span>
                      </li>`;
        $(`#selectbox${numid}`).append(html);
      } else if (target.tagName === 'IMG') {
        target.parentNode.parentNode.remove();
      } else if (target.className === 'select') {
        if ($(target).parent().parent().find('select').val() === '2') {
          $(target).parent().parent().find('ul').css({ display: 'none' });
          $(target).parent().parent().css({ paddingBottom: '200px' });
          $(target).parent().parent().find('.addselection').css({ display: 'none' });
          $(target).parent().parent().find('textarea').css({ display: 'block' });
        } else {
          $(target).parent().parent().find('ul').css({ display: 'block' });
          $(target).parent().parent().css({ paddingBottom: '20px' });
          $(target).parent().parent().find('.addselection').css({ display: 'block' });
          $(target).parent().parent().find('textarea').css({ display: 'none' });
        }
      }
    });
    this.$cancel.hover(() => {
      this.$submit.css({ backgroundColor: 'white' });
      this.$cancel.css({ backgroundColor: 'gray' });
    }, () => {
      this.$submit.css({ backgroundColor: 'gray' });
      this.$cancel.css({ backgroundColor: 'white' });
    });
    $('#submit').on('click', () => {
      this.editQuestionnaire();
    });
  },
  getQADetail() {
    getQADetail({
      key_admin: $.cookie('ukey'),
      paperid: conf.paperId,
    }).then(json => {
      console.log(json);
      $('#paperTitle').val(json.data.paperTitle);
      $('#startTime').val(json.data.startTime);
      $('#endTime').val(json.data.endTime);
      $('#image').attr('src', json.data.paperIcon);
      this.groupBox.find(`.groupitem${json.data.groupId}`).prop('selected', true);
      if (json.data.jumpType !== '') {
        $(`.awardbox input[value="${json.data.jumpType}"]`).prop('checked', true);
        $('.awardbox input[type="radio"]:checked').parents('p').find('input[name="inputValue"]').val(`${json.data.jumpLink.replace(/amp;/g, '')}`);
      } else {
        $('.awardbox input[value="4"]').prop('checked', true);
      }
      let divdata = '';
      $.each(json.data.questionlist, (i, v) => {
        let li = '';

        if (v.questionType === '2') {
          li += `<div class="text" >
            <textarea name="name" rows="8" cols="80"
            placeholder="问答" disabled="disabled" id="text${num}"></textarea>
          </div>
          <li>
            <input type="text" name="" value="" placeholder="选项">
            <span class="delselection">
            <img src="https://img.rtmap.com/webwxgetmsgimg.png" title="删除选项" alt="删除"></span>
          </li>
          <li>
            <input type="text" name="" value="" placeholder="选项">
            <span class="delselection">
            <img src="https://img.rtmap.com/webwxgetmsgimg.png" title="删除选项" alt="删除"></span>
          </li>
          <li>
            <input type="text" name="" value="" placeholder="选项">
            <span class="delselection">
            <img src="https://img.rtmap.com/webwxgetmsgimg.png" title="删除选项" alt="删除"></span>
          </li>`;
        } else {
          $.each(v.optionList, (index, option) => {
            li += `
            <li>
              <input type="text" name="" data-ticket="${option.ticket}"
              data-optionId="${option.optionId}"
              value="${option.contents}" data-ticket="${option.ticket}" placeholder="选项">
              <span class="delselection">
                <img src="https://img.rtmap.com/webwxgetmsgimg.png"
                 title="删除选项" alt="删除" style="display:none">
              </span>
            </li>`;
          });
        }
        divdata += `<div class="setContent other" id="setbox${i + 1}">
                      <div class="titleName">
                        <label for="">题目</label>
          <input type="text" name="" value="${v.questionTitle}" data-questionId="${v.questionId}" >
                        <img src="https://img.rtmap.com/del.png"
                        title="删除题目" alt="删除题目" id="deltitle" style="display:none">
                        <img src="https://img.rtmap.com/addtimu.png" title="添加题目" alt="添加题目"
                        id="addtitle" style="display:none">
                      </div>
                      <div class="type">
                        <label for="">类型</label>
                        <select class="select" name="" id="select${num}" disabled>
                          <option value="0" ${v.questionType === '0' ?
                           'selected' : ''}><a href="#" >单选</a></option>
                          <option value="1" ${v.questionType === '1' ?
                           'selected' : ''}><a href="#">多选</a></option>
                          <option value="2" ${v.questionType === '2' ?
                           'selected' : ''}><a href="#">问答</a></option>
                        </select>
                      </div>
            <ul class="selection seloption" id="selectbox${i + 1}" style="display:
            ${v.questionType === '2' ? 'none' : 'block'}">${li}</ul>
            <div class="addselection" id="${i + 1}" style="display:none">新建选项</div>
                    </div>`;
      });
      $('.contentbox').html(divdata);
    }, json => {
      console.log(json);
    });
  },


  editQuestionnaire() {
    const dataConf = [];
    let jumplink = '';
    this.$setContent = $('.setContent');
    for (let i = 0; i < this.$setContent.length; i++) {
      const topictitle = this.$setContent.eq(i).find('.titleName').find('input').val();
      const questionid = this.$setContent.eq(i).find('.titleName').find('input')
      .attr('data-questionId');
      const Inputtype = this.$setContent.eq(i).find('.type').find('select').val();
      const ulInput = this.$setContent.eq(i).find('ul').find('input');
      const Inputtext = [];
      for (let j = 0; j < ulInput.length; j++) {
        Inputtext.push({ contents: ulInput.eq(j).val(), ticket: ulInput.eq(j).
          attr('data-ticket'), optionId: ulInput.eq(j).attr('data-optionId') });
      }
      dataConf.push({
        questionTitle: topictitle,
        questionType: Inputtype,
        option: Inputtext,
        questionId: questionid,
      });
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
    editQuestionnaire({
      key_admin: $.cookie('ukey'),
      paperId: conf.paperId,
      paperTitle: $('#paperTitle').val(),
      startTime: $('#startTime').val(),
      endTime: $('#endTime').val(),
      paperIcon: $('#image').attr('src'),
      questionlist: dataConf,
      jumpLink: jumplink === undefined ? '' : jumplink,
      jumpType: $('.awardbox input[type="radio"]:checked').val(),
      group_id: this.groupBox.find('option:selected').val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.href = '/survey/survey';
    }, json => {
      console.log(json);
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
        html += `<option class="groupitem${item.id}" value="${item.id}">${
          item.group_name}</option>`;
      });
      this.groupBox.html(html);
      this.getQADetail();
    }, error => {
      alert(error.msg);
    });
  },
};
editsurver.init();
