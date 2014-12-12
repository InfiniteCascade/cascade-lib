function ObjectSearch (parent, options) {
   InfiniteSearch.call(this, parent, options);
}

ObjectSearch.prototype = Object.create(InfiniteSearch.prototype);

(function ($) { 
   $.fn.objectSearch = function (options) {
         var $this = this;
         if ($this.objectSearchObject === undefined) {
            $this.objectSearchObject = new ObjectSearch($this, options);
         }

         return $this.objectSearchObject;
   };
}(jQuery));