// Formality public script
import 'jquery';

//core functions
import loader from './public/core/loader'
import uiux from './public/core/uiux'
import nav from './public/core/nav'
import validate from './public/core/validate'
import submit from './public/core/submit'
import conditional from './public/core/conditional'
import embed from './public/core/embed'
import hints from './public/core/hints'
import hooks from './public/core/hooks'

//fields functions
import select from './public/fields/select'
import switch1 from './public/fields/switch'
import textarea from './public/fields/textarea'
import number from './public/fields/number'
import rating from './public/fields/rating'
import multiple from './public/fields/multiple'
import media from './public/fields/media'
import upload from './public/fields/upload'

jQuery(document).ready(() => {
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
  upload.init()
});
