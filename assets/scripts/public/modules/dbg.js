import { cl } from '../helpers'

export const hasDbg = (form) => {
  const dbgField = form.querySelector('[data-dbg-image], [data-dbg-color]')
  if(dbgField) { initDbg() }
  return dbgField ? true : false
}

export let initDbg = () => {
  const original = getComputedStyle(document.documentElement).getPropertyValue('--formality_bg');
  document.documentElement.style.setProperty('--formality_bg_backup', original);
}

export let updateDbg = (field) => {
  const image = field.getAttribute('data-dbg-image')
  const color = field.getAttribute('data-dbg-color')
  if(image) {
    const newBg = document.createElement('span')
    const img = new Image()
    const bgWrap = document.querySelector(cl('bg'))
    bgWrap.appendChild(newBg)
    img.onload = () => {
      newBg.style.backgroundImage = 'url('+image+')'
      newBg.style.opacity = 1
      if(color) { document.documentElement.style.setProperty('--formality_bg', color) }
    }
    img.src = image;
  } else {
    const bgs = document.querySelectorAll(cl('bg span'))
    bgs.forEach((bg) => {
      if(bg.matches(':not(:last-child)')) {
        bg.remove()
      } else {
        bg.style.opacity = 0
      }
    })
  }
  if(color) {
    if(!image) { document.documentElement.style.setProperty('--formality_bg', color) }
  } else {
    document.documentElement.style.setProperty('--formality_bg', 'var(--formality_bg_backup)')
  }
}
