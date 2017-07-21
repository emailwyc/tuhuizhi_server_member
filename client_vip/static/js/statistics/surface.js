require('../../scss/statistics/surface.scss');
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
const apiPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? 'https://mem.rtmap.com' : 'https://backend.rtmap.com';
const $key = $.cookie('ukey');
let paget = 1;
let pandt = 2;
console.log($key);
const mains = {
  query(info, e) {
    // console.log(1111);
    // const e1 = e || 0;
    // if (!$('.gdate').val()) { alert('日期不能为空');} else {
    $.ajax({
      // url: 'https://backend.rtmap.com/MerAdmin/Wechatmember/wechat_member_like',
      url: `${apiPath}/MerAdmin/Wechatmember/wechat_member_like`,
      dataType: 'jsonp',
      data: info,
      type: 'POST',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          // console.log(res.data);
          paget = res.data.page;
          if (res.data.page === 1) {
            $('.previous_t').attr('disabled', 'disabled');
          } else {
            $('.previous_t').removeAttr('disabled');
          }
          pandt = res.data.page_num;
          $('.num_t').html(res.data.page);
          $('.num_allpage').html(res.data.page_num);
          $('tbody').empty();
          for (let num = 0; num < res.data.data.length; num++) {
            const tr = $(`<tr class="tr${num}"><td>
            <img src="${res.data.data[num].headimgurl}"/>
          </td><td></td><td></td>
          <td></td><td></td><td></td>
          </tr>`);
            $('tbody').append(tr);
          }
            // console.log(res.data);
          $.each(
          res.data.data, (i, n) => {
            // console.log(n);
            $(`.tr${i} td`).eq(1).html(n.subscribe_time);
            $(`.tr${i} td`).eq(2).html(n.nickname);
            $(`.tr${i} td`).eq(3).html(n.sex === '1' ? '男' : '女');
            $(`.tr${i} td`).eq(4).html(n.city);
            $(`.tr${i} td`).eq(5).html(n.openid);
          }
        );
        } else if (res.code === 102 && e) {
          $('tbody').empty();
          alert('找不到匹配项');
        }
          // console.log(time);
          // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => {
        console.log(json);
      },
    });
  },
  daochu(date) {
    $.ajax({
      url: 'https://mem.rtmap.com/MerAdmin/Wechatmember/wechat_member_export',
      dataType: 'jsonp',
      data: date,
      type: 'POST',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          console.log(res.path);
          window.location.href = res.data.path;
        } else {
          console.log();
        }
        // console.log(time);
        // dt('api', { url, params, res, time: (+(new Date) - time) });
      },
      error: (json) => {
        console.log(json);
      },
    });
  },
};
$(document).ready(
  () => {
    const dayBefore = new Date();
    dayBefore.setDate(dayBefore.getDate() - 1);
    const getLocalTime = (day) => {
      const y = day.getFullYear();
      const moon = (day.getMonth() + 1) < 10 ? `0${day.getMonth() + 1}` : day.getMonth() + 1;
      const d = day.getDate() < 10 ? `0${day.getDate()}` : day.getDate();
      return `${y}-${moon}-${d}`;
    };
    console.log(getLocalTime);
    // $('.edate')[0].value = getLocalTime(new Date());
    // $('.gdate')[0].value = getLocalTime(dayBefore);
    const info2 = { key_admin: $key || '202cb962ac59075b964b07152d234b70',
                  page: 1,
                  lines: 10,
                  end: $('.edate').val(),
                  startime: $('.gdate').val(),
                  nickname: $('.nname').val(),
                  sex: ($('.sex').val() - 0),
                  city: $('.region').val(),
                };
    mains.query(info2);
  });
$('.query').click(
  (e) => {
    console.log(e);
    const info = { key_admin: $key || '202cb962ac59075b964b07152d234b70',
                  page: 1,
                  lines: 10,
                  end: $('.edate').val(),
                  startime: $('.gdate').val(),
                  nickname: $('.nname').val(),
                  sex: ($('.sex').val() - 0),
                  city: $('.region').val(),
                };
    mains.query(info, e);
  }
);
$('.dc').click(
  () => {
    const info1 = { key_admin: $key || '202cb962ac59075b964b07152d234b70',
                  page: 1,
                  lines: 10,
                  end: $('.edate').val(),
                  startime: $('.gdate').val(),
                  nickname: $('.nname').val(),
                  sex: ($('.sex').val() - 0),
                  city: $('.region').val(),
                };
    mains.daochu(info1);
  }
);
$('.previous_t').click(
  () => {
    const page1 = paget > 1 ? (--paget).toString() : '1';
    const info1 = { key_admin: $key || '202cb962ac59075b964b07152d234b70',
                  page: page1,
                  lines: 10,
                  end: $('.edate').val(),
                  startime: $('.gdate').val(),
                  nickname: $('.nname').val(),
                  sex: ($('.sex').val() - 0),
                  city: $('.region').val(),
                };
    mains.query(info1);
  }
);
$('.next_t').click(
  () => {
    if (paget < pandt) {
      const page2 = (++paget).toString();
      const info1 = { key_admin: $key || '202cb962ac59075b964b07152d234b70',
                    page: page2,
                    lines: 10,
                    end: $('.edate').val(),
                    startime: $('.gdate').val(),
                    nickname: $('.nname').val(),
                    sex: ($('.sex').val() - 0),
                    city: $('.region').val(),
                  };
      mains.query(info1);
    } else {
      alert('已经是最后一页了');
    }
  }
);
$('.gopage').click(
  () => {
    const info = { key_admin: $key || '202cb962ac59075b964b07152d234b70',
                  page: $('.gopagenum').val(),
                  lines: 10,
                  end: $('.edate').val(),
                  startime: $('.gdate').val(),
                  nickname: $('.nname').val(),
                  sex: ($('.sex').val() - 0),
                  city: $('.region').val(),
                };
    mains.query(info);
  }
);
