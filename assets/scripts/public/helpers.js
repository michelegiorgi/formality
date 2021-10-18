export let el = (parent='', child='', modifier='') => {
  let elClass = 'formality'
  if(parent && parent!=='form') { elClass += '__' + parent }
  if(child) { elClass += '__' + child }
  if(modifier) { elClass += '--' + modifier }
  if(elClass.includes(':input')) {
    elClass += ',.' + elClass + ',.' + elClass
    const inputs = ['input', 'textarea', 'select']
    let inputIndex = -1
    elClass = elClass.replaceAll(':input', () => {
      inputIndex++
      return inputs[inputIndex]
    })
  }
  return elClass
}
export let getElements = (element='', parent=document) => {
  let elClass = '.' + element
  return parent.querySelectorAll(elClass)
}
export let getElement = (element='', parent=document) => {
  let elClass = '.' + element
  return parent.querySelector(elClass)
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
export let focusFirst = (delay = 10) => {
  const inputs = getElements(el("section:first-child :input"))
  let focus = false
  inputs.forEach((input) => {
    if(!focus && !input.value) {
      setTimeout(() => { input.focus() }, delay)
    }
  })
}
export let isMobile = () => {
  let hasTouchScreen = false
  if ("maxTouchPoints" in navigator) {
    hasTouchScreen = navigator.maxTouchPoints > 0
  } else if ("msMaxTouchPoints" in navigator) {
    hasTouchScreen = navigator.msMaxTouchPoints > 0
  } else {
    let mQ = window.matchMedia && matchMedia("(pointer:coarse)")
    if (mQ && mQ.media === "(pointer:coarse)") {
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
