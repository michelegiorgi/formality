import el from '../utils/elements'
import uid from '../utils/uid'

/* eslint-disable no-unused-vars */

export default {
	init() {
		var submit = this;
		$(el("form")).submit(function(e){
			e.preventDefault();
			uid($(this));
			//submit.token();
			submit.send();
		});
	},
	token() {
		var submit = this;
		/*
		$.ajax({
			url: urlajax,
			data: { token: 1 },
			type: 'POST',
			success: function(token){
				if(token) {
					//correct token
				}
			}
		});*/
	},
	send() {
		var submit = this;
		var dataarray = $('*:not(.exclude)', el("form", "uid")).serializeArray();
		dataarray[dataarray.length] = { name: "action", value: "formality_send" };
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