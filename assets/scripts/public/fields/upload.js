import { el } from '../core/helpers'
import submit from '../core/submit'

export default {
  init() {
    this.build();
    this.dragndrop();
  },
  build() {
    let upload = this
    $(el("field", true, "--upload label")).click(function(){
      $(this).closest(el("field")).removeClass(el("field_error", false))
    })
    $(el("field", true, "--upload :input")).change(function () {
      let errors = [];
      const $input = $(this)
      const file = this.files.length ? this.files[0] : false;
      const $wrap = $input.closest(el("field"))
      if(file && file.type !== '' && file.size > 0) {
        const name = file.name
        const size = file.size
        const formats = $input.attr('accept').split(", ");
        const extension = '.' + name.split('.').pop();
        const max = parseInt($input.attr('data-max-size'));

        if(formats.indexOf(extension) == -1) { errors.push('invalid extension') }
        if(size > max) { errors.push('file size limit') }

        if(!errors.length) {
          let $fileinfo = $wrap.find('.formality__upload__info')
          $fileinfo.html(`<i></i><span><strong>Checking file</strong>Please wait</span>`)
          var reader = new FileReader()
          const previewFormats = ["jpeg", "jpg", "png", "gif", "svg"]
          const maxSize = parseInt($input.attr('data-max-size'))
          reader.fileName = name
          reader.fileSize = size
          reader.fileFormat = name.split('.').pop().toLowerCase();
          reader.onload = function(e) {
            $fileinfo.html(`<i${ previewFormats.indexOf(e.target.fileFormat) !== -1 ? ' style="background-image:url('+e.target.result+')"' : '' }></i><span><strong>${e.target.fileName}</strong>${formatBytes(e.target.fileSize)}</span>`)
            submit.token(upload, $input)
          }
          reader.readAsDataURL(file);
        }
      } else {
        errors.push('invalid file')
      }
      if(errors.length) {
        $wrap.val('');
        $wrap.removeClass(el("field_filled", false)).addClass(el("field_error", false));
        $wrap.find('.formality__input__status').html(`<ul class="formality__input__errors filled"><li>${errors[0]}</li></ul>`)
      }
      $(el("form")).removeClass(el("form", false, "--dragging"))
      $(el("field", true, "--dragging")).removeClass(el("field", false, "--dragging"))
      $input.focus()
    }).on("blur", function(){
      $(this).closest(el("field")).removeClass(el("field_error", false));
    });
    function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}
    //force focus on label click
    $(el("field", true, "--upload .formality__upload")).click(function(e){ $(this).prev().focus(); })
  },
  dragndrop() {
    let drag_timer;
    //drag file in viewport
    $(document).on('dragover', function(e){
      let dt = e.originalEvent.dataTransfer;
      if(dt.types && (dt.types.indexOf ? dt.types.indexOf('Files') != -1 : dt.types.contains('Files'))){
        $(el("form")).addClass(el("form", false, "--dragging"));
        $(el("field", true, "--upload") + el("field_error")).removeClass(el("field_error", false))
        window.clearTimeout(drag_timer);
        drag_timer = window.setTimeout(function(){
          $(el("form")).removeClass(el("form", false, "--dragging"));
        }, 200);
      }
    })
    //drag file in upload field
    $(el("field", true, "--upload :input")).on('dragenter', function(){
      const $input = $(this)
      const $wrap = $input.closest(el("field"))
      $wrap.addClass(el("field", false, "--dragging"));
      $input.focus();
    }).on('dragleave', function(){
      const $wrap = $(this).closest(el("field"))
      $wrap.removeClass(el("field", false, "--dragging"));
      $(this).blur();
    });
  },
  send(token, $input) {
    //send form
    let upload = this
    let fulldata = new FormData()
    fulldata.append("action", "formality_upload")
    fulldata.append("nonce", window.formality.action_nonce)
    fulldata.append("token", token)
    fulldata.append("id", $(el("form", "uid")).attr("data-id"))
    fulldata.append("field_" + $input.prop("id"), $input[0].files[0])
    $.ajax({
      url: window.formality.api + 'formality/v1/upload/',
      data: fulldata,
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', window.formality.login_nonce ) },
      type: 'POST',
      success: function(data){
        upload.result(data)
      },
      error: function(error){
        const data = {
          status: 400,
          error: ('responseText' in error) ? error.responseText : error
        }
        upload.result(data)
      },
    })
  },
  result(data){
    console.log(data)
    if(data.status == 200 && data.field){
      const $input = $('#'+ data.field)
      const $wrap = $input.closest(el("field"))
      $wrap.addClass(el("field", false, "--uploaded"));
    } else {

    }
  },
}
