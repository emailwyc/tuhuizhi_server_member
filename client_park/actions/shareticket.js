import utils from '../utils';
const conf = window.conf;
const wx = window.wx;
import { getUserInfo } from 'wxlogin';

// 购买优惠券列表
const infoRequest = () => ({
  type: 'INFOREQUEST',
});

const infoSuccess = data => ({
  type: 'INFOSUCCESS',
  data,
});

const infoError = () => ({
  type: 'INFOERROR',
});

const shareFun = (data) => new Promise(() => {
  const sucessFun = (type, code) => {
    utils.http({
      url: utils.api('/ParkApp/ParkPay/shareParkCoupon'),
      data: {
        // key_admin: conf.key,
        donate_num: localStorage.getItem(`${conf.key}share${data.prize_id}`),
        openid: getUserInfo().openid,
        active_code: code,
        type,  // 0：分享到朋友圈 1：分享给好友
        prize_id: data.prize_id,
      },
      isSuccessShow: true,
    }).then((res) => {
      console.log(res);
    }, (error) => {
      console.log(error);
    });
  };

  const share = (url, code) => {
    // alert(data.image_url);
    wx.onMenuShareTimeline({
      title: data.main_info, // 分享标题
      link: url, // 分享链接
      imgUrl: data.image_url, // 分享图标
      success: (info) => {
        sucessFun(0, code, info);
      },
      cancel: () => {
        // 用户取消分享后执行的回调函数
        // alert(JSON.stringify(error));
      },
      fail: () => {
        // alert(JSON.stringify(error));
      },
    });

    wx.onMenuShareAppMessage({
      title: data.main_info, // 分享标题
      desc: data.extend_info, // 分享描述
      link: url, // 分享链接
      imgUrl: data.image_url, // 分享图标
      type: '', // 分享类型,music、video或link，不填默认为link
      dataUrl: '',
       // 如果type是music或video，则要提供数据链接，默认为空
      success: (info) => {
        // 用户确认分享后执行的回调函数
        sucessFun(1, code, info);
      },
      cancel: () => {
        // 用户取消分享后执行的回调函数
        // alert(JSON.stringify(error));
      },
      fail: () => {
        // alert(JSON.stringify(error));
      },
    });
  };

  // 得到code码
  utils.http({
    url: utils.api('/ParkApp/ParkPay/createActivateCode'),
    isSuccessShow: true,
  }).then((res) => {
    if (location.href.indexOf('h5.rtmap.com') > 0) {
      share(`http://fw.joycity.mobi/share/index.html?key_admin=${conf.key}&data=${
        encodeURIComponent(JSON.stringify(data))}&code=${res.data}`, res.data);
    } else {
      share(`http://fw.joycity.mobi/share/test.html?key_admin=${conf.key}&data=${
        encodeURIComponent(JSON.stringify(data))}&code=${res.data}`, res.data);
    }
  }, (error) => {
    console.log(error);
  });
});

export const myTicketInfoFun = (id, status) => dispatch => {
  dispatch(infoRequest());
  utils.http({
    url: utils.api('/ParkApp/ParkPay/myParkCouponDetails'),
    data: {
      key_admin: conf.key,
      prize_id: id,
      status,
      openid: getUserInfo().openid,
    },
    isSuccessShow: true,
  }).then((data) => {
    dispatch(infoSuccess(data.data));
    shareFun(data.data);
  }, (error) => {
    dispatch(infoError(error));
  });
};
