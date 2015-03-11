function TealFilter (parent, opts) {
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



TealFilter.prototype.init = function() {
   var self = this;
   this.elements.canvas = $("<div />").hide().addClass('teal-filter').appendTo(this.parent);
   
}

TealFilter.prototype.reset = function() {
   this.elements.canvas.find('.section').hide();
   this.state = {'type': null, 'query': null};
   this.search();
};

(function ($) { 
   $.fn.tealFilter = function (opts) {
   		var $this = this;
      	if ($this.tealFilter === undefined) {
      		$this.tealFilter = new TealFilter($this, opts);
      	}

         return $this.tealFilter;
   };
}(jQuery));