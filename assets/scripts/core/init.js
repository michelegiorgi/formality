import ui from './ui'
import nav from './nav'
import validate from './validate'
import submit from './submit'

export default function() {
	ui.focus()
	ui.placeholder()
	ui.filled()
	
	nav.build();
	nav.navigation();
	nav.legend();
	
	validate.init();
	
	submit.init();
}