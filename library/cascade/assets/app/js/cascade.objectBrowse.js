function ObjectBrowser (parent, options) {
   InfiniteBrowser.call(this, parent, options);
}

ObjectBrowser.prototype = Object.create(InfiniteBrowser.prototype);

(function ($) { 
   $.fn.objectBrowse = function (options) {
   		var $this = this;
      	if ($this.objectBrowser === undefined) {
      		$this.objectBrowser = new ObjectBrowser($this, options);
      	}

         return $this.objectBrowser;
   };
}(jQuery));