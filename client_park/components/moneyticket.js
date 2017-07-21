import React, { Component, PropTypes } from 'react';
import './moneyticket.scss';

class MoneyTicket extends Component {
  static propTypes = {
    dataList: PropTypes.array.isRequired,
    onClickFun: PropTypes.func,
    value: PropTypes.string.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      checked: this.props.value,
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      checked: nextProps.value,
    });
  }

  render() {
    const { dataList, onClickFun } = this.props;
    const dataHtml = dataList.map(data => {
      console.log(data);
      return (
        <dl key={data.prize_id} className="hea">
          <dt><em style={{ backgroundImage: `url(${data.image_url})` }}></em></dt>
          <dd className="all-dd">
            <h2>{data.main_info}</h2>
            <h3>{data.extend_info}</h3>
            <p className="active">{data.num}张抵用 {(data.num * data.price).toFixed(2)}元</p>
          </dd>
          <dd className={`icon-lists ${this.state.checked}`} onClick={onClickFun}>
              <i className={`icon iconfont ${this.state.checked ?
                 'icon-xuanzhong' : 'icon-seiyw32'}`}
              ></i>
          </dd>
        </dl>
      );
    });

    return (
      <li>{dataHtml}</li>
    );
  }
}

export default MoneyTicket;
