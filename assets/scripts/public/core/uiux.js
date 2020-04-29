import el from '../utils/elements'
import hints from './hints'

export default {
  init() {
    this.focus()
    this.placeholder()
    this.filled()
    this.keyboard()
  },
  focus() {
    //toggle focus class on input wrap 
    $(el("field", true, " :input")).on("focus", function() {
      const $parentEl = $(this).closest(el("field"))
      $parentEl.addClass(el("field_focus", false))
      hints.show($parentEl)
    }).on("blur", function() {
      $(el("field_focus")).removeClass(el("field_focus", false))
      hints.clear()
    })
    //autofocus first input
    setTimeout(function(){ $(el("section") + ":first-child " + el("field") + ":first").find(":input").first().focus() }, 1000)
    window.onfocus = function(){
      if ( window.location !== window.parent.location ) {
        $(el("field", true, " :input[required]")).filter(function(){ return !this.value; }).first().focus();
      }
    }
    //click outside form
    $(document).mouseup(function (e) {
      if (!$(el("form")).is(e.target) && $(el("form")).has(e.target).length === 0) {
        $(el("field_focus")).removeClass(el("field_focus", false))
        hints.clear()
      }
    })
  },
  placeholder() {
    //placeholder as input wrap attribute
    $(el("input", true)).each(function(){
      const placeholder = $(this).find(":input[placeholder]").attr("placeholder")
      $(this).append('<div class="' + el("input", false, "__status") + '"' + ( placeholder ? ' data-placeholder="' + placeholder + '"' : '' ) + '></div>')
    })
  },
  filled() {
    //toggle filled class to input wrap
    $(el("field", true, " :input")).on("change", function() { fillToggle(this) })
    function fillToggle(field) {
      let val = $(field).val()
      if($(field).is(":checkbox")){
        val = $(field).is(":checked");
      }
      const name = $(field).attr("name")
      if(val) {
        $(field).closest(el("field")).addClass(el("field_filled", false))
        $(el("nav_list", true, ' li[data-name="'+name+'"]')).addClass("active")
      } else {
        $(field).closest(el("field")).removeClass(el("field_filled", false))
        $(el("nav_list", true, ' li[data-name="'+name+'"]')).removeClass("active")
      }
    }   
  },
  keyboard() {
    //previous field focus
    const uiux = this 
    $(el("field", true, " :input")).on("keydown", function(e) {
      const $this = $(this)
      const validprev = (!$this.val()) || $this.is(':checkbox') || $this.is(':radio') ? true : false
      if(validprev && (e.keyCode == 8)) {
        uiux.move($this, "prev", e)
      } else if(e.keyCode == 13) {
        if(!$this.is("textarea")) {
          uiux.move($this, "next", e)
        }
      } else if( e.which == 9 ) {
        uiux.move($this, "next", e)
      }
    })
  },
  move($field, direction = "next", e) {
    const conversational = $field.closest(el("form", true, "--conversational")).length
    let $element = ""
    const $fieldwrap = $field.closest(el("field"))
    if(direction=="next") {
      $element = $fieldwrap.next(el("field", true, ":visible"))
      if(!$element.length) {
        $element = $fieldwrap.nextUntil(el("field", true, ":visible")).last().next()
      }
    } else if(direction=="prev") {
      $element = $fieldwrap.prev(el("field", true, ":visible"))
      if(!$element.length) {
        $element = $fieldwrap.prevUntil(el("field", true, ":visible")).last().prev()
      }
    } else if(direction=="first") {
      $element = $field.next(el("field", true, ":visible"))
      if(!$element.length) {
        $element = $field.nextUntil(el("field", true, ":visible")).last().next()
      }
    } else {
      $element = $field
    }
    if($element.length) {
      if(conversational) {
        let offset = 0;
        if($("body").hasClass("body-formality")) {
          offset = $(window).height()/3;
          if($fieldwrap.hasClass(el("field", false, "--select")) && direction=="next") {
            $field.blur()
            const selectheight = $fieldwrap.find(".formality__select__list").height()
            offset = offset + selectheight
          }
          $('html, body').stop().animate({ scrollTop: ($element.offset().top - offset) }, 300)
        } else {
          const $main = $(".formality__main");
          offset = $main.height()/3
          if(!$element.hasClass(el("field", false))) { $element = $fieldwrap }
          $main.stop().animate({ scrollTop: (($main.scrollTop() + $element.position().top) - offset) }, 300)
        }
      } else {
        $element.find(":input" + (direction=="prev" ? ":last" : ":first")).focus()
      }
      e.preventDefault()
    } else {
      if(($fieldwrap.is(':first-child')||$fieldwrap.is(':nth-child(2)')) && direction == "prev") {
        if($(el("button", "uid", "--prev")).is(":visible")) {
          e.preventDefault()
          $(el("button", "uid", "--prev")).click()
        }
      } else if($fieldwrap.is(':last-child') && direction == "next") {
        e.preventDefault()
        if($(el("button", "uid", "--next")).is(":visible")) {
          $(el("button", "uid", "--next")).click()
        } else {
          $(el("form", "uid")).submit()
        }       
      } else {
        //
      }
    }
  },
}