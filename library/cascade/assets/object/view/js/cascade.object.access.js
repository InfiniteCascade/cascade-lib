function AccessManager($manager) {
	var self = this;
	this.object = $manager;
	this.activeMenu = false;
	this.requestors = {};
	this.options = jQuery.extend(true, {}, this.defaultOptions, $manager.data('access'));

	$manager.find('[data-requestor]').each(function() {
		var $requestor = $(this);
		var requestorObject = new AccessRequestor(self, $requestor);
		var requestorId = requestorObject.getId();
		if (requestorId) {
			self.requestors[requestorId] = requestorObject;
		}
	});

	this.searchForm = $("<input />", {'type': 'text', 'name': 'search', 'placeholder': 'Search for new item...', 'class': 'form-control access-search-input'});
	this.searchForm.appendTo($manager);
	this.searchForm.objectSearch({
		'data': {
			'typeFilters': ['authority'],
			'ignore': self.getRequestorIds()
		},
		'callback': function(object, datum) {
			self.addRequestorRow(object, datum);
		}
	});

}

AccessManager.prototype.defaultOptions = {
	'roles': {}
};

AccessManager.prototype.addRequestorRow =  function(object, datum) {
	$.debug(object);
	$.debug(datum);
};

AccessManager.prototype.getRoles =  function() {
	var self = this;
	if (this._roles === undefined) {
		this._roles = {};
		jQuery.each(this.options.roles, function(index, value) {
			self._roles[index] = new AccessRole(value);
		});
	}
	return this._roles;
};

AccessManager.prototype.getRole = function(role) {
	var roles = this.getRoles();
	if (roles[role] !== undefined) {
		return roles[role];
	}
	return false;
}

AccessManager.prototype.handleFormSubmit = function($form, event) {
	$.debug("SUBMIT!");
	$.debug(event);
	return false;
};

AccessManager.prototype.setActiveRoleMenu = function(newMenu) {
	if (this.activeMenu && this.activeMenu.isVisible()) {
		this.activeMenu.hide();
	}
	if (newMenu && newMenu !== this.activeMenu) {
		this.activeMenu = newMenu;
		newMenu.show();
	} else {
		this.activeMenu = false;
	}
};

AccessManager.prototype.getRequestorIds = function() {
	var ids = [];
	jQuery.each(this.requestors, function(index, value) {
		ids.push(value.getId());
	});
	return ids;
};

function AccessRequestor(manager, $requestor) {
	this.manager = manager;
	this.object = $requestor;
	this.options = jQuery.extend(true, {}, this.defaultOptions, $requestor.data('requestor'));
	this.role = null;
	this.roleObject = null;
	this.getRole();
}

AccessRequestor.prototype.defaultOptions = {
	'maxRoleLevel': false
};

AccessRequestor.prototype.getLabel = function() {
	if (this.options.label !== undefined) {
		return this.options.label;
	}
	return false;
};
AccessRequestor.prototype.getId = function() {
	if (this.options.id !== undefined) {
		return this.options.id;
	}
	return false;
};
AccessRequestor.prototype.getRole = function() {
	var self = this;
	if (this.role === null) {
		this.object.find('[data-role]').each(function() {
			self.roleObject = $(this);
			var role = $(this).data('role');
			if (role.id !== undefined && (self.role = self.getPossibleRole(role.id))) {
				self.role.attach(self, $(this));
				$(this).on('click', function(e) {
					self.toggleRoleMenu();
				});
			} else {
				$(this).addClass('disabled');
			}
			if (self.role) {
				return false;
			}
		});
	}
	return this.role;
};

AccessRequestor.prototype.getManager = function() {
	return this.manager;
};

AccessRequestor.prototype.getObject = function() {
	return this.object;
};


AccessRequestor.prototype.buildRoleMenu = function() {
	var self = this;
	var menu = $('<div />', {'class': 'access-role-menu clearfix'}).hide();
	var list = $("<ul />", {'class': 'list-group'}).appendTo(menu);
	jQuery.each(this.getPossibleRoles(), function(index, value) {
		var listItem = $("<a />", {'class': 'list-group-item', 'href': '#'}).appendTo(list);
		var disabled = false;
		if (value === self.getRole()) {
			listItem.addClass('active disabled');
			disabled = true;
		}
		var listItemTitle = $("<h4 />", {'class': 'list-group-item-heading'}).html(value.getLabel()).appendTo(listItem);
		if (value.getHelpText()) {
			var listItemHelpText = $("<div />", {'class': 'list-group-item-text'}).html(value.getHelpText()).appendTo(listItem);
		}
		listItem.on('click', function(e) {
			if (!disabled) {
				self.switchRole(value);
			}
			self.getManager().setActiveRoleMenu();
			return false;
		});
	});
	return new AccessMenu(menu);
};

AccessRequestor.prototype.switchRole = function(newRole) {
	this.role = newRole;
	$.debug(newRole);
	$.debug(this.roleObject);
	this.roleObject.find('.role-label').html(newRole.getLabel());
	this.getRoleMenu().destroy();
	delete this._roleMenu;
	return true;
}

AccessRequestor.prototype.getRoleMenu = function() {
	if (this._roleMenu === undefined) {
		this._roleMenu = this.buildRoleMenu();
		this._roleMenu.attach(this);
	}
	return this._roleMenu;
};

AccessRequestor.prototype.getPossibleRoles = function() {
	var self = this;
	var roles = {};
	jQuery.each(this.getManager().getRoles(), function(index, value) {
		if (self.options.maxRoleLevel && value.getLevel() >= self.options.maxRoleLevel) { return true; }
		if (!value.getAvailable()) { return true; }
		roles[index] = value;
	});
	return roles;
};

AccessRequestor.prototype.getPossibleRole = function(role) {
	var roles = this.getPossibleRoles();
	if (roles[role] !== undefined) {
		return roles[role];
	}
	return false;
}

AccessRequestor.prototype.toggleRoleMenu = function() {
	this.getManager().setActiveRoleMenu(this.getRoleMenu());
	$.debug(this.getRoleMenu());
};

function AccessMenu($object) {
	this.object = $object;
}

AccessMenu.prototype.attach = function(requestor) {
	$.debug(requestor.getObject());
	$.debug(this.getObject());
	requestor.getObject().append(this.getObject());
};

AccessMenu.prototype.show = function(callback) {
	if (!this.object.is(':visible') && !this.object.is(':animated')) {
		this.object.slideDown(callback);
	}
};

AccessMenu.prototype.hide = function(callback) {
	if (this.object.is(':visible') && !this.object.is(':animated')) {
		this.object.slideUp(callback);
	}
};

AccessMenu.prototype.getObject = function() {
	return this.object;
};

AccessMenu.prototype.isVisible = function() {
	return this.object.is(':visible');
}

AccessMenu.prototype.destroy = function() {
	var self = this;
	if (!this.isVisible()) {
		return this.object.remove();
	} else {
		return this.hide(function() { self.object.remove() });
	}
}

function AccessRole(options) {
	var self = this;
	this.options = jQuery.extend(true, {}, this.defaultOptions, options);
}

AccessRole.prototype.attach = function(requestor, $roleButton) {
	var self = this;
	$roleButton.data('role', this);
	if (this.getRequired()) {
		$roleButton.addClass("disabled required");
	}
};

AccessRole.prototype.defaultOptions = {
	'level': 0,
	'available': true,
	'required': false,
	'label': false,
	'helpText': false
};

AccessRole.prototype.getRequestor = function() {
	return this.requestor;
};

AccessRole.prototype.getManager = function() {
	return this.requestor.manager;
};

AccessRole.prototype.getLevel = function() {
	return this.options.level;
};

AccessRole.prototype.getRequired = function() {
	return this.options.required;
};

AccessRole.prototype.getAvailable = function() {
	return this.options.available;
};

AccessRole.prototype.getLabel = function() {
	return this.options.label;
};

AccessRole.prototype.getHelpText = function() {
	return this.options.helpText;
}

$preparer.add(function(context) {
	$("[data-access]").each(function() {
		var $this = $(this);
		var $form = $(this).parents('form').first();
		var manager = new AccessManager($this);
		$this.data('manager', manager);
		$form.on('submit', function(e) {
			manager.handleFormSubmit($(this), e);
			e.preventDefault(); // during development
			e.stopPropagation();
		});
		$.debug(manager);
	});
});

