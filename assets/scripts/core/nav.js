import el from '../utils/elements'
import validate from './validate'

export default {
	build() {
		
		//build navigation list
		//build required fields legend
		const $steps = $(el("section"));
		if($steps.length > 1) {
			let stepn = 0;
			$steps.each(function(){
				const $head = $(this).find(el("section_header"));
				const $required = $(this).find(el("field_required"));
				let legend = "";
				for (let i = 0; i < $required.length; i++) {
					const inputname = $required.eq(i).find(":input").attr("name");
					legend += '<li data-name="'+inputname+'"></li>';
				}
				legend = '<ul class="'+el("nav_legend", false)+'">'+legend+'</ul>';
				$(el("nav_list")).append('<li class="' + el("nav_section", false) + (stepn==0 ? " " + el("nav_section", false, "--active"):"") +'"><a href="#" data-step="'+stepn+'"><div>'+$head.html()+'</div></a>'+legend+'</li>')
				stepn++;
			})
		}

	},
	navigation() {
		
		//gotostep function
		$(el("nav_section", true, " a[data-step]")).click(function(e){
			const index = $(this).attr("data-step");
			e.preventDefault();
			goto(index);
		})
		$(el("button", true, "--next")).click(function(e){
			e.preventDefault();
			goto(current()+1);
		});
		$(el("button", true, "--prev")).click(function(e){
			e.preventDefault();
			goto(current()-1);
		})
		function goto(index) {
			if(validate.check(current(), index)) {
				const $steps = $(el("section"));
				const $nav = $(el("nav_section"));
				const atTheEnd = index >= $steps.length - 1;
				anim(index);
				$steps.removeClass(el("section", false, "--active")).eq(index).addClass(el("section", false, "--active"));
				$nav.removeClass(el("nav_section", false, "--active")).eq(index).addClass(el("nav_section", false, "--active"));
				setTimeout(function() {	$(el("section", true, "--active") + " " + el("field") + ":first").click(); }, 400);
				$(el("button", true, "--prev")).toggle(index > 0);
				$(el("button", true, "--next")).toggle(!atTheEnd);
				$(el("submit")).toggle(atTheEnd);
			}
    }
    //step animations
    function anim(index) {
			const animclasses = "moveFromRight moveToRight moveFromLeft moveToLeft"
			$(el("section", true, "--active")).removeClass(animclasses).addClass((index > current() ? "moveToLeft" : "moveToRight" ));
			$(el("section")).eq(index).removeClass(animclasses).addClass((index > current() ? "moveFromRight" : "moveFromLeft" ));
		}
		//get current step
		function current() {
			const $steps = $(el("section"));
			return $steps.index($steps.filter(el("section", true, "--active")));
		}
    
	},
	legend() {
		
		//legend click
		$(el("nav_section", true, " li[data-name]")).click(function(e) {
			e.preventDefault();
			const name = $(this).attr("data-name");
			$(el("section") + " " + el("field") + " :input[name="+name+"]").click();
		})
		
	},
};