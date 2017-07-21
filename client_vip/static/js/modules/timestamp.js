import { zerofill } from './zerofill';
export const getDates = time => {
  const date = new Date(time * 1000);
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const hour = date.getHours();
  const minu = date.getMinutes();
  const sec = date.getSeconds();
  return `${year}/${zerofill(month)}/${
    zerofill(day)} ${zerofill(hour)}:${zerofill(minu)}:${zerofill(sec)}`;
};

// 针对html input text="data" 得时间戳
export const getDatesInput = time => {
  const date = new Date(time * 1000);
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  return `${year}-${zerofill(month)}-${zerofill(day)}`;
};
