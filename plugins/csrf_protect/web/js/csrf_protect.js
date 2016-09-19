$(function() {
	$.ajax({
		url: "/protect",
		type: "POST",
		success: function(response) {
			$('form').append('<input type="hidden" name="csrf_token" value="' + response + '" />');
		}
	});
});