import el from '../utils/elements'
import 'parsleyjs'

export default {
	init() {
		this.build();
	},
	build() {
		const $steps = $(el("section"));
		if($steps.length > 1) {
			let stepn = 0;
			$steps.each(function(){
				stepn++;
				const $head = $(this).find(el("section_header"));
				const $required = $(this).find(el("field_required"));
				let legend = "";
				for (let i = 0; i < $required.length; i++) {
					const inputname = $required.eq(i).find(":input").attr("name");
					legend += '<li data-name="'+inputname+'"></li>';
				}
				$(el("nav_list")).append('<li><a href="#" data-step="'+stepn+'"><div>' + $head.html() + '</div></a><ul>'+legend+'</ul></li>')
			})
		}
	},
	navigate(index) {
		const $steps = $(el("section"));
		const atTheEnd = index >= $steps.length - 1;
		$steps.removeClass('current').eq(index).addClass('current');
    $('.form-navigation .previous').toggle(index > 0);
    $('.form-navigation .next').toggle(!atTheEnd);
    $('.form-navigation [type=submit]').toggle(atTheEnd);
	},
	current() {
    const $steps = $(el("section"));
    return $steps.index($steps.filter('.current'));
  },
  
};