const { __ } = wp.i18n

export default function() {
  //welcome panel toggle
  $('.formality-welcome-toggle').click(function(e){
    e.preventDefault()
    $('.formality-welcome-toggle').toggleClass('close').addClass('loading')
    $('.welcome-panel').toggleClass('hidden')
    const href = this.href
    // eslint-disable-next-line no-unused-vars
    $.ajax({ url: href }).done(function(data) {
      $('.formality-welcome-toggle').removeClass('loading')
    })
  })

  //export panel toggle
  $('.formality-export-toggle').click(function(e){
    e.preventDefault()
    $('.export-panel').toggleClass('hidden')
  })

  //newsletter
  $(".formality-newsletter").submit(function(e) {
    e.preventDefault()
    const $form = $(this)
    const $resultElement = $form.find(".formality-newsletter-result")
    const email = $form.find("input[type='email']").val()
    const privacy = $form.find('input[type=checkbox]').prop('checked')
    const list = '//michelegiorgi.us14.list-manage.com/subscribe/post-json?u=faecff7416c1e26364c56ff3d&id=4f37f92e73'
    let error = __("Something went wrong. Please retry later.", "formality")
    if (!email || !email.length || (email.indexOf("@") == -1)) {
      $resultElement.text(__("Please insert a valid email address", "formality"))
    } else if (!privacy) {
      $resultElement.text(__("To continue you have to accept our privacy policy", "formality"));
    } else {
      if(!$form.hasClass("disabled")) {
        $form.addClass("loading");
        $resultElement.html("");
        $.ajax({
          type: "GET",
          url: list,
          data: $form.serialize(),
          cache: false,
          dataType: "jsonp",
          jsonp: "c",
          contentType: "application/json; charset=utf-8",
          error: function(){
            $form.removeClass("loading");
            $resultElement.html(error);
          },
          success: function(data){
            $form.removeClass("loading");
            var message = data.msg || error;
            $resultElement.text(message);
          },
        })
      }
    }
  })

  //export
  let exportForm = document.querySelector('.export-panel form')
  let exportLink = exportForm.querySelector('.result > a')
  let exportProgressbar = exportForm.querySelector('.progress > .bar')

  exportForm.addEventListener('submit', function(e){
    e.preventDefault();
    exportResults(exportForm)
  })

  function exportResults(exportForm) {
    exportLink.innerText = '';
    exportProgressbar.style.width = '0%';
    exportForm.classList.add('loading');
    let url = new URL(exportForm.getAttribute('action'));
    for (const pair of new FormData(exportForm)) { url.searchParams.append(pair[0], pair[1]); }
    fetch(url, { method: 'get' })
      .then((response) => {
        if (response.ok) {
          return response.json();
        } else {
          throw new Error('Something went wrong');
        }
      })
      .then(data => {
        if('url' in data) {
          exportProgressbar.style.width = '100%';
          setTimeout(function(){
            exportForm.classList.remove('loading');
            exportLink.setAttribute('href', data.url)
            exportLink.setAttribute('download', data.file)
            exportLink.innerText = 'Download now'
            let click = document.createEvent('MouseEvents')
            click.initEvent('click' ,true ,true)
            exportLink.dispatchEvent(click)
          }, 800)
        } else {
          alert('error')
        }
      })
      .catch((error) => {
        console.log(error)
      });
  }
}
