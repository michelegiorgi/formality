import { el } from './helpers'

export default {
  init() {
    if ( window.location == window.parent.location ) {
      $(el('cta')).each(function(){
        const formlink = $(this).attr('href')
        const formid = $(this).attr('id')
        if(formlink && formid && (!$(el('sidebar', true, '[data-sidebar='+formid+']')).length)) {
          $('body').append('<div class="' + el('sidebar',false) + ' ' + formid + '" data-sidebar="' + formid + '"><div class="formality__sidebar__iframe"><iframe src="' + formlink + '"></iframe></div></div>')
        }
      })
      setTimeout(function(){ $(el('sidebar')).addClass(el('sidebar', false, '--loaded')) }, 1000)
      this.openSidebar()
      this.closeSidebar()
    }
  },
  openSidebar() {
    $(el('cta', true, ', [href^="#formality-open-"]')).click(function(e){
      e.preventDefault()
      let href = $(this).attr('href')
      let formid = 0
      if(href.charAt(0)=="#") {
        href = href.replace("#","").replace("-open","")
        formid = href
      } else {
        formid = $(this).attr('id')
      }
      $(el('sidebar', true, '[data-sidebar='+formid+']')).addClass(el('sidebar', false, '--open'))
      let iframe = $(el('sidebar', true, '[data-sidebar='+formid+'] iframe'))[0];
      iframe.contentWindow.dispatchEvent(new CustomEvent('fo', { detail: 'open_sidebar' }))
    })
  },
  closeSidebar() {
    $(el('sidebar')).click(function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).removeClass(el('sidebar', false, '--open'))
    })
  },
}