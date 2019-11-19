import el from '../../utils/elements'
import uiux from '../uiux'

export default {
  init() {
    this.build();
    this.keyboard();
    this.change();
  },
  build() {
    $(el("field", true, "--select")).each(function(){
      $(this).addClass(el("field", false, "--select-js"));
      let $input = $(this).children(el("input",true));
      let $select = $input.children("select");
      let $options = $select.children("option:not([disabled])");
      let options = ""
      $options.each(function(){
        const selected = $(this)[0].hasAttribute("selected") ? ' class="selected"': '';
        options += '<li data-value="'+$(this).attr("value")+'"'+selected+'>'+$(this).text()+'</li>'
      })
      $('<div class="formality__select__fake" style="height:'+$select.outerHeight()+'px"></div>').insertBefore($select);
      const optionsclass = $options.length < 6 ? ' options--' + $options.length : '';
      $input.append('<div class="formality__select__list'+optionsclass+'"><ul>'+options+'</ul></div>');
      //$(this).height($(this).outerHeight());
    });
    $("body").on("mousedown touchstart", ".formality__select__fake", function(e) {
      e.preventDefault();
      e.stopPropagation();
      if($(this).closest(el("field")).hasClass(el("field_focus", false))) {
        $(this).next("select").blur();
      } else {
        $(this).next("select").focus();
      }
    })
  },
  keyboard() {
    let select = this;
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
          $focused.trigger("click");
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
  change() {
    $('body').on('click', '.formality__select__list li', function(e){
      e.preventDefault();
      $('.formality__select__list li').removeClass("selected").removeClass("focus");
      $(this).addClass("selected").addClass("focus");
      let $field = $(this).closest(el("field", true, "--select"));
      const value = $(this).attr("data-value");
      $field.find("select").val(value).trigger("change");
    });
  },
}