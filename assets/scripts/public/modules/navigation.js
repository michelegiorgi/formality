import { el, cl, getUID, getInput, animateScroll } from './helpers'
import { moveField } from './fields'
import { validateStep } from './validation'

export let buildNavigation = (form, conversational = false) => {
  const sections = form.querySelectorAll(cl('section'))
  if(conversational){
    conversationalNavbar(form, sections[0])
    conversationalNavigation(form)
  } else {
    standardNavbar(form, sections)
    standardNavigation(form)
  }
}

export let standardNavbar = (form, sections) => {
  const submitBtn = form.querySelector(cl('btn','','submit'))
  const nextBtn = form.querySelector(cl('btn','','next'))
  const prevBtn = form.querySelector(cl('btn','','prev'))
  const nav = form.querySelector(cl('nav'))
  if(sections.length > 1) {
    let sectionN = 0
    prevBtn.style.display = 'none';
    submitBtn.style.display = 'none';
    const navList = form.querySelector(cl('nav', 'list'))
    sections.forEach((section) => {
      const sectionHeader = section.querySelector(cl('section', 'header'))
      const headHtml = sectionHeader ? sectionHeader.innerHTML : ''
      const requiredFields = section.querySelectorAll(cl('field', '', 'required'))
      let legend = ''
      requiredFields.forEach((field) => {
        const input = getInput(field)
        legend += `<li data-name="${ input.name }"></li>`
      })
      let sectionClass = el('nav', 'section')
      sectionClass += sectionN==0 ? ` ${ el('nav', 'section', 'active') }` : ''
      sectionClass += !headHtml ? ` ${ el('nav', 'section', 'hidden') }` : ''
      let sectionHtml = `<li class="${ sectionClass }"><a href="#" data-step="${ sectionN }"><div>${ headHtml }</div></a><ul class="${ el('nav', 'legend') }">${ legend }</ul></li>`
      navList.insertAdjacentHTML('beforeend', sectionHtml)
      sectionN++
    })
  } else {
    nav.classList.add(el('nav', '', 'nostep'))
    prevBtn.style.display = 'none';
    nextBtn.style.display = 'none';
  }
}

export let standardNavigation = (form) => {
  const sectionLinks = form.querySelectorAll(cl('nav', 'section a[data-step]'))
  sectionLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      e.preventDefault()
      const index = link.getAttribute('data-step')
      moveStep(index, form)
    })
  })
  const nextButton = form.querySelector(cl('btn', '', 'next'))
  nextButton.addEventListener('click', (e) => {
    e.preventDefault()
    moveStep('next', form)
  })
  const prevButton = form.querySelector(cl('btn', '', 'prev'))
  prevButton.addEventListener('click', (e) => {
    e.preventDefault()
    moveStep('prev', form)
  })
  const legendLinks = form.querySelectorAll(cl('nav', 'legend li'))
  legendLinks.forEach((link) => {
    link.addEventListener('click', (e)=>{
      e.preventDefault()
      const name = link.getAttribute('data-name')
      const input = form.querySelector(`[name="${ name }"]`)
      input.focus()
    })
  })
}

export let conversationalNavbar = (form, section) => {
  let sectionN = 0
  let listHtml = `<li class="${ el('nav', 'anchor') }"><a href="#"></a><ul>`
  const navList = form.querySelector(cl('nav', 'list'))
  const navItems = section.childNodes
  navItems.forEach((navItem) => {
    sectionN++
    const navItemId = `field_${ getUID(form) }_${ sectionN }`
    navItem.setAttribute('id', navItemId)
    if(navItem.classList.contains(el('field'))) {
      const input = getInput(navItem)
      const label = navItem.querySelector(cl('label'))
      const disabled = navItem.classList.contains(el('field', '', 'disabled'))
      listHtml += `<li class="${ disabled ? 'disabled' : '' }" data-name="${ input.name }"><a href="#${ navItemId }">${ label.innerText }</a></li>`
    } else if(navItem.classList.contains(el('section', 'header'))) {
      const title = navItem.querySelector('h4')
      listHtml += `</ul></li><li class="${ el('nav', 'anchor') }"><a href="#${ navItemId }">${ title.innerText }</a><ul>`
    }
  })
  listHtml += `</ul></li>`
  navList.insertAdjacentHTML('beforeend', listHtml)
}

export let conversationalNavigation = (form) => {
  const scrollContainer = document.body.classList.contains('body-formality') ? null : form.querySelector(cl('main'))
  let current = 0
  const fields = form.querySelectorAll(cl('field'))
  fields.forEach((field) => {
    const observer = new IntersectionObserver((entry) => {
      if (entry[0].isIntersecting) {
        let active = field.id
        if(current!==active) {
          const prevField = current ? form.querySelector('#' + current) : form
          current = active
          const sended = field.closest(cl('form', '', 'sended'))
          const navList = form.querySelector(cl('nav', 'list'))
          const navLink = navList.querySelector('a[href="#' + active + '"]')
          const scrollPx = parseInt(Math.max(0, (navLink.offsetLeft - (navList.offsetWidth/2) + (navLink.offsetWidth/2))))
          const prevActives = navList.querySelectorAll('a.active')
          animateScroll(scrollPx, 100, navList, false)
          prevActives.forEach((prevActive) => {
            prevActive.classList.remove('active')
          })
          navLink.classList.add('active')
          const activeTitle = navLink.closest(cl('nav', 'anchor')).firstElementChild
          activeTitle.classList.add('active')
          if(!field.classList.contains(el('field', '', 'focus')) && !sended) {
            const input = getInput(field)
            input.focus()
            prevField.classList.remove(el('field', '', 'focus'))
          }
        }
      }
    },{ root: scrollContainer, rootMargin: '-50% 0px' });
    observer.observe(field);
  })

  const anchors = form.querySelectorAll(cl('nav', 'anchor a'))
  anchors.forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      e.preventDefault()
      e.stopPropagation()
      const fieldId = anchor.getAttribute('href')
      const field = form.querySelector(fieldId)
      const input = getInput(field)
      moveField(input, field, anchor.parentElement.classList.contains(el('nav', 'anchor')) ? 'next' : false, e, true)
    }, true)
  })
}

export const moveStep = (index, form) => {
  const sections = form.querySelectorAll(cl('section'))
  const prevSection = form.querySelector(cl('section', '', 'active'))
  const current = [].slice.call(sections).indexOf(prevSection)
  switch (index) {
    case 'next':
      index = current + 1
      break
    case 'prev':
      index = current - 1
      break
  }
  const newSection = sections[index]
  if(validateStep(form, current, index)) {
    const navs = form.querySelectorAll(cl('nav', 'section'))
    const atTheEnd = index >= sections.length - 1
    const animClasses = [ 'moveFromRight', 'moveToRight', 'moveFromLeft', 'moveToLeft' ]
    prevSection.classList.remove(...animClasses)
    newSection.classList.remove(...animClasses)
    prevSection.classList.add(index > current ? animClasses[3] : animClasses[1])
    newSection.classList.add(index > current ? animClasses[0] : animClasses[2])

    prevSection.classList.remove(el('section', '', 'active'))
    newSection.classList.add(el('section', '', 'active'))

    navs.forEach((nav, navindex) => {
      nav.classList.toggle(el('nav', 'section', 'active'), navindex == index)
    })

    setTimeout(() => {
      const sectionFields = newSection.querySelectorAll(cl('field'))
      const focusInput = getInput(sectionFields[current > index ? sectionFields.length - 1 : 0])
      focusInput.focus()
    }, 400)

    const submitBtn = form.querySelector(cl('btn', '', 'submit'))
    const nextBtn = form.querySelector(cl('btn', '', 'next'))
    const prevBtn = form.querySelector(cl('btn', '', 'prev'))
    submitBtn.style.display = atTheEnd ? '' : 'none';
    nextBtn.style.display = !atTheEnd ? '' : 'none';
    prevBtn.style.display = index > 0 ? '' : 'none';
  }
}
