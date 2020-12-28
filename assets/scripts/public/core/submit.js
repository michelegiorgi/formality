import { el, uid } from './helpers'
import hooks from './hooks'

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
        hooks.event('FormSubmit')
      })
    })
  },
  token() {
    //request token
    var submit = this
    if(!$(el("form", "uid")).hasClass("formality--loading")) {
      $(el("form", "uid")).addClass("formality--loading")
      $.ajax({
        url: window.formality.api + 'formality/v1/token/',
        data: {
          nonce: window.formality.action_nonce,
          action: "formality_token",
        },
        beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', window.formality.login_nonce ) },
        cache: false,
        type: 'POST',
        success: function(response){
          if(response.status == 200) {
            submit.send(response.token)
          } else {
            submit.result(response)
          }
        },
        error: function(){
          const data = { status: 400 }
          submit.result(data)
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
      url: window.formality.api + 'formality/v1/send/',
      data: fulldata,
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function(xhr) { xhr.setRequestHeader('X-WP-Nonce', window.formality.login_nonce ) },
      type: 'POST',
      success: function(data){
        submit.result(data)
      },
      error: function(error){
        const data = {
          status: 400,
          error: ('responseText' in error) ? error.responseText : error
        }
        submit.result(data)
      },
    })
  },
  result(data){
    let add, remove;
    $(el("result", "uid")).addClass(el("result", false, "--visible"))
    $(el("form", "uid")).removeClass("formality--loading")
    $(el("field_focus")).removeClass(el("field_focus", false)).find(":input").blur()
    if(data.status == 200) {
      add = "result_success";
      remove = "result_error";
      $(el("button", "uid", "--prev")).hide()
      $(el("form", "uid")).addClass("formality--sended")
      hooks.event('FormSuccess', { data: data})
    } else {
      add = "result_error";
      remove = "result_success";
      $(el("form", "uid")).addClass("formality--error")
      hooks.event('FormError', { data: data})
    }
    $(el(add, "uid")).addClass(el(add, false, "--active"))
    $(el(remove, "uid")).removeClass(el(remove, false, "--active"))
    $('html, body').stop().animate({ scrollTop: $(el("actions", "uid")).offset().top }, 300)
  },
}

/* eslint-enable no-unused-vars */
