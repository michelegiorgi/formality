export let el = (parent='', child='', modifier='') => {
  let elClass = 'formality'
  if(parent && parent!=='form') { elClass += '__' + parent }
  if(child) { elClass += '__' + child }
  if(modifier) { elClass += '--' + modifier }
  if(elClass.includes(':input')) {
    elClass += ',' + elClass + ',' + elClass
    const inputs = ['input', 'textarea', 'select']
    let inputIndex = -1
    elClass = elClass.replaceAll(':input', () => {
      inputIndex++
      return inputs[inputIndex]
    })
  }
  return elClass
}

export let cl = (parent='', child='', modifier='') => {
  const element = el(parent, child, modifier)
  return '.' + element.replaceAll(',', ',.')
}

export let isIn = (elem, centerH = true) => {
  const distance = elem.getBoundingClientRect()
  let height = window.innerHeight || document.documentElement.clientHeight
  height = centerH ? height * .75 : height
  return (
    distance.top >= 0 &&
    distance.left >= 0 &&
    distance.bottom <= height &&
    distance.right <= (window.innerWidth || document.documentElement.clientWidth)
  )
}

export let getInput = (field, multiple=false) => {
  const inputs = 'input, textarea, select'
  return multiple ? field.querySelectorAll(inputs) : field.querySelector(inputs)
}

export let isConversational = (form) => {
  return form.classList.contains(el('form', '', 'conversational'))
}

export let isMobile = () => {
  let hasTouchScreen = false
  if ('maxTouchPoints' in navigator) {
    hasTouchScreen = navigator.maxTouchPoints > 0
  } else if ('msMaxTouchPoints' in navigator) {
    hasTouchScreen = navigator.msMaxTouchPoints > 0
  } else {
    let mQ = window.matchMedia && matchMedia('(pointer:coarse)')
    if (mQ && mQ.media === '(pointer:coarse)') {
      hasTouchScreen = !!mQ.matches
    } else if ('orientation' in window) {
      hasTouchScreen = true // deprecated, but good fallback
    } else {
      let UA = navigator.userAgent;
      hasTouchScreen = (
        /\b(BlackBerry|webOS|iPhone|IEMobile)\b/i.test(UA) ||
        /\b(Android|Windows Phone|iPad|iPod)\b/i.test(UA)
      )
    }
  }
  return hasTouchScreen
}

export let isVisible = (element) => {
  return !!( elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length ) && window.getComputedStyle(elem).visibility !== 'hidden';
}

export let pushEvent = (name, options = {}, target = window) => {
  const event = new CustomEvent('fo' + name, {
    view: window,
    bubbles: true,
    detail: options
  })
  target.dispatchEvent(event)
}

export let nextEl = (elem, selector) => {
  var sibling = elem.nextElementSibling
  if (!selector) return sibling
  while (sibling) {
    if (sibling.matches(selector)) return sibling
    sibling = sibling.nextElementSibling
  }
}

export let prevEl = (elem, selector) => {
  var sibling = elem.previousElementSibling
  if (!selector) return sibling
  while (sibling) {
    if (sibling.matches(selector)) return sibling
    sibling = sibling.previousElementSibling
  }
}

export let getUID = (form) => {
  return form.getAttribute('data-uid')
}

export const animateScroll = (to, duration, element = document.scrollingElement || document.documentElement, y=true) => {
  const start = y ? element.scrollTop : element.scrollLeft
  const change = to - start
  const startDate = +new Date()
  const easeInOutQuad = (t, b, c, d) => {
    let t2 = t
    t2 /= d / 2
    if (t2 < 1) return (c / 2) * t2 * t2 + b
    t2 -= 1
    return (-c / 2) * (t2 * (t2 - 2) - 1) + b
  };
  const animateScroll = () => {
    const currentDate = +new Date()
    const currentTime = currentDate - startDate
    const scroll = parseInt(easeInOutQuad(currentTime, start, change, duration), 10)
    if(y) {
      element.scrollTop = scroll
    } else {
      element.scrollLeft = scroll
    }
    if(currentTime < duration) {
      requestAnimationFrame(animateScroll)
    } else if(y) {
      element.scrollTop = to
    } else {
      element.scrollLeft = to
    }
  }
  animateScroll()
};
