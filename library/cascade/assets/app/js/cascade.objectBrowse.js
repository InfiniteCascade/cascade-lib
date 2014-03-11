function ObjectBrowser (parent, opts) {
	var defaultOptions = {
		'url': '/browse',
	};

	this.options = jQuery.extend(true, {}, defaultOptions, opts);
   this.parent = parent;
	this.elements = {};
   this.visible = false;
   this.focus = 'type';
   this.state = {'type': null, 'parent': null, 'object': null};
   this.init();
}



ObjectBrowser.prototype.init = function() {
   var self = this;
   this.elements.canvas = $("<div />").hide().addClass('object-browse').appendTo(this.parent);
   this.elements.areas = {};
   this.elements.areas.type = $("<div />").hide().addClass('section type-chooser').appendTo(this.elements.canvas);
   this.elements.areas.parent = $("<div />").hide().addClass('section parent-chooser').appendTo(this.elements.canvas);
   this.elements.areas.object = $("<div />").hide().addClass('section object-chooser').appendTo(this.elements.canvas);
   this.updateDimensions();
   $(window).on('resizeDone', function() {
      self.updateDimensions();
   });
}

ObjectBrowser.prototype.goBack = function() {
   var self = this;
   var visibleSections = this.detectVisibleSections();
   var sectionWidth = this.detectSectionWidth();
   $.debug(visibleSections);
   if (this.focus === 'type') {
      this.elements.areas.type.css({'left': 'auto'}).show();
   } else if (this.focus === 'parent') {
      if (visibleSections === 1) {
         this.elements.areas.type.hide();
      } else {
         this.elements.areas.type.show();
      }
   } else if (this.focus === 'object') {
      if (visibleSections === 1) {
         this.elements.areas.type.hide();
         this.elements.areas.parent.hide();
      } else if (visibleSections === 2) {
         this.elements.areas.type.hide();
         this.elements.areas.parent.show();
      } else {
         this.elements.areas.type.show();
         this.elements.areas.parent.show();
      }
   }
};

ObjectBrowser.prototype.renderType = function() {
   var self = this;
   this.elements.areas.type.html('');
   var canvas = $("<div />", {'class': 'canvas'}).appendTo(this.elements.areas.type);
   if (this.options.types === undefined) {
      canvas.html('<div class="alert alert-danger">No types were defined!</div>');
   } else {
      var list = $("<div />", {'class': 'list-group'}).appendTo(canvas);
      jQuery.each(this.options.types, function(index, label) {
         $("<a />", {'href': '#', 'class': 'object-type list-group-item'}).html('<i class="glyphicon glyphicon-chevron-right"></i>' + label).appendTo(list).click(function() {
            list.find('.object-type.active').removeClass('active');
            self.focus = 'parent';
            self.state.type = index;
            self.updateFocus();
            $(this).addClass('active');
         });
      });
   }
};

ObjectBrowser.prototype.renderParent = function() {
   var self = this;
   this.elements.areas.parent.html('');
   var canvas = $("<div />", {'class': 'canvas'}).appendTo(this.elements.areas.parent);
   canvas.html('<div class="alert alert-danger">not implemented!</div>');
};

ObjectBrowser.prototype.updateDimensions = function() {
   var canvasWidth = this.detectSectionWidth();
   var canvasHeight = this.elements.canvas.innerHeight();
   this.elements.canvas.find('.section').width(canvasWidth).height(canvasHeight);
   this.updatePositioning();
}

ObjectBrowser.prototype.updatePositioning = function() {
   var self = this;
   var visibleSections = this.detectVisibleSections();
   var sectionWidth = this.detectSectionWidth();
   $.debug(visibleSections);
   if (this.focus === 'type') {
      this.elements.areas.type.css({'left': 'auto'}).show();
   } else if (this.focus === 'parent') {
      if (visibleSections === 1) {
         this.elements.areas.type.hide();
      } else {
         this.elements.areas.type.show();
      }
   } else if (this.focus === 'object') {
      if (visibleSections === 1) {
         this.elements.areas.type.hide();
         this.elements.areas.parent.hide();
      } else if (visibleSections === 2) {
         this.elements.areas.type.hide();
         this.elements.areas.parent.show();
      } else {
         this.elements.areas.type.show();
         this.elements.areas.parent.show();
      }
   }
};

ObjectBrowser.prototype.detectSectionWidth = function() {
   if (this.elements.canvas.is(':visible')) {
      var canvasWidth = parseInt(this.elements.canvas.innerWidth(), 10);
   } else {
      var canvasWidth = parseInt(this.parent.innerWidth(), 10);
   }
   canvasWidth = canvasWidth - 4;
   var widthOne = canvasWidth;
   var widthTwo = canvasWidth / 2;
   var widthThree = canvasWidth / 3;
   if (widthThree > 300) {
      return widthThree;
   } else if (widthTwo > 300) {
      return widthTwo;
   } else {
      return widthOne;
   }
}

ObjectBrowser.prototype.detectVisibleSections = function() {
   if (this.elements.canvas.is(':visible')) {
      var canvasWidth = parseInt(this.elements.canvas.innerWidth(), 10);
   } else {
      var canvasWidth = parseInt(this.parent.innerWidth(), 10);
   }
   canvasWidth = canvasWidth - 4;
   var widthOne = canvasWidth;
   var widthTwo = canvasWidth / 2;
   var widthThree = canvasWidth / 3;
   if (widthThree > 300) {
      return 3;
   } else if (widthTwo > 300) {
      return 2;
   } else {
      return 1;
   }
}

ObjectBrowser.prototype.updateFocus = function() {
   var self = this;
   if (this.focus === 'type') {
      this.elements.areas.object.hide().removeClass('active');
      this.elements.areas.parent.hide().removeClass('active');
      this.renderType();
      this.elements.areas.type.addClass('active').show();
   } else if (this.focus === 'parent') {
      this.elements.areas.object.hide().removeClass('active');
      this.elements.areas.type.removeClass('active');
      this.renderParent();
      this.elements.areas.parent.addClass('active').show();
   } else if (this.focus === 'object') {
      this.elements.areas.type.removeClass('active');
      this.elements.areas.parent.removeClass('active');
      this.renderObject();
      this.elements.areas.parent.addClass('active').show();
   }
   this.updatePositioning();
};

ObjectBrowser.prototype.show = function() {
   var self = this;
   this.reset();
   this.elements.canvas.slideDown(function() { 
      self.visible = true; 
      self.updateDimensions();
   });
};

ObjectBrowser.prototype.hide = function() {
   var self = this;
   this.elements.canvas.slideUp(function() { self.visible = false; });
};

ObjectBrowser.prototype.reset = function() {
   this.elements.canvas.find('.section').hide();
   this.focus = 'type';
   this.state = {'type': null, 'parent': null, 'object': null};
   this.updateFocus();
};

(function ($) { 
   $.fn.objectBrowse = function (opts) {
   		var $this = this;
      	if ($this.objectBrowser === undefined) {
      		$this.objectBrowser = new ObjectBrowser($this, opts);
      	}

         return $this.objectBrowser;
   };
}(jQuery));