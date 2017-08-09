require('../../scss/dashboard/pageviewdetails.scss');
import { out } from '../modules/out.js';
import { pagepvData } from './model/pageview';
require('../modules/bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);
import echarts from 'echarts';

const columnAdvertisement = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
    this._pagepvData();
  },
  initDom() {
    this.$out = $('.out');
    this.$tbody = $('.table tbody');
    this.$table = $('.table-responsive');
    this.$pieWrapper = $('.pie-wrapper');
    this.$switch = $('#switch');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$switch.on('click', () => {
      const flag = this.$pieWrapper.css('display') === 'none';
      if (flag) {
        this.$switch.text('表格');
        this.$pieWrapper.show();
        this.$table.hide();
      } else {
        this.$switch.text('折线图');
        this.$pieWrapper.hide();
        this.$table.show();
      }
    });
  },
  _pagepvData() {
    pagepvData({
      ukey: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      const data = json.data.data;
      this.render(data);
      this.initEcharts(data);
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      this.$tbody.append(`<tr><td colspan="4">${json.msg}</td></tr>`);
    });
  },
  render(data) {
    if (data.length === 0) {
      this.$tbody.append('<tr><td colspan="4">找不到相关数据</td></tr>');
      return;
    }
    let tpl = '';
    $.each(data, (i, v) => {
      tpl += `<tr>
        <td>${v.describe}</td>
        <td>${v.pagename}</td>
        <td>${v.ctime}</td>
        <td>${v.num}</td>
      </tr>`;
    });
    this.$tbody.append(tpl);
    return;
  },
  initEcharts(data) {
    this.myChart = echarts.init(this.$pieWrapper.get(0));
    const legend = [];
    const xAxis = [];
    const series = [];
    const allData = {};
    $.each(data, (i, v) => {
      xAxis.push(v.ctime);
      if (allData.hasOwnProperty(v.pagename)) {
        allData[v.pagename].data.push([v.ctime, v.num]);
      } else {
        allData[v.pagename] = {
          name: v.pagename,
          type: 'line',
          connectNulls: true,
          data: [[v.ctime, v.num]],
        };
        legend.push(v.pagename);
      }
    });
    $.each(allData, (i, v) => {
      series.push(v);
    });
    const option = {
      title: {
        text: `${data[0].describe}页面访问量`,
        subtext: '',
        x: 'center',
      },
      tooltip: {
        show: true,
        trigger: 'item',
        formatter(params) {
          const text = `${params.seriesName}<br />访问量${params.value[1]}<br />${params.value[0]}`;
          return text;
        },
      },
      toolbox: {
        show: true,
        feature: {
          restore: { show: true },
          saveAsImage: { show: true },
        },
      },
      dataZoom: {
        show: true,
      },
      legend: {
        orient: 'vertical',
        left: 'left',
        data: legend,
      },
      grid: {
        y2: 80,
      },
      xAxis: [{
        type: 'time',
        splitNumber: 10,
        boundaryGap: false,
      }],
      yAxis: [{
        type: 'value',
      }],
      series,
    };
    this.myChart.setOption(option);
    this.$pieWrapper.hide();
  },
};

columnAdvertisement.init();
