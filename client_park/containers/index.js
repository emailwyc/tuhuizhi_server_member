import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as actions from '../actions';
import ShowFee from '../components/showfee';
const conf = window.conf;

class Index extends Component {
  static propTypes = {
    getParkObj: PropTypes.object.isRequired,
    getPark: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      data: [{
        url: '/park/search',
        class: 'icon-jiaofei',
        text: '缴费',
      },
      {
        url: '',
        class: 'icon-parking',
        text: '停车',
      },
      {
        url: '',
        class: 'icon-iconfont18zhaoche2',
        text: '寻车',
      },
      {
        url: '/park/records',
        class: 'icon-fapiao',
        text: '缴费记录',
      },
      {
        url: '/park/buy',
        class: 'icon-parking',
        text: '停车券购买',
      },
      {
        url: '/park/myticket',
        class: 'icon-parking',
        text: '我的券',
      },
      {
        url: '/park/activate',
        class: 'icon-parking',
        text: '激活',
      }],
      pic: '',
    };
  }

  componentDidMount() {
    this.props.getPark();
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      pic: nextProps.getParkObj.logo,
    });
  }

  getIndex() {
    return this.state.data.map((item, index) =>
      <li key={index} className="item-tap" onClick={
        () => (item.url ? this.goLink(item.url) : '')}
      >
        <div className="item-con">
          <i className={`iconfont ${item.class}`}>
          </i><span>{item.text}</span></div>
        <div className="iconfont icon-jinru"></div>
      </li>
    );
  }

  // empty() {
  //   localStorage.clear();
  // }

  toRecord(toUrl) {
    if (toUrl === '/park/search') {
      location.href = `${toUrl}?key_admin=${conf.key}&pic=${
        encodeURIComponent(this.props.getParkObj.logo)}`;
    } else {
      location.href = `${toUrl}?key_admin=${conf.key}`;
    }
  }

  goLink = (toUrl) => {
    this.toRecord(toUrl);
  }

  render() {
    const { getParkObj } = this.props;
    const htmlBox = [];
    getParkObj.list.forEach((item, i) => {
      if (item.location === 'total') {
        if (item.leftnum) {
          htmlBox.push(<h3 key={i}>空余车位数：{item.leftnum}</h3>);
        } else {
          htmlBox.push(<h3 key={i}>无车位状态数！</h3>);
        }
      } else {
        htmlBox.push(<p key={i}>{item.location}： 空闲<strong>{item.leftnum}</strong>个</p>);
      }
    });

    return (
      <div className="index">
        <dl className="index-hea">
          <dt className="iconfont icon-parking iconfont-parking2"></dt>
          <dd>
            {htmlBox}
          </dd>
        </dl>
        <ul className="tap-box">
          {this.getIndex()}
        </ul>
        {
          <ShowFee />
        }
      </div>
    );
  }
}

const mapStateToProps = state => ({
  getParkObj: state.getParkObj,
});
const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Index);
