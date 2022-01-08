import { cl, el, pushEvent } from './helpers'

export const initEmbeds = () => {
  if ( window.location == window.parent.location ) {
    const ctas = document.querySelectorAll(cl('cta'))
    ctas.forEach((cta) => {
      const link = cta.getAttribute('href')
      const id = cta.id
      if(link && id && !document.querySelector(cl('sidebar[data-sidebar='+id+']'))) {
        const sidebar = `<div class="${ el('sidebar') } ${ id }" data-sidebar="${ id }"><div class="${ el('sidebar', 'iframe') }" data-src="${ link }"></div></div>`
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
