import { el } from './helpers'

export let initDbg = () => {
  const original = getComputedStyle(document.documentElement).getPropertyValue('--formality_bg');
  document.documentElement.style.setProperty('--formality_bg_backup', original);
}

export let checkDbg = ($field) => {
  const image = $field.attr('data-dbg-image')
  const color = $field.attr('data-dbg-color')
  if(image) {
    const $newbg = $('<span></span>')
    const img = new Image()
    $newbg.appendTo(el("bg"))
    img.onload = function() {
      $newbg.css({ 'background-image': 'url('+image+')', 'opacity': 1 })
      if(color) { document.documentElement.style.setProperty('--formality_bg', color) }
    }
    img.src = image;
  } else {
    const $bgs = $(el("bg", true, ' span'))
    if($bgs.length) {
      $bgs.filter(':not(:last-child)').remove()
      $bgs.filter(':last-child').css({ 'opacity': 0 })
    }
  }
  if(color) {
    if(!image) { document.documentElement.style.setProperty('--formality_bg', color) }
  } else {
    document.documentElement.style.setProperty('--formality_bg', 'var(--formality_bg_backup)')
  }
}
