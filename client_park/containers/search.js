import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import Alert from 'rtui/alert';
import CarList from '../components/carlist';
import Header from '../components/header';
import Back from '../components/back';
import * as actions from '../actions/search';
import utils from '../utils';
const conf = window.conf;

class Search extends Component {
  static propTypes = {
    searchCarList: PropTypes.func.isRequired,
    searchCar: PropTypes.object.isRequired,
    myCars: PropTypes.array.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      carNo: '',
      info: {
        isShow: false,
        isSuccess: false,
        title: '',
        main: '',
      },
      byClick: false,
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.searchCar.code) {
      this.setState({
        info: {
          isShow: true,
          isSuccess: false,
          title: '温馨提示',
          main: '没有搜索到！',
          ok: () => {
            this.refs.searchIn.focus();
            this.setDefault();
          },
        },
      });
    }
  }

  onSelectCar(carNo, carimg) {
    localStorage.setItem(conf.key, JSON.stringify({
      carNo,
      carimg,
    }));
    location.href = `/pay/park?key_admin=${conf.key}`;
  }

  setDefault = () => {
    this.setState({
      info: {
        isShow: false,
        isSuccess: false,
        title: '',
        main: '',
      },
    });
  }

  hindleChange(e) {
    const target = e.target;
    this.setState({
      carNo: target.value,
      info: {
        isShow: false,
        isSuccess: false,
        title: '',
        main: '',
      },
    });
    if (! target.value) {
      this.setState({
        byClick: false,
      });
    }
  }

  handleSelect = (carPara) => {
    this.onSelectCar(carPara.CarSerialNo, carPara.carimg);
  }

  checkFun(value) {
    this.refs.searchIn.blur();
    if (!value) {
      this.setState({
        info: {
          isShow: true,
          isSuccess: false,
          title: '错误提示',
          main: '你还没有输入车牌号！',
          ok: () => {
            this.refs.searchIn.focus();
            this.setDefault();
          },
        },
      });
    } else {
      this.setState({
        byClick: true,
      });
      this.props.searchCarList(this.state.carNo);
    }
  }

  render() {
    const { searchCar, myCars } = this.props;

    return (
      <div className="search-box">
        {conf.key === 'e4273d13a384168962ee93a953b58ffd' ? <Back /> : ''}
        <Header pic={decodeURIComponent(utils.qs('pic'))} />
        <div className="search-con">
          <h4>输入车牌号缴费：</h4>
          <div className="input-box">
            <input ref="searchIn" placeholder="请输入车牌号！" value={this.state.carNo}
              onChange={e => this.hindleChange(e)}
            />
            <button onTouchStart={
              () => {
                this.checkFun(this.state.carNo);
              }
            }
            >查费用</button>
          </div>
        </div>
        <div className="search-list">
        <CarList cars={this.state.byClick ? searchCar.list : myCars} isClick={this.state.byClick}
          onSelect={this.handleSelect}
        />
        </div>
        <Alert {...this.state.info} />
      </div>
    );
  }
}

const mapStateToProps = state => ({
  searchCar: state.searchCar,
  myCars: state.myCar.list,
});

const mapDispatchToProps = dispatch => bindActionCreators(actions, dispatch);

export default connect(mapStateToProps, mapDispatchToProps)(Search);
