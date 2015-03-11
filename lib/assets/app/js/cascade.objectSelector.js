function ObjectSelector (parent, options) {
   TealSelector.call(this, parent, options);
}

ObjectSelector.prototype = Object.create(TealSelector.prototype);

(function ($) { 
   $.fn.objectSelector = function (options) {
         var $this = this;
         if ($this.objectSelectorObject === undefined) {
            $this.objectSelectorObject = new ObjectSelector($this, options);
         }

         return $this.objectSelectorObject;
   };
}(jQuery));