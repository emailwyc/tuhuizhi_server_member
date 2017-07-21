export const pay = (state = {
  info: {},
}, action) => {
  switch (action.type) {
    case 'PAYREQUEST':
      return Object.assign({}, state, {});
    case 'PAYSUCCESS':
      return Object.assign({}, state, { info: action.data });
    case 'PAYERROR':
      return Object.assign({}, state, {});
    default:
      return state;
  }
};
