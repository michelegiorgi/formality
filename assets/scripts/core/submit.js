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
		var fulldata = new FormData();
		var dataarray = $(el("form", "uid")).serializeArray();
    fulldata.append("action", "formality_send");
    fulldata.append("token", token);
    fulldata.append("id", $(el("form", "uid")).attr("data-id"));
    $(el("form", "uid")).find("input[type=file]").each(function(){
      fulldata.append(("field_" + $(this).prop("id")), $(this)[0].files[0]);
    });
    $.each(dataarray,function(key,input){
      fulldata.append("field_" + input.name,input.value);
    });
		$.ajax({
			url: window.formality.ajax,
			data: fulldata,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(data){
				if(data.status == 200) { 
					submit.success(data);
				} else {
					submit.errors(data);
				}
			},
		});
	},
	success(data) {
		console.log(data);
		const animclasses = "moveFromRight moveToRight moveFromLeft moveToLeft";
		$(el("section", "uid", "--active")).removeClass(animclasses).addClass("moveToLeft");
		$(el("result", "uid")).removeClass(animclasses).addClass("moveFromRight");
		$(el("section", "uid")).removeClass(el("section", false, "--active"));
		$(el("result", "uid")).addClass(el("result", false, "--active"));
	},
	errors(data) {
		console.log(data);
	},
}

/* eslint-enable no-unused-vars */