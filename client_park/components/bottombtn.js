import React, { Component, PropTypes } from 'react';
import './bottombtn.scss';

class BottomBtn extends Component {
  static propTypes = {
    btnList: PropTypes.array.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const { btnList } = this.props;

    return (
      <footer className="footer">
        {
          btnList.map((item, index) => <div key={index} onClick={
            () => item.btnFun && item.btnFun()} style={item.style && item.style
            } className={`butn butn${item.id}`}
          >{item.btnName}</div>)
        }
      </footer>
    );
  }
}

export default BottomBtn;
