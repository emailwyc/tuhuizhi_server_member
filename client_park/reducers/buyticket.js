export const buyTicketInfo = (state = {
  obj: {},
}, action) => {
  switch (action.type) {
    case 'BUYREQUEST':
      return Object.assign({}, state, { obj: {} });
    case 'BUYSUCCESS':
      return Object.assign({}, state, { obj: action.data });
    case 'BUYERROR':
      return Object.assign({}, state, { obj: {} });
    default:
      return state;
  }
};

export const orderInfo = (state = {
  obj: {},
}, action) => {
  switch (action.type) {
    case 'ORDERREQUEST':
      return Object.assign({}, state, { obj: {} });
    case 'ORDERSUCCESS':
      return Object.assign({}, state, { obj: action.data });
    case 'ORDERERROR':
      return Object.assign({}, state, { obj: {} });
    default:
      return state;
  }
};
