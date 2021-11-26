import { cl } from '../helpers'

export const initMedia = (form) => {
  const videos = form.querySelectorAll(cl('media video'))
  if(!videos) return
  videos.forEach((video) => {
    const link = video.nextElementSibling
    link.addEventListener('click', (e) => {
      e.preventDefault()
      if (video.paused) {
        video.play()
        video.classList.add('playing')
      } else {
        video.pause()
        video.classList.remove('playing')
      }
    })
  })
}
