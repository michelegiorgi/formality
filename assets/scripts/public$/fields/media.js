import { el } from '../core/helpers'
//import uiux from '../core/uiux'

export default {
  init() {
    $(el('media', true, ' video')).each(function(){
      const $video = $(this);
      const $link = $video.next();
      $link.click(function(e){
        e.preventDefault()
        let video = $video[0];
        if (video.paused) {
          video.play();
          $video.addClass('playing');
        } else {
          video.pause();
          $video.removeClass('playing');
        }  
      })
    })
  },
}