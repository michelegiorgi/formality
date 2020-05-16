//core functions
import loader from './core/loader'
import uiux from './core/uiux'
import nav from './core/nav'
import validate from './core/validate'
import submit from './core/submit'
import conditional from './core/conditional'
import embed from './core/embed'
import hints from './core/hints'

//fields functions
import select from './fields/select'
import switch1 from './fields/switch'
import textarea from './fields/textarea'
import number from './fields/number'
import rating from './fields/rating'
import multiple from './fields/multiple'
import media from './fields/media'

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
  multiple.init()
  media.init()
}