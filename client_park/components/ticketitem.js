import React, { Component, PropTypes } from 'react';
import './ticketitem.scss';

class TicketItem extends Component {

  static propTypes = {
    list: PropTypes.array.isRequired,
    ontap: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const { list, ontap } = this.props;
    return (
      <div>
      {
        list.length > 0 ? list.map((item) => (
          <div key={item.id} className="item" onClick={() => ontap(item.id)}>
            <div className="item-inner">
              <i className="radius-top"></i>
              <div className="item-info">
                <dl className="item-left">
                  <dt><em style={{ backgroundImage: `url(${item.image_url})` }}></em></dt>
                  <dd>
                  <h2>{item.main_info}</h2>
                  <h3>{item.extend_info}</h3>
                  {
                    // <p className="active">500张</p>
                  }
                  </dd>
                </dl>
                <div className="iconfont icon-jinru"></div>
              </div>
              <i className="radius-bottom"></i>
            </div>
          </div>
        )) : <div className="no-discount">暂无数据</div>
      }
      </div>
    );
  }
}

export default TicketItem;
