$preparer.add(function(context) {
	$("select[multiple]", context).selectpicker();
	$(".date", context).datepicker({
		autoclose: true,
		todayHighlight: true
	});
});