import { el } from '../core/helpers'
import uiux from '../core/uiux'

export default {
  init() {
    this.build();
    this.keyboard();
  },
  build() {
    $(el("field", true, "--switch :checkbox + label")).click(function(){
      $(this).prev().focus();
    });
  },
  keyboard() {
    $(el("field", true, "--switch :checkbox")).keydown(function(e){
      if ($(this).is(":checked") && e.which == 8) {
        $(this).prop( "checked", false )
      } else if( e.which == 8 ) {
        uiux.move($(this).closest(el("field")), "prev", e)
      }
    })
  },
}