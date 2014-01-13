(function ($) { 
   $.fn.objectSearch = function (opts) {
   		var $this = this;
   		var defaultOptions = {
			'url': '/search',
   		};
   		$this.options = jQuery.extend(true, {}, defaultOptions, opts);
      	return $this.on("change keyup", function(e) {
			$.debug($this.val());
		});
   };
}(jQuery));