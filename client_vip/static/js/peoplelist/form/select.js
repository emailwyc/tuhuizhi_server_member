export default (v, p, select) =>
  `<div class="form-group row">
     <label for="inputEmail3"
     class="col-sm-2 form-control-label">${v.content} ${p.required ? '*' : ''}</label>
     <div class="col-sm-10">
     ${select}
     </div>
  </div>`;
