import { el, cl, uid, getUID, getInput, animateScroll } from '../helpers'
import { moveField } from './fields'
import { validateStep } from './validation'
//import uiux from './uiux'

export let buildNavigation = (form, sections, conversational = false) => {
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
      listHtml += `<li data-name="${ input.id }"><a href="#${ navItemId }">${ label.innerText }</a></li>`
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
          current = active;
          const sended = field.closest(cl('form', '', 'sended'))
          const navList = form.querySelector(cl('nav', 'list'))
          const navLink = navList.querySelector('a[href="#'+active+'"]')
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
          }
        }
      }
    },{ root: scrollContainer, rootMargin: "-50% 0px" });
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

    setTimeout(function() {
      const sectionFields = newSection.querySelectorAll(cl('field'))
      const focusInput = getInput(sectionFields[current > index ? sectionFields.length - 1 : 0])
      focusInput.focus()
    }, 400)

    const submitBtn = form.querySelector(cl('btn', '', 'submit'))
    const nextBtn = form.querySelector(cl('btn', '', 'next'))
    const prevBtn = form.querySelector(cl('btn', '', 'prev'))
    submitBtn.style.display = index > 0 ? '' : 'none';
    nextBtn.style.display = !atTheEnd ? '' : 'none';
    prevBtn.style.display = atTheEnd ? '' : 'none';
  }
}


/*
export default {
  init() {
    this.build()
    this.legend()
  },
  build() {
    //build navigation list
    //build required fields legend
    let nav = this
    $(el("form")).each(function() {
      uid($(this))
      const $steps = $(el("section", "uid"))
      if($steps.length > 1) {
        let stepn = 0
        $(el("button", "uid", "--prev")).toggle(false)
        $(el("submit", "uid")).toggle(false)
        $steps.each(function(){
          const head_html = $(this).find(el("section_header")) ? $(this).find(el("section_header")).html() : '';
          const $required = $(this).find(el("field_required"))
          //build legend
          let legend = ""
          for (let i = 0; i < $required.length; i++) {
            const inputname = $required.eq(i).find(":input").attr("name")
            legend += '<li data-name="'+inputname+'"></li>'
          }
          let step_class = el("nav_section", false)
          step_class += stepn==0 ? " " + el("nav_section", false, "--active"): ""
          step_class += !head_html ? " " + el("nav_section", false, "--hidden"): ""
          let step_html = '<li class="' + step_class + '"><a href="#" data-step="'+stepn+'"><div>'+ head_html +'</div></a><ul class="'+el("nav_legend", false)+'">'+legend+'</ul></li>'
          $(el("nav_list", "uid")).append(step_html)
          stepn++
        })
        nav.standard()
      } else {
        if($(el("form", "uid")).hasClass(el("form", false, "--conversational"))) {
          let section = 0
          let liststring = ""
          liststring = liststring + '<li class="' + el("nav_anchor", false)+'"><a href="#"></a><ul>'
          $(el("section", "uid", "--active > *")).each(function(){
            section++
            const id = "field_" + uid(false, false) + "_" + section
            let label = ""
            const fieldid = $(this).find(":input").attr("id")
            $(this).attr("id", id)
            if($(this).hasClass(el("field", false))) {
              label = $(this).find(el("label", true)).first().text()
              liststring = liststring + '<li data-name="'+fieldid+'"><a href="#' + id + '">' + label + '</a></li>'
            } else if($(this).hasClass(el("section_header", false))) {
              label = $(this).find("h4").text()
              liststring = liststring + '</ul></li><li class="' + el("nav_anchor", false)+'"><a href="#' + id + '">' + label + '</a><ul>'
            }
          })
          $(el("nav_list", "uid")).append(liststring + '</ul></li>')
          $(el("nav", "uid")).append('<div class="formality__nav__buttons"><button type="button" class="formality__btn formality__btn--miniprev"></button><button type="button" class="formality__btn formality__btn--mininext"></button></div>')
          nav.conversational()
        } else {
          nav.standard()
          $(el("nav", "uid")).addClass(el("nav", false, '--nosteps'))
          $(el("button", "uid", "--prev")).toggle(false)
          $(el("button", "uid", "--next")).toggle(false)
        }
      }
    })
  },
  standard() {
    //gotostep function
    $(el("nav_section", true, " a[data-step]")).click(function(e){
      const index = $(this).attr("data-step")
      uid($(this))
      e.preventDefault()
      gotoStep(index)
    })
    $(el("button", true, "--next")).click(function(e){
      e.preventDefault()
      uid($(this))
      gotoStep(current()+1)
    })
    $(el("button", true, "--prev")).click(function(e){
      uid($(this))
      e.preventDefault()
      gotoStep(current()-1)
    })
    function gotoStep(index) {
      const currentstep = current();
      const form = document.querySelector(el("form", "uid"))
      if(validate.validateStep(form, currentstep, index)) {
        const $steps = $(el("section", "uid"))
        const $nav = $(el("nav_section", "uid"))
        const atTheEnd = index >= $steps.length - 1
        anim(index)
        $steps.removeClass(el("section", false, "--active")).eq(index).addClass(el("section", false, "--active"))
        $nav.removeClass(el("nav_section", false, "--active")).eq(index).addClass(el("nav_section", false, "--active"))
        setTimeout(function() {
          let $selector = $(el("section", "uid", "--active") + " " + el("field"))
          $selector = currentstep > index ? $selector.last() : $selector.first();
          $selector.find(":input").focus();
        }, 400)
        $(el("button", "uid", "--prev")).toggle(index > 0)
        $(el("button", "uid", "--next")).toggle(!atTheEnd)
        $(el("submit", "uid")).toggle(atTheEnd)
      }
    }
    //step animations
    function anim(index) {
      const animclasses = "moveFromRight moveToRight moveFromLeft moveToLeft"
      $(el("section", "uid", "--active")).removeClass(animclasses).addClass((index > current() ? "moveToLeft" : "moveToRight" ))
      $(el("section", "uid")).eq(index).removeClass(animclasses).addClass((index > current() ? "moveFromRight" : "moveFromLeft" ))
    }
    //get current step
    function current() {
      const $steps = $(el("section", "uid"))
      return $steps.index($steps.filter(el("section", "uid", "--active")))
    }
  },
  legend() {
    //legend click
    $(el("nav_section", true, " li[data-name]")).click(function(e) {
      e.preventDefault()
      uid($(this))
      const name = $(this).attr("data-name")
      $(el("section", "uid") + " " + el("field") + " :input[name="+name+"]").focus()
    })
  },

*/
