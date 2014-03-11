function InfiniteFilter (parent, opts) {
	var defaultOptions = {
		'url': '/search',
      'pageSize': 20
	};

	this.options = jQuery.extend(true, {}, defaultOptions, opts);
   this.parent = parent;
	this.elements = {};
   this.visible = false;
   this.cache = {};
   this.page = 0;
   this.state = {'type': null, 'query': null};
   this.init();
}



InfiniteFilter.prototype.init = function() {
   var self = this;
   this.elements.canvas = $("<div />").hide().addClass('infinite-filter').appendTo(this.parent);
   
}

InfiniteFilter.prototype.reset = function() {
   this.elements.canvas.find('.section').hide();
   this.state = {'type': null, 'query': null};
   this.search();
};

(function ($) { 
   $.fn.infiniteFilter = function (opts) {
   		var $this = this;
      	if ($this.infiniteFilter === undefined) {
      		$this.infiniteFilter = new InfiniteFilter($this, opts);
      	}

         return $this.infiniteFilter;
   };
}(jQuery));