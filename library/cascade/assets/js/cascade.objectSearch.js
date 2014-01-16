(function ($) { 
   $.fn.objectSearch = function (opts) {
   		var $this = this;
   		var defaultOptions = {
      		'remote': {
      			'url': '/search',
      		},
            'data': {},
      		'maxParallelRequests': 2
   		};
   		$this.options = jQuery.extend(true, {}, defaultOptions, opts);
   		if ($this.options.name === undefined) {
   			$this.options.name = $this.attr('id');
   		}
         $this.options.data['term'] = '--QUERY--';
         $this.options.remote.url += '?' + jQuery.param($this.options.data);
         $this.options.remote.url = $this.options.remote.url.replace('--QUERY--', '%QUERY');
         $this.options.template = '<p><strong>{{name}}</strong>&nbsp;{{subdescriptor}}</p>';
         $this.options.engine = SingleTemplateEngine;
         delete $this.options.data;
      	return $this.typeahead($this.options);
   };
}(jQuery));