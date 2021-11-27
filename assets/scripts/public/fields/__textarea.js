import { el } from '../core/helpers'
//import uiux from '../core/uiux'

export default {
  init() {
    this.build()
    this.autoHeight()
  },
  build() {
    $(el("field", true, "--textarea textarea")).each(function() {
      let savedValue = this.value;
      this.value = '';
      this.baseScrollHeight = this.scrollHeight;
      const elLineHeight = $(this).css( "line-height");
      this.elLineHeight = parseInt(elLineHeight.replace("px", ""));
      this.value = savedValue;
      $(this).attr('data-min-rows', this.rows)
      let $textarea = $(this);
      let max = parseInt($textarea.attr("maxlength"))
      $('<div class="formality__textarea__counter">' + $textarea.val().length + ' / ' + max + '</div>').insertBefore(this);
      $textarea.keyup(function(){
        $textarea.prev().text($textarea.val().length + ' / ' + max);
      });
    })
  },
  autoHeight() {
    $(document).on('input', el("field", true, "--textarea textarea"), function(){
      let minRows = this.getAttribute('data-min-rows')|0, rows;
      this.rows = minRows;
      rows = Math.ceil((this.scrollHeight - this.baseScrollHeight) / this.elLineHeight);
      this.rows = minRows + rows;
    });
  },
}