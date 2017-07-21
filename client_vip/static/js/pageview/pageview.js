require('../../scss/pageview/pageview.scss');
import { out } from '../modules/out.js';
import { pagepvData } from '../model/';
const $ = window.$;
require('../modules/cookie')($);
import echarts from 'echarts';

const pageview = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
    this._pagepvData();
  },
  initDom() {
    this.$out = $('.out');
    this.$tbody = $('.table tbody');
    this.$table = $('.table');
    this.$pieWrapper = $('.pie-wrapper');
    this.$switch = $('.add-btn');
    this.$searchInput = $('.searchinput');
    this.$searchBtn = $('.search-btn');
    this.store = {
      data: null,
    };
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$searchBtn.on('click', () => {
      const name = this.$searchInput.val();
      this._pagepvData(name);
    });

    $(document).keydown((event) => {
      if (event.keyCode === 13) {
        const name = this.$searchInput.val();
        this._pagepvData(name);
      }
    });

    this.$switch.on('click', () => {
      if (!this.store.data) {
        alert('暂无数据');
        return;
      }
      const flag = this.$pieWrapper.css('display') === 'none';
      if (flag) {
        this.$switch.text('表格');
        this.$pieWrapper.show();
        this.$table.hide();
        $('.pager').hide();
      } else {
        this.$switch.text('折线图');
        this.$pieWrapper.hide();
        this.$table.show();
        $('.pager').show();
      }
    });
  },
  _pagepvData(name) {
    const params = {
      ukey: $.cookie('ukey'),
    };
    if (name) {
      params.name = name;
    }
    pagepvData(params).then(json => {
      console.log(json);
      const data = json.data.data;
      this.store.data = data;
      this.initPage();
      this.initEcharts(this.store.data);
    }, json => {
      if (!this.store.data) {
        this.$tbody.html(`<tr><td colspan="3">${json.msg}</td></tr>`);
        $('.pager').hide();
        return;
      }
      alert(json.msg);
      // console.log(json);
    });
  },
  render(min, max) {
    const data = this.store.data;
    if (data.length === 0) {
      this.$tbody.html('<tr><td colspan="3">找不到相关数据</td></tr>');
      $('.pager').hide();
      return;
    }
    let tpl = '';
    for (let m = min; m < max; m++) {
      const v = data[m];
      tpl += `<tr>
        <td>${v.name}</td>
        <td>${v.ctime}</td>
        <td>${v.num}</td>
      </tr>`;
    }
    this.$tbody.html(tpl);
    return;
  },
  initEcharts(data) {
    if (!this.myChart) {
      this.myChart = echarts.init(this.$pieWrapper.get(0));
    }
    const legend = [];
    const xAxis = [];
    const series = [];
    const allData = {};
    $.each(data, (i, v) => {
      xAxis.push(v.ctime);
      if (allData.hasOwnProperty(v.name)) {
        allData[v.name].data.push([v.ctime, v.num]);
      } else {
        allData[v.name] = {
          name: v.name,
          type: 'line',
          connectNulls: true,
          data: [[v.ctime, v.num]],
        };
        legend.push(v.name);
      }
    });
    $.each(allData, (i, v) => {
      series.push(v);
    });
    const option = {
      title: {
        text: `${data[0].describe || ''}页面访问量`,
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
        left: 'left',
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
        right: 'right',
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
    this.myChart.setOption(option, true);
    const flag = this.$table.css('display') === 'none';
    if (!flag) {
      this.$pieWrapper.hide();
    }
  },
  initPage() {
    this.page = {
      lines: 10,
      startpage: 1,
      allpage: 1,
      total: this.store.data.length,
    };
    this.page.allpage = Math.ceil(this.store.data.length / this.page.lines);
    this.$pager = $('.pager');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$page_num = $('.pagenum');
    this.$currentppage = $('.currentpage');
    this.$total = $('.total');
    this.$lines = $('.lines');
    this.$gopage = $('.gopage');
    this.$gopage_btn = $('.gopage_btn');

    this.$currentppage.html(`${this.page.startpage}/${this.page.allpage}页`);
    this.$lines.text(`${this.page.lines}条/页`);
    this.$total.html(`共${this.page.total}条记录`);

    this.countPage();

    this.$prev.off('click').on('click', () => {
      if (this.page.startpage === 1) {
        alert('已经是第一页了');
      } else {
        this.page.startpage = --this.page.startpage;
        this.$currentppage.html(`${this.page.startpage}/${this.page.allpage}页`);
        this.countPage();
      }
    });
    this.$next.off('click').on('click', () => {
      if (this.page.startpage === this.page.allpage) {
        alert('已经是最后一页了');
      } else {
        this.page.startpage = ++this.page.startpage;
        this.$currentppage.html(`${this.page.startpage}/${this.page.allpage}页`);
        this.countPage();
      }
    });
    this.$gopage_btn.off('click').on('click', () => {
      const goNum = Number(this.$gopage.val());
      if (goNum > this.page.allpage || goNum < 1) {
        alert('请输入正确页码');
        return;
      }
      this.page.startpage = goNum;
      this.$currentppage.html(`${this.page.startpage}/${this.page.allpage}页`);
      this.countPage();
    });
  },
  countPage() {
    const min = this.page.startpage === 1 ? 0 : ((this.page.startpage - 1) * this.page.lines);
    const max = this.page.startpage === this.page.allpage ? this.page.total : this.page.startpage * this.page.lines;
    this.render(min, max);
  },
};

pageview.init();
