import React, { Component, PropTypes } from 'react';
import './carlist.scss';

class CarList extends Component {
  static propTypes = {
    cars: PropTypes.array.isRequired,
    isClick: PropTypes.bool.isRequired,
    onSelect: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const { cars, onSelect, isClick } = this.props;

    return (
      <ul className="tap-box">
      {
        (cars.length === 1 && isClick) ? onSelect(cars[0]) : cars.map(car => {
          const pic = car.carimg && { backgroundImage: `url(${car.carimg}-w640)` } || {};
          return (<li className="item-tap" key={car.CarSerialNo} onClick={() => onSelect(car)}>
            <div className="item-con">
              <figure><em style={pic} ></em></figure>
              <span>车牌号：{car.CarSerialNo}</span></div>
            <div className="iconfont icon-jinru"></div>
          </li>);
        })
      }
      </ul>
    );
  }
}

export default CarList;
