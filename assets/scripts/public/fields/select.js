import el from '../utils/elements'
import uiux from '../core/uiux'

export default {
  init() {
    this.build();
    this.keyboard();
  },
  build() {
    const select = this;
    $(el("field", true, "--select")).each(function(){
      $(this).addClass(el("field", false, "--select-js"));
      //$(this).addClass(el("field", false, "--open"));
      let $input = $(this).children(el("input",true));
      let $select = $input.children("select");
      let $options = $select.children("option:not([disabled])");
      let options = ""
      $options.each(function(){
        const selected = $(this)[0].hasAttribute("selected") ? ' class="selected"': '';
        options += '<li data-value="'+$(this).attr("value")+'"'+selected+'>'+$(this).text()+'</li>'
      })
      $('<div class="formality__select__fake"></div>').insertBefore($select);
      const optionsclass = $options.length < 6 ? ' options--' + $options.length : '';
      $input.append('<div class="formality__select__list'+optionsclass+'"><ul>'+options+'</ul></div>');
      //$(this).height($(this).outerHeight());
      $select.on('focus', function(){
        $(this).closest(el("field", true, "--select")).addClass(el("field", false, "--open"));
      }).on('blur', function(){
        $(this).closest(el("field", true, "--select")).removeClass(el("field", false, "--open"));
      })
    });
    $("body").on("mousedown touchstart", ".formality__select__fake", function(e) {
      e.preventDefault();
      //e.stopPropagation();
      const $field = $(this).closest(el("field", true, "--select"));
      if($(this).closest(el("field")).hasClass(el("field", false, "--open"))) {
        $field.removeClass(el("field", false, "--open"));
      } else {
        $(this).next("select").focus();
        $field.addClass(el("field", false, "--open"));
      }
    })
    $('body').on('click', '.formality__select__list li', function(e){
      e.preventDefault();
      select.change($(this), true)
    });
  },
  keyboard() {
    const select = this;
    $(el("field", true, "--select select")).keydown(function(e){
      e.preventDefault();
      let $options = $(this).parent().find('.formality__select__list li');
      let $focused = $options.filter(".focus")
      if(e.which == 40) {  
        select.move($focused, "next", $options)
      } else if (e.which == 38) {
        select.move($focused, "prev", $options)
      } else if (e.which == 13) {
        if($focused.length) {
          select.change($focused)
          uiux.move($(this).closest(el("field")), "next", e);
        }
      } else if (e.which == 8) {
        uiux.move($(this).closest(el("field")), "prev", e);
      }
    })
  },
  move($focused, direction = "next", $options) {
    if($focused.length) {
      let $nextprev
      if(direction == "next") {
        $nextprev = $focused.next()
      } else {
        $nextprev = $focused.prev()
      }
      if($nextprev.length) {
        $focused.removeClass("focus")
        $focused = $nextprev.addClass("focus")
      }
    } else {
      $focused = $options.first().addClass("focus")
    }
    const $optionslist = $focused.closest("ul");
    const scrollpx = parseInt(Math.max(0, ($focused.position().top + $optionslist.scrollTop() - ($optionslist.height()/2) + ($focused.height()/2)) ));
    $optionslist.stop().animate({ scrollTop: scrollpx }, 100)
  },
  change($selected, focus = false) {
    $('.formality__select__list li').removeClass("selected").removeClass("focus");
    $selected.addClass("selected").addClass("focus");
    let $field = $selected.closest(el("field", true, "--select"));
    const value = $selected.attr("data-value");
    let $select = $field.find("select")
    $select.val(value).trigger("change");
    if(focus) {
      $select.focus();
      $field.removeClass(el("field", false, "--open"));
    }
  },
}