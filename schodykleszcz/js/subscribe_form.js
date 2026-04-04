$(function() {

	"use strict";

	$('.subscribe-form').on("submit", function(){
		var $this = $(this);
						   
		$('.invalid').removeClass('invalid');						   
		var msg = 'Wypełnij wymagane pola:',
			successMessage = "Dziękujemy! Poinformujemy Cię jako pierwszego!",
			error = 0,
			pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);


        if (!pattern.test($.trim($this.find('input[name="email"]').val()))) {error = 1; $this.find('input[name="email"]').parent().addClass('invalid'); msg = msg +  '\n - E-mail';}

        if (error){
        	updateTextPopup('BŁĄD', msg);
        }else{

			var url = 'import.php',
            	email = $.trim($this.find('input[name="email"]').val());

            $.post(url,{'email':email},function(data){
			    updateTextPopup('DZIĘKUJEMY!', successMessage);
	        	$this.append('<input type="reset" class="reset-button"/>');
	        	$('.reset-button').click().remove();
	        	$this.find('.focus').removeClass('focus');
			});

        }
	  	return false;
	});

	$(document).on('keyup', '.input-wrapper .input', function(){
		$(this).parent().removeClass('invalid');
	});

	function updateTextPopup(title, text){
		$('.text-popup .text-popup-title').text(title);
		$('.text-popup .text-popup-message').text(text);
		$('.text-popup').addClass('active');
	}

});