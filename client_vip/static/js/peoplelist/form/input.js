export default (v) => `<div class="form-group row">
    <label for="inputEmail3" class="col-sm-2 form-control-label">
    ${v.content} ${v.required ? '*' : ''}
    </label>
    <div class="col-sm-10">
      <input type="${v.fromtype === 'input' ? 'text' : 'date'}" class="form-control ${v.type}"
       name="${v.type}" placeholder="${v.placeholder}">
    </div>
  </div>
  `;
