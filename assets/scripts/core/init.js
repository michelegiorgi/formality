import uiux from './uiux'
import nav from './nav'
import validate from './validate'
import submit from './submit'

export default function() {
	uiux.init()
	submit.init()
	nav.init()	
	validate.init()
}