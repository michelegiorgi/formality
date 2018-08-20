import el from '../utils/elements'
import 'parsleyjs'

export default {
	init() {
		
	},
	navigate(index) {
		let $steps = $(el("section"));
		let atTheEnd = index >= $steps.length - 1;
		$steps.removeClass('current').eq(index).addClass('current');
    $('.form-navigation .previous').toggle(index > 0);
    $('.form-navigation .next').toggle(!atTheEnd);
    $('.form-navigation [type=submit]').toggle(atTheEnd);
	},
	current() {
    let $steps = $(el("section"));
    return $steps.index($steps.filter('.current'));
  },
  
};