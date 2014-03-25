(function ($) { 
   $.fn.objectSearch = function (opts) {
   		var $this = this;
   		var defaultOptions = {
      		'remote': {
      			'url': '/search',
      		},
            'data': {},
      		'maxParallelRequests': 2,
            'callback': function (object, datum) { $.debug(object); return false; }
   		};

   		$this.options = jQuery.extend(true, {}, defaultOptions, opts);
   		if ($this.options.name === undefined) {
   			$this.options.name = $this.attr('id');
   		}

         var engineOptions = {
            'name': 'objects',
            'queryTokenizer': Bloodhound.tokenizers.whitespace,
            'datumTokenizer': Bloodhound.tokenizers.obj.whitespace('descriptor'),
            'remote': {
               'url': $this.options.remote.url,
               'ajax': {
                  'data': $this.options.data
               }
            }
         };
         var typeOptions = {};
         var typeSource = {};

         var data = {'term': '--QUERY--'};
         engineOptions.remote.url += '?' + jQuery.param(data);
         engineOptions.remote.url = engineOptions.remote.url.replace('--QUERY--', '%QUERY');
         var engine = new Bloodhound(engineOptions);
         engine.initialize();
         $.debug(SingleTemplateEngine.compile('<p><strong>{{descriptor}}</strong>&nbsp;{{subdescriptor}}</p>'));
         typeSource.name = 'objects';
         typeSource.displayKey = 'label',
         typeSource.source = engine.ttAdapter()
         typeSource.templates = {
            'empty': [
               '<div class="empty-message">',
               'No objects matched your query.',
               '</div>'
               ].join('\n'),
            'suggestion': SingleTemplateEngine.compile('<p><strong>{{descriptor}}</strong>&nbsp;{{subdescriptor}}</p>')
         };
      	return $this.typeahead(typeOptions, typeSource).on('typeahead:autocompleted', function(event) { event.stopPropagation(); return false; }).on('typeahead:selected', $this.options.callback);
   };
}(jQuery));