const $ = window.$;
import { feedback, feedbackDel } from '../model';
// const startpage = 1;

export const feeds = {
  init() {
    this.initEvent();
    this.initInput();
    this.page = 1;
    this.lines = 10;
    this.query({
      key_admin: $.cookie('ukey'),
      page: this.page,
      lines: this.lines,
      start_time: '',
      end_time: '',
    });
  },

  getLocalTime(day) {
    const y = day.getFullYear();
    const moon = (day.getMonth() + 1) < 10 ? `0${day.getMonth() + 1}` : day.getMonth() + 1;
    const d = day.getDate() < 10 ? `0${day.getDate()}` : day.getDate();
    return `${y}-${moon}-${d}`;
  },
  initInput() {
    const dayBefore = new Date();
    dayBefore.setDate(dayBefore.getDate() - 1);
    $('.sdate')[0].value = this.getLocalTime(dayBefore);
    $('.edate')[0].value = this.getLocalTime(new Date());
  },
  initEvent() {
    // 意见反馈
    $(':radio').change(
      () => {
        if ($(':radio')[0].checked === false) {
          $('.condition').attr('disabled', 'disabled');
          $('.sdate').removeAttr('disabled');
          $('.edate').removeAttr('disabled');
        } else {
          $('.condition').removeAttr('disabled');
          $('.sdate').attr('disabled', 'disabled');
          $('.edate').attr('disabled', 'disabled');
        }
      }
    );
    $('.condition').change(
      () => {
        const day = new Date();
        day.setDate(day.getDate() - $('.condition').val());
        const info = {
          key_admin: $.cookie('ukey'),
          page: this.page,
          lines: this.lines,
          start_time: `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`,
          end_time: `
          ${new Date().getFullYear()}-${new Date().getMonth() + 1}-${new Date().getDate()}
          `,
        };
        feeds.query(info);
      }
    );
    $('.sdate').change(
      () => {
        const day = new Date();
        day.setDate(day.getDate() - $('.condition').val());
        const info = {
          key_admin: $.cookie('ukey'),
          page: this.page,
          lines: this.lines,
          start_time: `${$('.sdate').val()}`,
          end_time: `${$('.edate').val()}`,
        };
        feeds.query(info);
      }
    );
    $('.edate').change(
      () => {
        const day = new Date();
        day.setDate(day.getDate() - $('.condition').val());
        const info = {
          key_admin: $.cookie('ukey'),
          page: this.page,
          lines: this.lines,
          start_time: `${$('.sdate').val()}`,
          end_time: `${$('.edate').val()}`,
        };
        feeds.query(info);
      }
    );
    // 全选
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

    // 批量删除
    $('.problem').click(
      () => {
        const arr = [];
        for (let num = 0; num < $('tbody :checked').length; num++) {
          arr[num] = $('tbody :checked').eq(num).val() - 0;
        }
        if (arr.length === 0) {
          $('.moda_3').show();
        } else {
          $('.moda_2').show();
        }
      }
    );
    $('.cancel').click(
      () => {
        $('.moda_2').hide();
      }
    );
    $('.deleall_1').click(
      () => {
        $('.moda_3').hide();
      }
    );
    $('.deleall').click(
      () => {
        const arr = [];
        for (let num = 0; num < $('tbody :checked').length; num++) {
          arr[num] = $('tbody :checked').eq(num).val() - 0;
        }
        const arr1 = { key_admin: $.cookie('ukey'),
                       id: JSON.stringify(arr),
        };
        $('.moda_2').hide();
        feeds.dele(arr1);
      }
    );

    // $('.prev').on('click', () => {
    //   if (startpage === 1) {
    //     alert('已经是第一页了');
    //   } else {
    //     this.$pagenum = -- this.startpage;
    //     this.list(this.$pagenum);
    //   }
    // });

    $('.prev').on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.query({
          key_admin: $.cookie('ukey'),
          page: this.page,
          lines: this.lines,
          start_time: '',
          end_time: '',
        });
      }
    });
    $('.next').on('click', () => {
      console.log(this.page);
      if (this.page === this.pagenum) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        const info = {
          key_admin: $.cookie('ukey'),
          page: this.page || '1',
          lines: this.lines,
          start_time: '',
          end_time: '',
        };
        feeds.query(info);
      }
    });
    // 旧代码  下一页
    // $('.next').on('click', () => {
    //   const day = new Date();
    //   day.setDate(day.getDate() - $('.condition').val());
    //   console.log(this.page);
    //   if (this.page === this.pagenum) {
    //     alert('已经是最后一页了');
    //   } else {
    //     this.page ++;
    //     const info = {
    //       key_admin: $.cookie('ukey'),
    //       page: this.$page || '1',
    //       lines: '3',
    //       start_time: `${$(':radio')[0].checked} ?
    //        ${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}:
    //        ${$('.sdate').val()}`,
    //       end_time: `${$(':radio')[0].checked} ?
    //        ${new Date().getFullYear()}-${new Date().getMonth() + 1}-${new Date().getDate()}:
    //        ${$('.edate').val()}`,
    //     };
    //     feeds.query(info);
    //   }
    // });
  },
  query(info) {
    feedback(info).then(res => {
      if (res.code === 200) {
        console.log(res);
        $('tbody').empty();
        for (let num = 0; num < res.data.length; num++) {
          const tr = $(`<tr class="tr${num}"><td>
          <input type="checkbox" value="${res.data[num].id}" />
          </td><td></td><td></td>
<td></td><td><a href="/management/reply?id=${res.data[num].id}"> 回复 </a>
<a class="a1"> 详情 </a><a class="a2">删除</a>

<div class="moda">
<p><span class="warning">!</span> 确定要删除该条意见反馈吗？</p>
<button class="no btn btn-default btn-sm">取消</button>
<button class="yes btn btn-primary btn-sm">确定</button>
</div>
<div class="moda_1">
<p class="moda_1p"> <span class="idno"></span><span class="time"></span></p>
<p class="content_1"><p>
<button class="yes_1 btn btn-primary btn-sm">删除</button>
<button class="no_1 btn btn-default btn-sm">返回</button>
</div></td>`);
          $('tbody').append(tr);
        }
        $('.currentpage').html(`当前第${res.page}页`);
        this.pagenum = res.pagenum;
        console.log(res.pagenum);
        $.each(
          res.data, (i, n) => {
            $(`tbody .tr${i} td .a2`).click(
              function a() {
                $(this).next().show();
              }
            );
            $(`tbody .tr${i} td .a1`).click(
              function b() {
                $(this).next().next().next().show();
              }
            );
            $(`tbody .tr${i} td .no`).click(
              () => {
                $('.moda').hide();
              }
            );
            $(`tbody .tr${i} td .no_1`).click(
              () => {
                $('.moda_1').hide();
              }
            );
            $(`tbody .tr${i} td .yes`).last().click(
              function b() {
                $('.moda').hide();
                console.log('hgvhgugfuf');
                const infodel = {
                  key_admin: $.cookie('ukey'),
                  id: JSON.stringify([n.id - 0]),
                };
                feeds.dele(infodel);
                $(this).parent().parent().parent().remove();
              }
            );
            $(`tbody .tr${i} td .yes_1`).last().click(
              () => {
                $('.moda_1').hide();
                $('.moda').show();
              }
            );
            $(`.tr${i} td`).eq(1).html(n.usermember);
            $(`.tr${i} td`).eq(2).html(n.content);
            $(`.tr${i} td`).eq(3).html(n.createtime);
            $(`.tr${i} td`).eq(4).find('.moda_1 .idno').html(n.usermember);
            $(`.tr${i} td`).eq(4).find('.moda_1 .time').html(n.createtime);
            $(`.tr${i} td`).eq(4).find('.moda_1 .content_1').html(n.content);
          }
        );
      } else {
        console.log('nnjnjnjnb');
      }
    }, res => {
      console.log(res.msg);

      if (res.code === 102) {
        $('tbody').html(`<tr><td colspan="5">${res.msg}</td></tr>`);
        $('.pager').css('display', 'none');
      }
    });
  },
  dele(info) {
    feedbackDel(info).then(res => {
      if (res.code === 200) {
        $('.moda_4').show().fadeOut(1000);
        $('tbody :checked').parent().parent().remove();
      } else {
        console.log();
      }
    });
  },
};
