import el from '../../utils/elements'

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
        options += '<li data-value="'+$(this).attr("value")+'">'+$(this).text()+'</li>'
      })
      $input.append('<div class="formality__select__list"><ul>'+options+'</ul></div>'); 
    });
  },
  keyboard() {
    let select = this;
    $(el("field", true, "--select select")).keydown(function(e){
      e.preventDefault();
      let $options = $(this).next('.formality__select__list').find('li');
      let $focused = $options.filter(".focus")
      if(e.which == 40) {  
        select.move($focused, "next", $options)
      } else if (e.which == 38) {
        select.move($focused, "prev", $options)
      } else if (e.which == 13) {
        if($focused.length) {
          $focused.trigger("click")
        }
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
    $('.formality__select__list li').click(function(e){
      e.preventDefault();
      $('.formality__select__list li').removeClass("selected");
      $('.formality__select__list li').removeClass("focus");
      $(this).addClass("selected").addClass("focus");
      let $field = $(this).closest(el("field", true, "--select"));
      const value = $(this).attr("data-value");
      $field.find("select").val(value).change().focus();
    });
  },
}