import el from '../utils/elements'
import uid from '../utils/uid'

/* eslint-disable no-unused-vars */

export default {
	init() {
		//init form submit
		var submit = this
		window.Parsley.on('form:init', function() {
			$(this.$element).submit(function(e){
				e.preventDefault()
				uid($(this))
				submit.token()
			})
		})
	},
	token() {
		//request token
		var submit = this
		if(!$(el("form", "uid")).hasClass("formality--loading")) {
      $(el("form", "uid")).addClass("formality--loading")
      $.ajax({
        url: window.formality.ajax,
        data: {
          nonce: window.formality.nonce,
          action: "formality_token",
        },
        type: 'POST',
        success: function(response){
          if(response) {
            submit.send(response.token)
          }
        },
      })
		}
	},
	send(token) {
		//send form
		var submit = this
		var fulldata = new FormData()
		var dataarray = $(el("form", "uid")).serializeArray()
    fulldata.append("action", "formality_send")
    fulldata.append("token", token)
    fulldata.append("id", $(el("form", "uid")).attr("data-id"))
    $(el("form", "uid")).find("input[type=file]").each(function(){
      fulldata.append(("field_" + $(this).prop("id")), $(this)[0].files[0])
    })
    $.each(dataarray,function(key,input){
      fulldata.append("field_" + input.name,input.value)
    })
		$.ajax({
			url: window.formality.ajax,
			data: fulldata,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(data){
				$(el("result", "uid")).addClass(el("result", false, "--visible"))
				$(el("form", "uid")).removeClass("formality--loading")
				$(el("field_focus")).removeClass(el("field_focus", false)).find(":input").blur()
				if(data.status == 200) { 
					submit.success(data)
				} else {
					submit.errors(data)
				}
			},
		})
	},
	success(data) {
		console.log(data)
		$(el("result_success", "uid")).addClass(el("result_success", false, "--active"))
		$(el("result_error", "uid")).removeClass(el("result_error", false, "--active"))
		$(el("form", "uid")).addClass("formality--sended")
		$(el("button", "uid", "--prev")).hide()
    $('html, body').stop().animate({ scrollTop: $(el("result_success", "uid")).offset().top }, 300)
	},
	errors(data) {
		console.log(data)
		$(el("result_error", "uid")).addClass(el("result_error", false, "--active"))
		$(el("result_success", "uid")).removeClass(el("result_success", false, "--active"))
		$(el("form", "uid")).addClass("formality--error")
		$('html, body').stop().animate({ scrollTop: $(el("result_error", "uid")).offset().top }, 300)
	},
}

/* eslint-enable no-unused-vars */