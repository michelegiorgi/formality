import el from '../utils/elements'

export default {
  focus() {
    //toggle focus class on input wrap 
    $(el("field", true, " :input")).on("focus", function() {
      $(el("field_focus")).removeClass(el("field_focus", false))
      $(this).closest(el("field")).addClass(el("field_focus", false))
    })
    //autofocus first input
    setTimeout(function(){ $(el("section") + ":first-child " + el("field") + ":first").find(":input").focus() }, 1000);
    //click outside form
    $(document).mouseup(function (e) {
      if (!$(el("form")).is(e.target) && $(el("form")).has(e.target).length === 0) {
        $(el("field_focus")).removeClass(el("field_focus", false))
      }
    });
  },
  placeholder() {
    //placeholder as input wrap attribute
    $(el("input") + " :input[placeholder]").each(function(){
      let placeholder = $(this).attr("placeholder")
      $(this).closest(el("input")).attr("data-placeholder", placeholder)
    })
  },
  filled() {
    //toggle filled class to input wrap
    $(el("field", true, " :input")).on("change", function() { fillToggle(this) })
    function fillToggle(field) {
      const val = $(field).val()
      const name = $(field).attr("name")
      if(val) {
        $(field).closest(el("field")).addClass(el("field_filled", false))
        $(el("nav_list", true, ' li[data-name='+name+']')).addClass("active")
      } else {
        $(field).closest(el("field")).removeClass(el("field_filled", false))
        $(el("nav_list", true, ' li[data-name='+name+']')).removeClass("active")
      }
    }   
  },
};