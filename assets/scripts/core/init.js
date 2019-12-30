import loader from './loader'
import uiux from './uiux'
import nav from './nav'
import validate from './validate'
import submit from './submit'
import conditional from './conditional'
import embed from './embed'
import hints from './hints'

import select from './fields/select'
import checkbox from './fields/checkbox'
import textarea from './fields/textarea'
import number from './fields/number'


export default function() {
	loader.init()
	uiux.init()
	submit.init()
	nav.init()	
	validate.init()
	conditional.init()
	embed.init()
	hints.init()
	
	select.init()
	checkbox.init()
	textarea.init()
	number.init()
}