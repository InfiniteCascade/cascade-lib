function CanisFilter (parent, opts) {
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



CanisFilter.prototype.init = function() {
   var self = this;
   this.elements.canvas = $("<div />").hide().addClass('canis-filter').appendTo(this.parent);
   
}

CanisFilter.prototype.reset = function() {
   this.elements.canvas.find('.section').hide();
   this.state = {'type': null, 'query': null};
   this.search();
};

(function ($) { 
   $.fn.canisFilter = function (opts) {
   		var $this = this;
      	if ($this.canisFilter === undefined) {
      		$this.canisFilter = new CanisFilter($this, opts);
      	}

         return $this.canisFilter;
   };
}(jQuery));