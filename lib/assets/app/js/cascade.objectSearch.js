function ObjectSearch (parent, options) {
   CanisSearch.call(this, parent, options);
}

ObjectSearch.prototype = Object.create(CanisSearch.prototype);

(function ($) { 
   $.fn.objectSearch = function (options) {
         var $this = this;
         if ($this.objectSearchObject === undefined) {
            $this.objectSearchObject = new ObjectSearch($this, options);
         }

         return $this.objectSearchObject;
   };
}(jQuery));