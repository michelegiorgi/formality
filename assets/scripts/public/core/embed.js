//import el from '../utils/elements'

export default {
  init() {
    if ( window.location == window.parent.location ) {
      $('.formality__cta').each(function(){
        const formlink = $(this).attr('href')
        const formid = $(this).attr('id')
        if(formlink && formid && (!$('.formality__sidebar[data-sidebar='+formid+']').length)) {
          $('body').append('<div class="formality__sidebar '+formid+'" data-sidebar="'+formid+'"><div class="formality__sidebar__iframe"><iframe src="'+formlink+'"></iframe></div></div>')
        }
      })
      setTimeout(function(){ $('.formality__sidebar').addClass('formality__sidebar--loaded') }, 1000)
      this.openSidebar()
      this.closeSidebar()
    }
  },
  openSidebar() {
    $('.formality__cta, [href^=#formality-open-]').click(function(e){
      e.preventDefault()
      let href = $(this).attr('href')
      let formid = 0
      if(href.charAt(0)=="#") {
        href = href.replace("#","").replace("-open","")
        formid = href
      } else {
        formid = $(this).attr('id')
      }
      $('.formality__sidebar[data-sidebar='+formid+']').addClass('formality__sidebar--open')
      let iframe = $('.formality__sidebar[data-sidebar=' + formid + '] iframe')[0];
      iframe.contentWindow.dispatchEvent(new CustomEvent('fo', { detail: 'open_sidebar' }))
    })
  },
  closeSidebar() {
    $('.formality__sidebar').click(function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).removeClass('formality__sidebar--open')
    })
  },
}