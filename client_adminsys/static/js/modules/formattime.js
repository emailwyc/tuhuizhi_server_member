module.exports = (date) => {
  const year = date.getFullYear();
  let month = date.getMonth() + 1;
  month = month < 10 ? `0${month}` : month;
  let day = date.getDate();
  day = day < 10 ? `0${day}` : day;
  const hour = date.getHours();
  let min = date.getMinutes();
  min = min < 10 ? `0${min}` : min;
  return `${year}-${month}-${day} ${hour}:${min}`;
};
