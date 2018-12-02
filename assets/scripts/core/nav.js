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
			} else {
				if($(el("form", "uid")).hasClass(el("form", false, "--conversational"))) {
					let section = 0;
					let liststring = "";
					liststring = liststring + '<li class="' + el("nav_anchor", false)+'"><ul>';
					$(el("section", "uid", "--active > *")).each(function(){
						section++;
						let id = "field_" + uid(false, false) + "_" + section;
						let label = "";
						$(this).attr("id", id);
						if($(this).hasClass(el("field", false))) {
							label = $(this).find(el("label")).text();
							liststring = liststring + '<li><a href="#' + id + '">' + label + '</a></li>';
						} else {
							label = $(this).find("h4").text();
							liststring = liststring + '</ul></li><li class="' + el("nav_anchor", false)+'"><a href="#' + id + '">' + label + '</a><ul>';
						}
					})
					$(el("nav_list", "uid")).append(liststring + '</ul></li>');
					nav.conversational();
				}
				$(el("button", "uid", "--prev")).toggle(false);
				$(el("button", "uid", "--next")).toggle(false);
				validate.form()
			}
		})
	},
	navigation() {
		//gotostep function
		$(el("nav_section", true, " a[data-step]")).click(function(e){
			const index = $(this).attr("data-step");
			uid($(this));
			e.preventDefault();
			goto(index);
		})
		$(el("button", true, "--next")).click(function(e){
			e.preventDefault();
			uid($(this));
			goto(current()+1);
		});
		$(el("button", true, "--prev")).click(function(e){
			uid($(this));
			e.preventDefault();
			goto(current()-1);
		})
		function goto(index) {
			if(validate.checkstep(current(), index)) {
				const $steps = $(el("section", "uid"));
				const $nav = $(el("nav_section", "uid"));
				const atTheEnd = index >= $steps.length - 1;
				anim(index);
				$steps.removeClass(el("section", false, "--active")).eq(index).addClass(el("section", false, "--active"));
				$nav.removeClass(el("nav_section", false, "--active")).eq(index).addClass(el("nav_section", false, "--active"));
				setTimeout(function() {	$(el("section", "uid", "--active") + " " + el("field", "uid") + ":first").click(); }, 400);
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
			$(el("section", "uid") + " " + el("field") + " :input[name="+name+"]").click();
		})		
	},
	conversational() {
		inView.offset($(window).height()/2);
		inView(el("field", "uid")).on('enter', element => {
      const sectionid = $(element).attr("id");
      const $navlink = $(el("nav_list", "uid", ' a[href="#'+sectionid+'"]'));
      $(el("nav_list", "uid", " a")).removeClass("active");
      $navlink.addClass("active");
      $navlink.closest(el("nav_anchor")).find("> a").addClass("active");
      if(!$(element).hasClass("formality__field--focus")) {
				$(element).find("input, textarea").focus();
			}
    });
	},
};