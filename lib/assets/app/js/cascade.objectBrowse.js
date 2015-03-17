function ObjectBrowser (parent, options) {
   CanisBrowser.call(this, parent, options);
}

ObjectBrowser.prototype = Object.create(CanisBrowser.prototype);

(function ($) { 
   $.fn.objectBrowse = function (options) {
   		var $this = this;
      	if ($this.objectBrowseObject === undefined) {
      		$this.objectBrowseObject = new ObjectBrowser($this, options);
      	}

         return $this.objectBrowseObject;
   };
}(jQuery));