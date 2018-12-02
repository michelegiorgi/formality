import ui from './ui'
import nav from './nav'
import validate from './validate'
import submit from './submit'
import conversational from './conversational'

export default function() {
	ui.focus()
	ui.placeholder()
	ui.filled()
	
	nav.build();
	nav.navigation();
	nav.legend();
	
	validate.init();
	
	submit.init();
	
	conversational.init();
}