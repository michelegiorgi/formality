import el from '../utils/elements'
import uid from '../utils/uid'
import validate from './validate'
import inView from 'in-view'

export default {
	build() {
		//build navigation list
		//build required fields legend
		let nav = this;
		$(el("form")).each(function() {
			uid($(this));
			const $steps = $(el("section", "uid"));
			if($steps.length > 1) {
				let stepn = 0;
				$(el("button", "uid", "--prev")).toggle(false);
				$(el("submit", "uid")).toggle(false);
				$steps.each(function(){
					const $head = $(this).find(el("section_header"));
					const $required = $(this).find(el("field_required"));
					let legend = "";
					for (let i = 0; i < $required.length; i++) {
						const inputname = $required.eq(i).find(":input").attr("name");
						legend += '<li data-name="'+inputname+'"></li>';
					}
					legend = '<ul class="'+el("nav_legend", false)+'">'+legend+'</ul>';
					$(el("nav_list", "uid")).append('<li class="' + el("nav_section", false) + (stepn==0 ? " " + el("nav_section", false, "--active"):"") +'"><a href="#" data-step="'+stepn+'"><div>'+$head.html()+'</div></a>'+legend+'</li>')
					stepn++;
				})
				nav.standard();
			} else {
				if($(el("form", "uid")).hasClass(el("form", false, "--conversational"))) {
					let section = 0;
					let liststring = "";
					liststring = liststring + '<li class="' + el("nav_anchor", false)+'"><ul>';
					$(el("section", "uid", "--active > *")).each(function(){
						section++;
						const id = "field_" + uid(false, false) + "_" + section;
						let label = "";
						const fieldid = $(this).find(":input").attr("id");
						$(this).attr("id", id);
						if($(this).hasClass(el("field", false))) {
							label = $(this).find(el("label")).text();
							liststring = liststring + '<li data-name="'+fieldid+'"><a href="#' + id + '">' + label + '</a></li>';
						} else {
							label = $(this).find("h4").text();
							liststring = liststring + '</ul></li><li class="' + el("nav_anchor", false)+'"><a href="#' + id + '">' + label + '</a><ul>';
						}
					})
					$(el("nav_list", "uid")).append(liststring + '</ul></li>');
					$(el("nav", "uid")).append('<div class="formality__nav__buttons"><button type="button" class="formality__btn formality__btn--prev"></button><button type="button" class="formality__btn formality__btn--next"></button></div>');
					nav.conversational();
				} else {
          nav.standard();
          $(el("button", "uid", "--prev")).toggle(false);
          $(el("button", "uid", "--next")).toggle(false);
				}
				validate.form()
			}
		})
	},
	standard() {
		//gotostep function
		$(el("nav_section", true, " a[data-step]")).click(function(e){
			const index = $(this).attr("data-step");
			uid($(this));
			e.preventDefault();
			gotoStep(index);
		})
		$(el("button", true, "--next")).click(function(e){
			e.preventDefault();
			uid($(this));
			gotoStep(current()+1);
		});
		$(el("button", true, "--prev")).click(function(e){
			uid($(this));
			e.preventDefault();
			gotoStep(current()-1);
		})
		function gotoStep(index) {
			if(validate.checkstep(current(), index)) {
				const $steps = $(el("section", "uid"));
				const $nav = $(el("nav_section", "uid"));
				const atTheEnd = index >= $steps.length - 1;
				anim(index);
				$steps.removeClass(el("section", false, "--active")).eq(index).addClass(el("section", false, "--active"));
				$nav.removeClass(el("nav_section", false, "--active")).eq(index).addClass(el("nav_section", false, "--active"));
				setTimeout(function() {	$(el("section", "uid", "--active") + " " + el("field") + ":nth-child(2) :input").focus(); }, 400);
				$(el("button", "uid", "--prev")).toggle(index > 0);
				$(el("button", "uid", "--next")).toggle(!atTheEnd);
				$(el("submit", "uid")).toggle(atTheEnd);
			}
    }
    //step animations
    function anim(index) {
			const animclasses = "moveFromRight moveToRight moveFromLeft moveToLeft";
			$(el("section", "uid", "--active")).removeClass(animclasses).addClass((index > current() ? "moveToLeft" : "moveToRight" ));
			$(el("section", "uid")).eq(index).removeClass(animclasses).addClass((index > current() ? "moveFromRight" : "moveFromLeft" ));
		}
		//get current step
		function current() {
			const $steps = $(el("section", "uid"));
			return $steps.index($steps.filter(el("section", "uid", "--active")));
		}
	},
	legend() {
		//legend click
		$(el("nav_section", true, " li[data-name]")).click(function(e) {
			e.preventDefault();
			uid($(this));
			const name = $(this).attr("data-name");
			$(el("section", "uid") + " " + el("field") + " :input[name="+name+"]").focus();
		})		
	},
  keyboard() {
    //previous field focus
    const nav = this; 
    $(el("field", true, " :input")).on("keydown", function(e) {
      if((!$(this).val()) && (e.keyCode == 8)) {
        nav.goto($(this), "prev", e)
      } else if(e.keyCode == 13) {
        nav.goto($(this), "next", e)
      } else if( e.which == 9 ) {
        nav.goto($(this), "next", e)
      }
    });
  },
  goto($field, direction = "next", e) {
    const conversational = $field.closest(el("form", true, "--conversational")).length;
    let $element = "";
    const $fieldwrap = $field.closest(el("field"));
    if(direction=="next") {
      $element = $fieldwrap.next(el("field"));
      if(!$element.length) {
        $element = $fieldwrap.nextUntil(el("field")).last().next();
      }
    } else if(direction=="prev") {
      $element = $fieldwrap.prev(el("field"));
      if(!$element.length) {
        $element = $fieldwrap.prevUntil(el("field")).last().prev();
      }
    } else if(direction=="first") {
      $element = $field.next(el("field"));
      if(!$element.length) {
        $element = $field.nextUntil(el("field")).last().next();
      }
    } else {
      $element = $field;
    }
    if($element.length) {
      if(conversational) {
        const offset = $(window).height()/3;
        $('html, body').stop().animate({ scrollTop: ($element.offset().top - offset) }, 300);
      } else {
        $element.find(":input").focus()
      }
      e.preventDefault()
    } else {
      if(($fieldwrap.is(':first-child')||$fieldwrap.is(':nth-child(2)')) && direction == "prev") {
        if($(el("button", "uid", "--prev")).is(":visible")) {
          $(el("button", "uid", "--prev")).click()
        }
      } else if($fieldwrap.is(':last-child') && direction == "next") {
        if($(el("button", "uid", "--next")).is(":visible")) {
          $(el("button", "uid", "--next")).click()
        } else {
          $(el("form", "uid")).submit()
        }
        e.preventDefault()
      }
    }
  },
	conversational() {
    const nav = this;
		inView.offset($(window).height()/2);
		inView(el("field", "uid")).on('enter', element => {
      const sectionid = $(element).attr("id");
      const $navlink = $(el("nav_list", "uid", ' a[href="#'+sectionid+'"]'));
      $(el("nav_list", "uid", " a")).removeClass("active");
      $navlink.addClass("active");
      $navlink.closest(el("nav_anchor")).find("> a").addClass("active");
      if(!$(element).hasClass("formality__field--focus")) {
				$(element).find(":input").focus();
			}
    });
    $(window).resize(function() {
      inView.offset($(window).height()/2);
    });
    $(el("button", "uid", "--next")).click(function(e){
			let $element = $(el("field_focus")).find(":input");
			nav.goto($element, "next", e)
		});
		$(el("button", "uid", "--prev")).click(function(e){
			let $element = $(el("field_focus")).find(":input");
			nav.goto($element, "prev", e)
		});
		$(el("nav_anchor", "uid", " a")).click(function(e){
      //e.preventDefault();
      const fieldid = $(this).attr("href");
			let $element = $(fieldid).find(":input");
			if($(this).parent().hasClass(el("nav_anchor", false))) {        
        nav.goto($(fieldid), "first", e)
			} else {
        nav.goto($element, false, e)
      }
		});
	},
};