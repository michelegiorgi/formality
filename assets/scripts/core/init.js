import loader from './loader'
import uiux from './uiux'
import nav from './nav'
import validate from './validate'
import submit from './submit'
import conditional from './conditional'
import embed from './embed'
import hints from './hints'

import select from './fields/select'
import switch1 from './fields/switch'
import textarea from './fields/textarea'
import number from './fields/number'
import rating from './fields/rating'

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
	switch1.init()
	textarea.init()
	number.init()
	rating.init()
}