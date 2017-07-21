export const park = (state = {
  info: {},
}, action) => {
  switch (action.type) {
    case 'CHOOSECARREQUEST':
      return Object.assign({}, state, {});
    case 'CHOOSECARSUCCESS':
      return Object.assign({}, state, { info: action.data });
    case 'CHOOSECARERROR':
      return Object.assign({}, state, {});
    default:
      return state;
  }
};

export const order = (state = {
  info: {},
}, action) => {
  switch (action.type) {
    case 'ORDERREQUEST':
      return Object.assign({}, state, {});
    case 'ORDERSUCCESS':
      return Object.assign({}, state, { info: action.data });
    case 'ORDERERROR':
      return Object.assign({}, state, {});
    default:
      return state;
  }
};

export const cansel = (state = {
}, action) => {
  switch (action.type) {
    case 'CANCELREQUEST':
      return Object.assign({}, state, {});
    case 'CANCELSUCCESS':
      return Object.assign({}, state, action.data);
    case 'CANCELERROR':
      return Object.assign({}, state, {});
    default:
      return state;
  }
};

export const pay = (state = {
  isShow: false,
  isSuccess: false,
  title: '',
  main: '',
  mainTit: '',
  tip: '',
}, action) => {
  switch (action.type) {
    case 'SETDEFAULT':
      return Object.assign({}, state, {
        isShow: false,
        isSuccess: false,
        title: '',
        main: '',
        mainTit: '',
        tip: '',
      });
    case 'PAYREQUEST':
      return Object.assign({}, state, {
        isShow: false,
        isSuccess: false,
        title: '',
        main: '',
        mainTit: '',
        tip: '',
      });
    case 'PAYSUCCESS':
      return Object.assign({}, state, {
        isShow: true,
        isSuccess: true,
        title: '支付成功',
        main: '请10分钟内离场',
        mainTit: '欢迎下次光临',
        tip: '注：凭“缴费记录”可领取发票',
      });
    case 'PAYERROR':
      return Object.assign({}, state, {
        isShow: true,
        isSuccess: false,
        title: '支付失败',
        main: '请重新支付',
        mainTit: '',
        tip: '',
        data: action.data,
      });
    default:
      return state;
  }
};

// 积分

export const pointOrder = (state = {
  info: {},
}, action) => {
  switch (action.type) {
    case 'POINTREQUEST':
      return Object.assign({}, state, {});
    case 'POINTSUCCESS':
      return Object.assign({}, state, { info: action.data });
    case 'POINTERROR':
      return Object.assign({}, state, {});
    default:
      return state;
  }
};
