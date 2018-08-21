import el from '../utils/elements'
import 'parsleyjs'

export default {
	init() {
		this.build();
		this.navigation();
	},
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
		function goto(index) {
			const $steps = $(el("section"));
			const atTheEnd = index >= $steps.length - 1;
			$steps.removeClass(el("section", false, "--active")).eq(index).addClass(el("section", false, "--active"));
			$('.form-navigation .previous').toggle(index > 0);
			$('.form-navigation .next').toggle(!atTheEnd);
			$('.form-navigation [type=submit]').toggle(atTheEnd);
    }
	},
	current() {
    
    //get current step
    const $steps = $(el("section"));
    return $steps.index($steps.filter('.current'));
  },
  
};