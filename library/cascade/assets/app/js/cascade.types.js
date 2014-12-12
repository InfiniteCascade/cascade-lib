function CascadeTypes() {
	this.types = {};
}

CascadeTypes.prototype.load = function(types) {
	this.types = types;
	//console.log(types);
};

jQuery.cascadeTypes = new CascadeTypes();

$(document).ready(function() {
	$("[data-cascade-types]").each(function() {
		jQuery.cascadeTypes.load($(this).data('cascade-types'));
		$(this).removeData('cascade-types');
		$(this).removeAttr('data-cascade-types');
	});
});