require('../../scss/taxi/taxi.scss');
import { getOrderList } from '../model/taxi';
const $ = window.$;
require('../modules/cookie')($);

const taxi = {
  init() {
    this.initDom();
    this.initEvent();
    this.getOrderList();
    this.orderstatus = '';
  },
  initDom() {
    this.$table = $('.table tbody');
  },
  initEvent() {},
  getOrderList() {
    getOrderList({
      key_admin: $.cookie('ukey'),
      page: '1',
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data.data, (i, v) => {
        let orderstatus;
        if (v.orderstatus === '0') {
          orderstatus = '请求订单';
        } else if (v.orderstatus === '1') {
          // console.log(orderstatus);
          orderstatus = '订单请求成功';
        } else if (v.orderstatus === '2') {
          orderstatus = '请求订单失败';
        } else if (v.orderstatus === '3') {
          orderstatus = '已被司机接单';
        } else if (v.orderstatus === '4') {
          orderstatus = '取消订单';
        } else if (v.orderstatus === '4') {
          orderstatus = '订单完成';
        }
        console.log(v);
        // rule 车型， city_name 城市， start_name 出发地， end_name 目的地， departure_time 出发时间
        // passenger_phone 手机号， orderstatus 订单状态， scoreplan 预估积分
        // scoresense 实际积分， pay_time 支付时间， total_price总 金额
        tr += `<tr><td>${v.rule}</td><td>${v.city_name}</td><td>${v.start_name}</td>
        <td>${v.end_name}</td><td>${v.departure_time}</td><td>${v.passenger_phone}</td>
        <td>${orderstatus}</td><td>${v.scoreplan}</td><td>${v.scoresense}</td>
        <td>${v.pay_time}</td><td>${v.total_price}</td></tr>`;
        this.$table.html(tr);
      });
    }, json => {
      console.log(json);
    });
  },
};
taxi.init();
