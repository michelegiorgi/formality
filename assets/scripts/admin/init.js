const { __ } = wp.i18n

export default function() {
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
  
  
  $(".formality-newsletter").submit(function(e) {
		e.preventDefault()
		const $form = $(this)
		const $resultElement = $form.find(".formality-newsletter-result")
		const email = $form.find("input[type='email']").val()
    const privacy = $form.find('input[type=checkbox]').prop('checked')
    const list = '//michelegiorgi.us14.list-manage.com/subscribe/post-json?u=faecff7416c1e26364c56ff3d&id=4f37f92e73'
    let error = __("Sembra esserci un problema con i nostri server. Riprova più tardi.", "formality")
    if (!email || !email.length || (email.indexOf("@") == -1)) {
      $resultElement.text(__("Inserisci un indirizzo e-mail valido", "formality"))
    } else if (!privacy) {
      $resultElement.text(__("Per procedere con la registrazione devi acconsentire al trattamento dei tuoi dati personali", "formality"));
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
}