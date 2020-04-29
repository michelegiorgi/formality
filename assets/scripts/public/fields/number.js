import el from '../utils/elements'
//import uiux from '../core/uiux'

export default {
  init() {
    this.build();
  },
  build() {
    $(el("field", true, "--number input")).each(function(){
      $('<div class="formality__input__spinner"><a href="#"></a><a href="#"></a></div>').insertAfter(this);
      $(this).on("keypress", function (e) {
        if((e.which < 48 || e.which > 57) && e.which!==44 && e.which!==46 ) {
          e.preventDefault();
        }
      });  
    });
    $(el("field", true, "--number")).on("click", ".formality__input__spinner a", function(e){
      e.preventDefault();
      const input = $(this).closest(el("field")).find("input")[0];
      if(!input.value) {
        $(input).val(0)
      } else {
        if($(this).is(':first-child')) {
          input.stepUp();
        } else {
          input.stepDown();
        }
      }
      $(input).trigger("change")
      input.focus();
    })
  },
}