//import el from '../utils/elements'

export default {
  init() {
    $('.formality__cta').each(function(){
      const formlink = $(this).attr('href')
      const formid = $(this).attr('id')
      if(formlink && formid && (!$('.formality__sidebar[data-sidebar='+formid+']').length)) {
        $('body').append('<div class="formality__sidebar '+formid+'" data-sidebar="'+formid+'"><div class="formality__sidebar__iframe"><iframe src="'+formlink+'"></iframe></div></div>')
      }
    })
    setTimeout(function(){ $('.formality__sidebar').addClass('formality__sidebar--loaded') }, 2000)
    this.openSidebar()
    this.closeSidebar()
  },
  openSidebar() {
    $('.formality__cta').click(function(e){
      e.preventDefault()
      const formid = $(this).attr('id')
      $('.formality__sidebar[data-sidebar='+formid+']').addClass('formality__sidebar--open')
      let iframe = $('.formality__sidebar[data-sidebar=' + formid + '] iframe')[0];
      iframe.contentWindow.focus();
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