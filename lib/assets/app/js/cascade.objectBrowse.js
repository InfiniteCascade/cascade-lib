function ObjectBrowser (parent, options) {
   TealBrowser.call(this, parent, options);
}

ObjectBrowser.prototype = Object.create(TealBrowser.prototype);

(function ($) { 
   $.fn.objectBrowse = function (options) {
   		var $this = this;
      	if ($this.objectBrowseObject === undefined) {
      		$this.objectBrowseObject = new ObjectBrowser($this, options);
      	}

         return $this.objectBrowseObject;
   };
}(jQuery));