import { el } from '../core/helpers'
import submit from '../core/submit'
import validate from '../core/validate'
const { __ } = wp.i18n

export default {
  init() {
    this.build();
    this.dragndrop();
    this.remove();
  },
  build() {
    let upload = this
    $(el("field", true, "--upload label")).click(function(){
      $(this).closest(el("field")).removeClass(el("field_error", false))
    })
    $(el("field", true, "--upload :input")).change(function () {
      let errors = [];
      const $input = $(this)

      const $wrap = $input.closest(el("field"))
      $wrap.removeClass(el("field", false, "--uploaded"));

      if(validate.validateField($wrap[0])) {
        const file = this.files.length ? this.files[0] : false;
        let $fileinfo = $wrap.find('.formality__upload__info')
        $fileinfo.html(`<i></i><span><strong>${ __('Checking file', 'formality') }</strong>${ __('Please wait', 'formality') }</span>`)
        var reader = new FileReader()
        const previewFormats = ["jpeg", "jpg", "png", "gif", "svg"]
        const maxSize = parseInt($input.attr('data-max-size'))
        reader.fileName = file.name
        reader.fileSize = file.size
        reader.fileFormat = file.name.split('.').pop().toLowerCase();
        reader.onload = function(e) {
          $fileinfo.html(`<i${ previewFormats.indexOf(e.target.fileFormat) !== -1 ? ' style="background-image:url('+e.target.result+')"' : '' }></i><span><strong>${e.target.fileName}</strong>${formatBytes(e.target.fileSize)}</span><a class="formality__upload__remove" href="#"></a>`)
          submit.token(upload, $input)
        }
        reader.readAsDataURL(file);
      } else {
        $input.val('');
        $wrap.removeClass(el("field_filled", false));
      }

      $(el("form")).removeClass(el("form", false, "--dragging"))
      $(el("field", true, "--dragging")).removeClass(el("field", false, "--dragging"))
      $input.focus()
    }).on("blur", function(){
      $(this).closest(el("field")).removeClass(el("field_error", false));
    });
    function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}
    //force focus on label click
    $(el("field", true, "--upload .formality__upload")).click(function(e){ $(this).prev().focus(); })
  },
  remove() {
    $(el("field", true, "--upload")).on("click", ".formality__upload__remove", function(e){
      e.preventDefault();
      $(this).closest(el("field")).find(':input').val("").trigger("change")
    })
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
    const oldfile = $input.attr("data-file");
    fulldata.append("action", "formality_upload")
    fulldata.append("nonce", window.formality.action_nonce)
    fulldata.append("token", token)
    fulldata.append("id", $(el("form", "uid")).attr("data-id"))
    fulldata.append("field", $input.prop("id"))
    fulldata.append("field_" + $input.prop("id"), $input[0].files[0])
    if(oldfile) { fulldata.append("old", oldfile) }
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
          debug: ('responseText' in error) ? error.responseText : error,
          error: 'Internal server error',
          field: $input.prop("id")
        }
        upload.result(data)
      },
    })
  },
  result(data){
    const $input = $('#'+ data.field)
    const $wrap = $input.closest(el("field"))
    if(data.status == 200 && data.field){
      $input.attr("data-file", data.file)
      $wrap.addClass(el("field", false, "--uploaded"));
    } else {
      $input.val('');
      $wrap.removeClass(el("field_filled", false)).addClass(el("field_error", false));
      $wrap.find('.formality__input__status').html(`<div class="formality__input__errors">${ data.error }</ul>`)
    }
  },
}
