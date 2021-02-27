import { el } from '../core/helpers'
import submit from '../core/submit'

export default {
  init() {
    this.build();
    this.dragndrop();
  },
  build() {
    let upload = this
    $(el("field", true, "--upload :input")).change(function () {
      const $input = $(this)
      const $wrap = $input.closest(el("field"))
      if(this.files && this.files[0]) {
        submit.token(upload, $input)
        var reader = new FileReader()
        const previewFormats = ["jpeg", "jpg", "png", "gif", "svg"]
        const maxSize = parseInt($input.attr('data-max-size'))
        reader.fileName = this.files[0].name
        reader.fileSize = this.files[0].size
        reader.fileFormat = this.files[0].name.split('.').pop().toLowerCase();
        reader.onload = function (e) {
          $wrap.find('.formality__upload__info').html(`<i${ previewFormats.indexOf(e.target.fileFormat) !== -1 ? ' style="background-image:url('+e.target.result+')"' : '' }></i><span><strong>${e.target.fileName}</strong>${formatBytes(e.target.fileSize)}</span>`)
        }
        reader.readAsDataURL(this.files[0]);
      } else {
        //$().removeClass("filled");
      }
      $(el("form")).removeClass(el("form", false, "--dragging"))
      $(el("field", true, "--dragging")).removeClass(el("field", false, "--dragging"))
      $input.focus()
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
        window.clearTimeout(drag_timer);
        drag_timer = window.setTimeout(function(){
          $(el("form")).removeClass(el("form", false, "--dragging"));
        }, 200);
      }
    });
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
    console.log(data);
  },
}
