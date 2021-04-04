import { el } from './helpers'
import hooks from './hooks'

export default {
  init() {
    if ( window.location == window.parent.location ) {
      $(el('cta')).each(function(){
        const formlink = $(this).attr('href')
        const formid = $(this).attr('id')
        if(formlink && formid && (!$(el('sidebar', true, '[data-sidebar='+formid+']')).length)) {
          $('body').append('<div class="' + el('sidebar',false) + ' ' + formid + '" data-sidebar="' + formid + '"><div class="formality__sidebar__iframe" data-src="' + formlink + '"></div></div>')
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
      const href = $(this).attr('href')
      const formid = href.charAt(0)=="#" ? href.replace("#","").replace("-open","") : $(this).attr('id')
      $(el('sidebar', true, '[data-sidebar='+formid+']')).addClass(el('sidebar', false, '--open'))
      let $iframe = $(el('sidebar', true, '[data-sidebar='+formid+'] iframe'))
      if(!$iframe.length) {
        const $container = $(el('sidebar', true, '__iframe'))
        $container.html('<iframe onload="this.parentElement.classList.add(\'' + el('sidebar', false, '__iframe--show') + '\')" src="' + $container.attr("data-src") + '"></iframe>');
      } else {
        hooks.event('SidebarOpened', {}, $iframe[0].contentWindow)
      }
    })
  },
  closeSidebar() {
    $(el('sidebar')).click(function(e){
      e.stopPropagation();
      e.preventDefault();
      $(this).removeClass(el('sidebar', false, '--open'))
      $("body").focus();
      hooks.event('SidebarClosed')
    })
  },
}
