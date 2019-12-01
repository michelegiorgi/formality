//import el from '../utils/elements'

export default {
  init() {
    $('.formality__cta').each(function(){
      const formlink = $(this).attr('href')
      const formid = $(this).attr('id')
      if(!$('.formality__sidebar[data-sidebar='+formid+']').length) {
        $('body').append('<div class="formality__sidebar" data-sidebar="'+formid+'"><div class="formality__sidebar__iframe"><iframe src="'+formlink+'"></iframe></div></div>')
      }
    })
    $('.formality__sidebar').addClass('formality__sidebar--loaded')
    this.openSidebar()
  },
  openSidebar() {
    $('.formality__cta').click(function(e){
      e.preventDefault()
      const formid = $(this).attr('id')
      $('.formality__sidebar[data-sidebar='+formid+']').toggleClass('formality__sidebar--open')
    })
  },
}