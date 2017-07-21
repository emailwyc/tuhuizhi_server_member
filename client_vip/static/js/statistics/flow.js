require('../../scss/statistics/flow.scss');
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
const $key = $.cookie('ukey');
let pages = 1;
let pands = 2;
$(document).ready(
  () => {
    const dayBefore = new Date();
    dayBefore.setDate(dayBefore.getDate() + 1);
    const getLocalTime = (day) => {
      const y = day.getFullYear();
      const moon = (day.getMonth() + 1) < 10 ? `0${day.getMonth() + 1}` : day.getMonth() + 1;
      const d = day.getDate() < 10 ? `0${day.getDate()}` : day.getDate();
      return `${y}-${moon}-${d}`;
    };
    $('.edate')[0].value = getLocalTime(dayBefore);
    $('.sdate')[0].value = getLocalTime(new Date());
    $('.a1')[0].href =
    `/api/dt/base?service=1000&sdate=${$('.sdate').val()}&edate=${$('.edate').val()}&
size=10&page=1&key=${$key}&action=export`;
    $('.edate')[0].value =
     `${new Date().getFullYear()}-${new Date().getMonth() + 1}-${new Date().getDate() + 1}`;
    $('.sdate')[0].value =
     `${new Date().getFullYear()}-${new Date().getMonth() + 1}-${new Date().getDate()}`;
    const cha = {
      service: $('.service').val(),
      sdate: $('.sdate').val(),
      edate: $('.edate').val(),
      page: '1',
      size: '10',
      key: $key || '202cb962ac59075b964b07152d234b70',
    };
    $.ajax({
      url: `//${location.host}/api/dt/base`,
      dataType: 'json',
      data: cha,
      type: 'GET',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          if (pages === 1) {
            $('.previous_s').attr('disabled', 'disabled');
          } else {
            $('.previous_s').removeAttr('disabled');
          }
          $('tbody').empty();
          for (let num = 0; num < res.data.list.length; num++) {
            const tr = $(`<tr class="tr${num}"><td>
            </td><td></td><td></td>
            <td></td><td></td>
            </tr>`);
            $('tbody').append(tr);
          }
          console.log(res.data.list);
          $.each(
            res.data.list, (i, n) => {
              console.log(n);
              $(`.tr${i} td`).eq(0).html(n.date);
              $(`.tr${i} td`).eq(1).html(n.totalpv);
              $(`.tr${i} td`).eq(2).html(n.totaluv);
              $(`.tr${i} td`).eq(3).html(n.pv);
              $(`.tr${i} td`).eq(4).html(n.uv);
            }
          );
          console.log(res);
        } else {
          console.log();
        }
      },
      error: (json) => {
        console.log(`${location.host}/api/dt/base`);
        console.log(json);
      },
    });
  }
);

$('.query').click(
  () => {
    $('.a1')[0].href =
  `/api/dt/base?service=${$('.service').val()}&sdate=${$('.sdate').val()}&edate=${$('.edate').val()}
    &size=10&page=1&key=${$key}&action=export`.trim();
    const cha = {
      service: $('.service').val(),
      sdate: $('.sdate').val(),
      edate: $('.edate').val(),
      page: '1',
      size: '10',
      key: $key || '202cb962ac59075b964b07152d234b70',
    };
    $.ajax({
      url: `//${location.host}/api/dt/base`,
      dataType: 'json',
      data: cha,
      type: 'GET',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        // console.log(res);
        if (res.code === 200) {
          if (pages === 1) {
            $('.previous_s').attr('disabled', 'disabled');
          } else {
            $('.previous_s').removeAttr('disabled');
          }
          pands = 2;
          pages = 1;
          $('.num_s').html(pages);
          $('tbody').empty();
          for (let num = 0; num < res.data.list.length; num++) {
            const tr = $(`<tr class="tr${num}"><td>
            </td><td></td><td></td>
            <td></td><td></td>
            </tr>`);
            $('tbody').append(tr);
          }
          console.log(res.data.list);
          $.each(
            res.data.list, (i, n) => {
              console.log(n);
              $(`.tr${i} td`).eq(0).html(n.date);
              $(`.tr${i} td`).eq(1).html(n.totalpv);
              $(`.tr${i} td`).eq(2).html(n.totaluv);
              $(`.tr${i} td`).eq(3).html(n.pv);
              $(`.tr${i} td`).eq(4).html(n.uv);
            }
          );
          console.log(res);
        } else if (res.code === 102) {
          alert('未找到匹配项');
          console.log();
        }
      },
      error: (json) => {
        console.log(`${location.host}/api/dt/base`);
        console.log(json);
      },
    });
  }
);
$('.previous_s').click(
  () => {
    const page2 = pages > 1 ? (--pages).toString() : '1';
    const cha = {
      service: $('.service').val(),
      sdate: $('.sdate').val(),
      edate: $('.edate').val(),
      page: page2,
      size: '10',
      key: $key || '202cb962ac59075b964b07152d234b70',
    };
    $.ajax({
      url: `//${location.host}/api/dt/base`,
      dataType: 'json',
      data: cha,
      type: 'GET',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        console.log(res);
        if (res.code === 200) {
          if (pages === 1) {
            $('.previous_s').attr('disabled', 'disabled');
          } else {
            $('.previous_s').removeAttr('disabled');
          }
          $('.num_s').html(pages);
          $('tbody').empty();
          for (let num = 0; num < res.data.list.length; num++) {
            const tr = $(`<tr class="tr${num}"><td>
            </td><td></td><td></td>
            <td></td><td></td>
            </tr>`);
            $('tbody').append(tr);
          }
          console.log(res.data.list);
          $.each(
            res.data.list, (i, n) => {
              console.log(n);
              $(`.tr${i} td`).eq(0).html(n.date);
              $(`.tr${i} td`).eq(1).html(n.totalpv);
              $(`.tr${i} td`).eq(2).html(n.totaluv);
              $(`.tr${i} td`).eq(3).html(n.pv);
              $(`.tr${i} td`).eq(4).html(n.uv);
            }
          );
          console.log(res);
        } else {
          console.log();
        }
      },
      error: (json) => {
        console.log(`${location.host}/api/dt/base`);
        console.log(json);
      },
    });
  }
);
$('.next_s').click(
  () => {
    if (pages < pands) {
      const page1 = (++pages).toString();
      const cha = {
        service: $('.service').val(),
        sdate: $('.sdate').val(),
        edate: $('.edate').val(),
        page: page1,
        size: '10',
        key: $key || '202cb962ac59075b964b07152d234b70',
      };
      $.ajax({
        url: `//${location.host}/api/dt/base`,
        dataType: 'json',
        data: cha,
        type: 'GET',
        xhrFields: {
          withCredentials: true,
        },
        crossDomain: true,
        success: (res) => {
          if (res.code === 200) {
            // console.log(res.data.totalPage);
            if (pages === 1) {
              $('.previous_s').attr('disabled', 'disabled');
            } else {
              $('.previous_s').removeAttr('disabled');
            }
            pands = res.data.totalPage;
            $('.num_s').html(pages);
            $('tbody').empty();
            for (let num = 0; num < res.data.list.length; num++) {
              const tr = $(`<tr class="tr${num}"><td>
            </td><td></td><td></td>
            <td></td><td></td>
            </tr>`);
              $('tbody').append(tr);
            }
            console.log(res.data.list);
            $.each(
            res.data.list, (i, n) => {
              console.log(n);
              $(`.tr${i} td`).eq(0).html(n.date);
              $(`.tr${i} td`).eq(1).html(n.totalpv);
              $(`.tr${i} td`).eq(2).html(n.totaluv);
              $(`.tr${i} td`).eq(3).html(n.pv);
              $(`.tr${i} td`).eq(4).html(n.uv);
            }
          );
            console.log(res);
          } else {
            console.log();
          }
        },
        error: (json) => {
          console.log(`${location.host}/api/dt/base`);
          console.log(json);
        },
      });} else {
      alert('已经是最后一页了');
    }
  }
);
