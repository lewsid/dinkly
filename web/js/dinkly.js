$(document).ready(function() {
	$(".login-error-close").click(function() {
		$(this).parents('.alert').slideUp();
	});
});

function validateRequired(input) {
	if(input.val() == '') {
		input.parents('.form-group').addClass('has-error');
		return false;
	}
	return true;
}