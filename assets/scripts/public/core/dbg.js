import { el, uid } from './helpers'

export default {
  init() {
    //
  },
  check($field) {
    const image = $field.attr('data-dbg-image')
    const color = $field.attr('data-dbg-color')
    if(image || color) {
      if(image) {
        const $bg = $(el("bg"))
        $bg.removeClass(el("bg", false, '--end'));
        $bg.addClass(el("bg", false, '--loading'));
        $bg.append('<span></span>')
        let img = new Image();
        img.onload = function() {
          const $newbg = $(el("bg", true, ' span'));
          $newbg.css({ 'background-image' : 'url('+image+')' })
          $bg.removeClass(el("bg", false, '--loading'))
        };
        img.src = image;
      } else {
        const $bgtoremove = $(el("bg", true, ' span.active'));
      }
    }
  },
  remove() {
    /*
    const $bg = $(el("bg"))
    const $bgtoremove = $(el("bg", true, ' span'));
    if(!$bg.hasClass(el("bg", false, '--loading'))) {
      //$bgtoremove.first().remove();
    }*/
  }
}
