export const myTicketList = (state = {
  list: [],
  info: 0,
}, action) => {
  switch (action.type) {
    case 'MYCKETREQUEST':
      return Object.assign({}, state, { list: [] });
    case 'MYCKETSUCCESS':
      return Object.assign({}, state, { list: action.data.data, info: action.data.code });
    case 'MYCKETERROR':
      return Object.assign({}, state, { list: [] });
    default:
      return state;
  }
};
