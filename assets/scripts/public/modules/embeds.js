import { cl, el, pushEvent } from '../helpers'

export const initEmbeds = () => {
  if ( window.location == window.parent.location ) {
    const ctas = document.querySelectorAll(cl('cta'))
    ctas.forEach((cta) => {
      const link = cta.getAttribute('href')
      const id = cta.id
      if(link && id && !document.querySelector(cl('sidebar[data-sidebar='+id+']'))) {
        const sidebar = `<div class="${ el('sidebar') } ${ id }" data-sidebar="${ id }"><div class="formality__sidebar__iframe" data-src="${ link }"></div></div>`
        document.body.insertAdjacentHTML('beforeend', sidebar)
      }
      cta.addEventListener('click', (e) => {
        openSidebar(cta, e, id)
      })
    })
    const customLinks = document.querySelectorAll('[href^="#formality-open-"]')
    customLinks.forEach((customLink) => {
      customLink.addEventListener('click', (e) => {
        openSidebar(customLink, e)
      })
    })
    setTimeout(() => {
      const sidebars = document.querySelectorAll(cl('sidebar'))
      sidebars.forEach((sidebar) => {
        sidebar.classList.add(el('sidebar', '', 'loaded'))
        sidebar.addEventListener('click', (e) => {
          closeSidebar(sidebar, e)
        })
      })
    }, 1000)
  }
}

export const openSidebar = (cta, e, formId = '') => {
  e.preventDefault()
  if(!formId) { formId = cta.getAttribute('href').replace('#','').replace('-open','') }
  const sidebar = document.querySelector(cl('sidebar[data-sidebar='+formId+']'))
  if(!sidebar) return
  sidebar.classList.add(el('sidebar', '', 'open'))
  const iframe = sidebar.querySelector('iframe')
  if(!iframe) {
    const container = sidebar.querySelector(cl('sidebar', 'iframe'))
    if(!container) return
    container.innerHTML = `<iframe onload="this.parentElement.classList.add(\'${ el('sidebar', 'iframe', 'show') }\')" src="${ container.getAttribute('data-src') }"></iframe>`
  } else {
    pushEvent('SidebarOpened', {}, iframe.contentWindow)
  }
}

export const closeSidebar = (sidebar, e) => {
  e.stopPropagation()
  e.preventDefault()
  sidebar.classList.remove(el('sidebar', '', 'open'))
  document.body.focus()
  pushEvent('SidebarClosed')
}

/*

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
*/
