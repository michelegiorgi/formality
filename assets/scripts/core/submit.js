import el from '../utils/elements'
import uid from '../utils/uid'

/* eslint-disable no-unused-vars */

export default {
	init() {
		//init form submit
		var submit = this;
		window.Parsley.on('form:init', function() {
			$(this.$element).submit(function(e){
				e.preventDefault();
				uid($(this));
				submit.token();
			});
		});
	},
	token() {
		//request token
		var submit = this;
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
		});
	},
	send(token) {
		//send form
		var submit = this;
		var dataarray = $(el("form", "uid")).serializeArray();
		dataarray.push({ name: "action", value: "formality_send" });
		dataarray.push({ name: "token", value: token });
		var fulldata = new FormData();
    $(el("form", "uid")).find("input[type=file]").each(function(){
      fulldata.append($(this).prop("id"), $(this)[0].files[0]);
    });
    $.each(dataarray,function(key,input){
      fulldata.append(input.name,input.value);
    });
		$.ajax({
			url: window.formality.ajax,
			data: fulldata,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(data){
				alert(JSON.stringify(data))
			},
		});
	},
}

/* eslint-enable no-unused-vars */