$(document).ready(function() {
	$(".message-close").click(function() {
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