import uiux from './uiux'
import nav from './nav'
import validate from './validate'
import submit from './submit'
import conditional from './conditional'
import select from './fields/select'
import checkbox from './fields/checkbox'
import textarea from './fields/textarea'
import number from './fields/number'


export default function() {
	uiux.init()
	submit.init()
	nav.init()	
	validate.init()
	conditional.init()
	select.init()
	checkbox.init()
	textarea.init()
	number.init()
}