(function ($) { 
   $.fn.objectSearch = function (opts) {
   		var $this = this;
   		var defaultOptions = {
			'remote': {
				'url': '/search?query=%QUERY',
			},
			'maxParallelRequests': 2
   		};
   		$this.options = jQuery.extend(true, {}, defaultOptions, opts);
   		if ($this.options.name === undefined) {
   			$this.options.name = $this.attr('id');
   		}
      	return $this.typeahead($this.options);
   };
}(jQuery));