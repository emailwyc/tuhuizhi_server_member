export const myTicketInfo = (state = {
  obj: {},
}, action) => {
  switch (action.type) {
    case 'INFOREQUEST':
      return Object.assign({}, state, { obj: {} });
    case 'INFOSUCCESS':
      return Object.assign({}, state, { obj: action.data });
    case 'INFOERROR':
      return Object.assign({}, state, { obj: {} });
    default:
      return state;
  }
};
