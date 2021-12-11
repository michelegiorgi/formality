const { __ } = wp.i18n

export const welcomePanel = () => {
  const welcomeToggles = document.querySelectorAll('.formality-welcome-toggle')
  if(!welcomeToggles.length) return
  const welcomePanel = document.querySelector('.welcome-panel')
  welcomeToggles.forEach((welcomeToggle) => {
    welcomeToggle.addEventListener('click', (e) => {
      e.preventDefault()
      welcomeToggle.classList.toggle('close')
      welcomeToggle.classList.add('loading')
      welcomePanel.classList.toggle('hidden')
      fetch(welcomeToggle.href, { method: 'get' })
        .then((response) => {
          welcomeToggle.classList.remove('loading')
        })
    })
  })
}

export const newsletterForm = () => {
  let form = document.querySelector('.formality-newsletter')
  if(!form) return
  const input = form.querySelector('input[name="EMAIL"]')
  const resultElement = form.querySelector('.formality-newsletter-result')
  form.addEventListener("submit", (e) => {
    e.preventDefault()
    if(form.classList.contains('success')) { return false }
    const email = input.value
    const privacy = form.querySelector('input[name="gdpr[33536]"]').checked
    let error = ''
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      error = __('Please insert a valid email address', 'formality')
    } else if(!privacy) {
      error = __('To continue you have to accept our privacy policy', 'formality')
    } else {
      const callback = 'mcsubscribe'
      let url = '//michelegiorgi.us14.list-manage.com/subscribe/post-json?u=faecff7416c1e26364c56ff3d&id=4f37f92e73'
      let data = '&c=' + callback
      const inputs = form.querySelectorAll('input')
      for (var i = 0; i < inputs.length; i++) { data += '&' + inputs[i].name + '=' + encodeURIComponent(inputs[i].value) }
      url += data

      var script = document.createElement('script')
      script.type = 'text/javascript'
      script.async = true
      script.src = url
      window[callback] = (result) => {
        delete window[callback]
        document.body.removeChild(script)
        const resulthtml = result.msg
        resultElement.innerHTML = resulthtml
        if(result.result !== 'error' || resulthtml.includes('already subscribed')) { form.classList.add('success') }
      }
      document.body.appendChild(script)
    }
    if(error) { resultElement.innerText = error }
  })
}
