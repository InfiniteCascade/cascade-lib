function ObjectBrowser (opts) {
	var defaultOptions = {
		'url': '/browse',
	};
	var options = jQuery.extend(true, {}, defaultOptions, opts);
	var elements = {};

}

ObjectBrowser.prototype.show = function() {

};

ObjectBrowser.prototype.hide = function() {

};

ObjectBrowser.prototype.reset = function() {

};

(function ($) { 
   $.fn.objectBrowse = function (opts) {
   		var $this = this;
      	if ($this.objectBrowse === undefined) {
      		$this.objectBrowse = new ObjectBrowser(opts);
      	}
      	$this.objectBrowse.reset();
      	$this.objectBrowse.show();
   };
}(jQuery));