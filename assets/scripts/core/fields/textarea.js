import el from '../../utils/elements'
//import uiux from '../uiux'

export default {
  init() {
    this.build()
  },
  build() {
    $(el("field", true, "--textarea textarea")).each(function() {
      let $textarea = $(this);
      let max = parseInt($textarea.attr("maxlength"))
      $('<div class="formality__textarea__counter">' + $textarea.val().length + ' / ' + max + '</div>').insertBefore(this);
      $textarea.keyup(function(){
        $textarea.prev().text($textarea.val().length + ' / ' + max);
      });
    })
  },
}