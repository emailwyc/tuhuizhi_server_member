require('../../scss/parking/order.scss');
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
console.log($('.query'));
import { confirmInvoice, getOrderList } from '../model/order';
const key1 = $.cookie('ukey');
const $status = $('.status');
const $createTime = $('.create_time');
const $payTime = $('.pay_time');
const $invoiceTime = $('.invoice_time');
const $query = $('.query');
const $openid = $('.openid');
const $orderno = $('.orderno');
const $carnum = $('.carnum');
const $previous = $('.previous');
const $next = $('.next');
let $panduan;
let page = 1;
const $main = {
  getLocalTime(nS) {
    const day = new Date(parseInt(nS, 10) * 1000);
    const y = day.getFullYear();
    const moon = (day.getMonth() + 1) < 10 ? `0${day.getMonth() + 1}` : day.getMonth() + 1;
    const d = day.getDate() < 10 ? `0${day.getDate()}` : day.getDate();
    const h = day.getHours() < 10 ? `0${day.getHours()}` : day.getHours();
    const m = day.getMinutes() < 10 ? `0${day.getMinutes()}` : day.getMinutes();
    return `${y}-${moon}-${d} ${h}:${m}`;
  },
  method(querystr) {
    getOrderList(querystr).then(res => {
      if (res.code === 200) {
        if (page <= res.page.total_page) {
          $('.num').html(page);
          $('.num_p').html(res.page.total_page);
          $panduan = res.page.total_page;
          $('tbody').empty();
          console.log($('tbody tr td').last().html());
          $.each(
            res.data, (i, n) => {
              let thing;
              let statusKey;
              if (n.status === '0') {
                thing = '新订单';
                statusKey = 1;
              } else if ((n.status - 0) && n.invoice_time === '0') {
                thing = '已付款';
                statusKey = 2;
              } else if (!!(n.invoice_time - 0)) {
                thing = '已开票';
                statusKey = 3;
              }


              const tr = $(`<tr class="tr${i}">
              <td><input type="checkbox" value="${n.id}" key="${statusKey}" /></td>
              <td><input type="text" value="${n.orderno}" class="orderno" readonly /></td>
              <td>${this.getLocalTime(n.createtime)}</td>
              <td>${n.mobile ? n.mobile : ''}</td>
              <td>${n.carno}</td><td>${thing}</td>
              <td>${n.pay_time ? this.getLocalTime(n.pay_time) : ''}</td>
              <td>${n.total_fee}</td><td>${(n.total_fee - n.payfee).toFixed(2)}</td>
              <td>${n.payfee ? n.payfee : ''}</td>
              <td>${(n.paytype - 0) === 1 ? '积分支付' : '微信支付'}</td><td>详情</td></tr>`);
              $('tbody').append(tr);

              $(`tbody .tr${i} td`).last().click(
                function aaa() {
                  $('.orderno_1').html($(this).parent().children().eq(1).children().val());
                  $('.status_1').html($(this).parent().children().eq(5).html());
                  $('.carnum_1').html($(this).parent().children().eq(4).html());
                  $('.time_1').html($(this).parent().children().eq(2).html());
                  $('.pay').html($(this).parent().children().eq(7).html());
                  $('.pay_1').html($(this).parent().children().eq(8).html());
                  $('.pay_2').html($(this).parent().children().eq(9).html());
                  console.log($(this).parent().children().eq(1).html());
                  $('.detail').show();
                }
              );
            }
          );
        }
      } else {
        console.log('cuowu');
      }
    });
  },
  // 开票接口
  bill(info) {
    confirmInvoice(info).then(() => {
      $('tbody :checked').parent().parent().children().eq(5).html('已开票');
      $('.state').show();
    });
  },
};

// 根据状态禁用相应的日期选择
const query = { key_admin: key1,
                   status: 2,
                   create_time: $createTime.val(),
                   pay_time: $payTime.val(),
                   invoice_time: $invoiceTime.val(),
                   openid: $openid.val(),
                   orderno: $orderno.val(),
                   carno: $carnum.val(),
                   page: 1 };
console.log(typeof($createTime.val()));
$main.method(query);

$('thead input').click(
  () => {
    console.log($('thead input')[0].checked);
    if ($('thead input')[0].checked) {
      for (let num = 0; num < $('tbody input').length; num++) {
        $('tbody input')[num].checked = true;
      }
    } else {
      for (let num = 0; num < $('tbody input').length; num++) {
        $('tbody input')[num].checked = false;
      }
    }
  }
);
$status.change(
  () => {
    console.log($status.val() === '');
    if ($status.val() === '1') {
      $payTime.attr('disabled', 'disabled');
      $invoiceTime.attr('disabled', 'disabled');
      $('.kaipiao').attr('disabled', 'disabled');
    } else if ($status.val() === '2') {
      $payTime.removeAttr('disabled');
      $invoiceTime.attr('disabled', 'disabled');
      $('.kaipiao').removeAttr('disabled');
    } else if ($status.val() === '3') {
      $payTime.removeAttr('disabled');
      $invoiceTime.removeAttr('disabled');
      $('.kaipiao').attr('disabled', 'disabled');
    } else {
      $payTime.removeAttr('disabled');
      $invoiceTime.removeAttr('disabled');
    }
  }
);
//
const getQueryStr = (pageNum = 1) => ({
  key_admin: key1,
  status: $status.val(),
  create_time: $createTime.val(),
  pay_time: $payTime.val(),
  invoice_time: $invoiceTime.val(),
  openid: $openid.val(),
  orderno: $orderno.val(),
  carno: $carnum.val(),
  page: pageNum });
  // 查询
$query.click(
  () => {
    const querystr = getQueryStr();
    console.log(typeof($createTime.val()));
    $main.method(querystr);
  }
);

// 上一页
$previous.click(
  () => {
    const page2 = page > 1 ? (--page).toString() : '1';
    console.log(page2);
    const querystr = getQueryStr(page2);
    if (page2 === '0') {
      alert('已经是第一页了');
    } else {
      $main.method(querystr);
    }
  }
);

// 下一页
$next.click(
  () => {
    if (page < $panduan) {
      const page1 = (++page).toString();
      console.log(page1);
      const querystr = getQueryStr(page1);
      $main.method(querystr);
    } else {
      alert('已经是最后一页了');
    }
  }
);
$('.bill').click(
  () => {
    $main.bill();
  }
);
// 开票
$('.kaipiao').click(
  () => {
    const arr = [];
    for (let num = 0; num < $('tbody :checked').length; num++) {
      arr[num] = $('tbody :checked').eq(num).val() - 0;
      if ($('tbody :checked').eq(num).attr('key') !== '2') {
        alert('需选择已付款订单');
        return;
      }
    }
    const arr1 = { key_admin: key1,
                   orderIds: JSON.stringify(arr),
    };
    if (arr.length === 0) {
      alert('请选择订单');
    } else {
      $main.bill(arr1);
    }
  }
);
$('.detail').css(
  { width: $('.main').width(),
  height: $('.sideber').height() - $('.footer').height(),
  top: $('.navbar').outerHeight(),
   }
);
$('.dhead').click(
  () => {
    $('.detail').hide();
  }
);
$('.back').click(
  () => {
    $('.detail').hide();
  }
);
