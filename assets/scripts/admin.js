// import external dependencies
import 'jquery';

// Load Events
jQuery(document).ready(() => {
	
/* eslint-disable no-undef */
  
  function formality_fieldcolor() {
    $(".uf-layout-element").each(function(){
      let attr = $(this).attr("data-type");
      if(!attr) {
        const type = $(this).data("type");
        attr = type;
        console.log(type);
        $(this).attr("data-type", type);
      }
      if(attr=="step") {
        $(this).closest(".uf-layout-row").addClass("uf-layout-row--step")
      } else {
        $(this).closest(".uf-layout-row").removeClass("uf-layout-row--step")
      }
    })
  }
  
  function formality_fielduid() {
    $(".uf-field-name-uid").each(function(){
      let uid = $(this).find("input").val();
      if(!uid) {
        $(this).find("input").val(([1e7]+1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16))).change();
        console.log("Field uid created")
      }
    })
  }
  
  function formality_fakeinput() {
    //fix fake input link 
    $("body").on("mousedown", ".uf-group-title-preview", function() {
      $(this).click();
    })
  }
  

	if($("body").hasClass("post-php") && $("body").hasClass("post-type-formality_form")) {
		acf.addAction('append_field', function(result){
			$(result.$el).closest(".layout").addClass("-collapsed");
		});		

    formality_fieldcolor()   
    formality_fakeinput()
    
    var target = $( "body" )[0];
    var observer = new MutationObserver(function( mutations ) {
      mutations.forEach(function(mutation) {
        //console.log(mutation.target);
        if(mutation.target.classList.contains('uf-layout-element')) {
          formality_fieldcolor()
        } else if(mutation.target.classList.contains('uf-overlay-body')) {
          formality_fielduid() 
        }        
      });    
    });
    observer.observe(target, { childList: true, subtree: true });
    
  }
  
  //observer.disconnect();

/* eslint-enable no-undef */
	
});