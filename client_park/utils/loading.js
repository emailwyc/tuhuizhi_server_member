import React from 'react';
import ReactDOM from 'react-dom';
import Loading from 'rtui/loading';

const loading = () => {
  let loadingCount = 0;
  let dom = null;
  const create = () => {
    // 创建容器
    // const rand = Math.random().toString().replace('0.', '');
    // const attrName = 'loading-box';
    // const attrVal = `loading_${rand}`;
    const el = document.createElement('div');
    // el.setAttribute(attrName, attrVal);
    document.querySelector('body').appendChild(el);

    return el;
  };
  // 显示loading
  const show = () => {
    if (loadingCount === 0) {
      dom = create();
      // create loading
      const cfg = {
        action: 'show',
      };
      ReactDOM.render(<Loading {...cfg} />, dom);
    }
    loadingCount++;
  };

  // 销毁loading
  const hide = () => {
    if (loadingCount === 1) {
      ReactDOM.unmountComponentAtNode(dom);
      document.querySelector('body').removeChild(dom);
    }
    loadingCount--;
  };

  return {
    show,
    hide,
    loadingCount,
  };
};

export default loading();

