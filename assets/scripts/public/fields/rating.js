import el from '../utils/elements'
//import uiux from '../core/uiux'

export default {
  init() {
    this.build();
  },
  build() {
    $(el("field", true, "--rating :radio + label")).click(function(){
      $(this).prev().focus();
    });
  },
}