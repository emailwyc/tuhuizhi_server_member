import { bindCard, sendMsg, getuserinfo, checkin, checkSigned, cardimg, geticonlist } from './model';
import { mustLogin, getUserInfo } from 'wxlogin';
import 'qrcode';
const storage = localStorage;
const userinfo = getUserInfo();
const openid = userinfo.openid;
const nickname = userinfo.nickname;
const headimgurl = userinfo.headimgurl;
mustLogin();
const loading = require('rtloading');
console.log(openid);
// loading.show();
// $('.imgqr').qrcode({ width: 150, height: 150, text: '1234777' });
let wait=60;

const index = {
  init() {
    // this.userInfo = getUserInfo();
    this.initDom();
    this.initEvent();
    this.state();
    // this.packno();
  },
  initDom() {
    this.$head = $('.head')[0];
    this.$mediaHeading = $('.media-heading');
    this.$btn = $('#btn');
    this.$tel = $('.tel');
    this.$longin = $('#longin');
    this.$smsvali = $('.smsvali');
    this.$score = $('.score');
    this.$cardtype = $('.cardtype');
    this.$imgqr = $('.imgqr');
    this.$card = $('.card');
    this.$cardbottom = $('.cardbottom');
    this.$checkin = $('.checkin');
    this.$cardtop = $('.cardtop');
    this.$receive = $('.receive');
    this.$btn2 = $('#btn-2');
    this.$btn1 = $('#btn-1');
    this.$jifenxingqing = $('.jifenxingqing');
    this.$memberbook = $('.memberbook');
    this.$iconlist = $('.iconlist');
  },
  initEvent() {
    $('body').on('click','.bindcard',()=>{
      $('#myModal').modal('show');
    });
    this.$receive.on('click', () => {
      $('#myModal').modal('show');
    })
    this.$btn.on('click', ()=>{
    // this.time(this.$btn[0]);
    const verification = this.reg(this.$tel.val());
    if (verification) {
      this.sendMsg();
      this.time(this.$btn[0]);
    } else {
      alert('请输入正确手机号');
    }
    });
    this.$longin.on('click',()=>{
      $('#myModal').modal('hide');
      this.bindCard();
    });
    this.$mediaHeading.click(
      () =>{
        this.setScore();
      }
    );
    this.$checkin.on('click', () => {
      // $('#myModal-1').modal('show');
      this.checkin();
    });
    this.$btn2.on('click', () =>{
      $('#myModal-1').modal('hide');
    });
    this.$jifenxingqing.on('click', () => {
      location.href =`score.html?key_admin=${this.key}&openid=${openid}`;
    });
    this.$btn1.on('click', () => {
      location.href =`score.html?key_admin=${this.key}&openid=${openid}`;
    });
    this.$memberbook.on('click', () => {
      location.href =`memberBook.html?key_admin=${this.key}&openid=${openid}`;
    });
  },
  state() {
    this.$head.src=headimgurl;
    this.$mediaHeading.html(nickname);
    console.log(this.getQueryString('key_admin'));
    this.key = this.getQueryString('key_admin');
    const width = this.$card.width()/2;
    this.$imgqr.qrcode({ width, height: width, text: '1234777' });
    // $("#bcode").JsBarcode("30003314");
    this.checkSigned();
    this.setScore();
    this.geticonlist();
    // location.href =`register.html`;
  },
  reg(tel) {
    const pattern = /0?(13|14|15|17|18)[0-9]{9}/ ;
    // console.log(pattern.test(str));
    const verification = pattern.test(tel);
    return verification ;
  },
  // 倒计时
  time(o) {
      if (wait == 0) {
          o.removeAttribute("disabled");
          o.value="获取验证码";
          wait = 60;
      } else {
          o.setAttribute("disabled", true);
          o.value= wait;
          wait--;
          setTimeout(function() {
              index.time(o);
          },
          1000)
      }
      },
      // 提取URL查询信息
  getQueryString(name) {
      const reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
      const r = window.location.search.substr(1).match(reg);
      if (r != null) {
          return unescape(r[2]);
      }
      return null;
  },
  // 验证码
    sendMsg() {
      // alert(this.key+'or'+this.$tel.val());
    loading.show();
    sendMsg({
      key_admin: this.key,
      mobile: this.$tel.val(),
    }).then(json => {
      loading.hide();
      console.log(json);
      // this.timer = countdown(this.$getvali);
    }, json => {
      console.log(json);
      loading.hide();
      if (json.code === 1001) {
        alert('系统错误请关闭页面重试');
      }
    });
  },
  bindCard() {
  loading.show();
  bindCard({
    key_admin: this.key,
    openid,
    mobile: this.$tel.val(),
    code: this.$smsvali.val(),
  }).then(() => {
    loading.hide();
    // alert('chenggong');
    // $('.member_box a').removeClass('showlogin');
    this.setScore();
  }, (json) => {
    // alert('失败');
    // console.log(json);
    // alert(json.code);
    // alert(json.msg);
    // alert(11);
    console.log(json);
    loading.hide();
    if (json.code === 2000) {
      const user = JSON.parse(storage.getItem(`user${this.key}`));
      user.mobile = this.$tel.val();
      storage.setItem(`user${this.key}`, JSON.stringify(user));
      this.userinfo = user;
      // location.href = `/user/reg?mobile=${this.$tel.val()}&key_admin=${conf.key}`;
      location.href =`register.html?mobile=${this.$tel.val()}&key_admin=${this.key}&openid=${openid}`;
      return;
    }
    if (json.code === 2001) {
      location.href = `register.html?mobile=${this.$tel.val()}&key_admin=${this.key}&openid=${openid}`;
      return;
    }
    if (json.code === 2002) {
      alert('');
      location.href = `register.html?mobile=${this.$tel.val()}&key_admin=${this.key}&openid=${openid}`;
      return;
    }
    if (json.code === 1031) {
      alert('验证码错误');
      return;
    }
  });
},
setScore() {
  loading.show();
  // alert(openid);
  getuserinfo({
    key_admin: this.key,
    openid,
  }).then(json => {
    // alert(json.code);
    loading.hide();
    $('.checkin').removeClass("bindcard");
    console.log(json);
    this.$score.text(json.data.score);
    this.$cardtype.html(json.data.cardtype);
    this.$cardtop.html(`<div class="card">
      <p class="cardnum">No：${json.data.cardno}</p>
      <img src="./img/card-1.png" class="cardimg" alt="vip">
      <!-- <div class="receive" data-toggle="modal" data-target="#myModal">领取会员卡</div> -->
      <div class="qrcon">
        <div class="imgqr"><div class="btnqc-1"></div></div>
      </div>
      <div class="btnqc"></div>
    </div>
    <div class="bccon">
      <img id="bcode"/>
    </div>`);
    const width = $('.card').width()/2.2;
    // console.log(width);
    $('.imgqr').qrcode({ width, height: width, text: `${json.data.cardno}` });
    $("#bcode").JsBarcode(`${json.data.cardno}`);
    $('.btnqc').on('click', () => {
      $('.cardimg').css('visibility','hidden');
      $('.btnqc').css('visibility','hidden');
      $('.imgqr').css('z-index',100);
      $('.imgqr').css('visibility','visible');
    });
    $('.btnqc-1').on('click', () => {
      $('.cardimg').css('visibility','visible');
      $('.btnqc').css('visibility','visible');
      $('.imgqr').css('visibility','hidden');
      $('.imgqr').css('z-index',-100);
    });
    this.cardimg();
    // this.$cardnum.html(`No：${json.data.cardno}`);
    // this.$scoreInfo.html(`当前积分<br>${json.data.score}`);
  }, (json) => {
    // alert(json.code);
    loading.hide();
    $('.checkin').addClass("bindcard");
    console.log('err');
  });
},
checkSigned() {
   checkSigned({
    key_admin: this.key,
    uid: openid,
  }).then(json => {
    console.log(json);
    if (json.code === 1045) {
      this.$cardbottom.html(`<p class="qiandao">今日已签到<span class="pubcolor">+${json.data.scores}</span></p>
      <p class="sign_tpl">明日签到得积分<span class="">40</span>　连续签到惊喜更多</p>`);
    } else {

    }
    // SETCHECKI
  }, () => {});
},
checkin(){
    checkin({
    key_admin: this.key,
    uid: openid,
  }).then(json => {
    this.$cardbottom.html(`<p class="qiandao">今日已签到<span class="pubcolor">+${json.data.score}</span></p>
    <p class="sign_tpl">明日签到得积分<span class="">40</span>　连续签到惊喜更多</p>`);
    $('#myModal-1').modal('show');
  }, () => {
    // $('#myModal-1').modal('show');
  });
},
cardimg(){
 cardimg({
  key_admin: this.key,
  openid,
}).then(json => {
  // alert(json.code);
  // alert(json.data.img);
  // alert(json.data);
  // alert(json.msg);
  $('.cardimg')[0].src=json.data.img;
  console.log(json);
}, () => {
  // alert(json.code);
  // alert(json.data.img);
  // alert(json.data);
  // alert(json.msg);
  console.log(json);
});
},
 formatStr(str)
{
str=str.replace(/{key_admin}/ig, this.key);
str=str.replace(/{openid}/ig, openid);
return(str);
},
geticonlist(){
 geticonlist({
  key_admin: this.key,
}).then(json => {
  console.log(json);

  // this.geturlString(name ,url)
  let html = '';
  $.each(json.data, (i, n)=> {
    const url = this.formatStr(n.url);
    html+=`<div class="icon">
     <a href="${url}">
      <img src="${n.logo}" alt="icon">
      <p>${n.title}</p>
     </a>
    </div>`
  });
  this.$iconlist.html(html);
}, () => {
  console.log(json);
});
},
};
index.init();
