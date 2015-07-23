function AccessManager($manager) {
    var self = this;
    this.$object = $manager;
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

    var selectorOptions = this.options.selector;
    selectorOptions.callback = function($selector, object) {
        self.addRequestorRow(object);
        $selector.resetSelector();
    };
    var baseData = {
        'ignore': self.getRequestorIds()
    };

    if (selectorOptions.search.data === undefined) {
        selectorOptions.search.data = {};
    }
    if (selectorOptions.browse.data === undefined) {
        selectorOptions.browse.data = {};
    }
    selectorOptions.browse.data = jQuery.extend(true, {}, selectorOptions.browse.data, baseData);
    selectorOptions.search.data = jQuery.extend(true, {}, selectorOptions.search.data, baseData);
    selectorOptions.search.data.typeFilters = ['authority'];
    this.selectorField = $('<input />', {'type': 'hidden', 'name': 'search'});
    this.selectorField.insertAfter($manager);
    this.selectorField.objectSelector(selectorOptions);

}

AccessManager.prototype.defaultOptions = {
    'types': {},
    'roles': {},
    'universalMaxRoleLevel': true,
    'selector': {
        'browse': {},
        'search': {}
    }
};

AccessManager.prototype.addRequestorRow =  function(object) {
    var self = this;
    var type = this.getType(object.objectType);
    if (!type) { return false; }
    var requestorObject = {};
    requestorObject.id = object.id;
    requestorObject.label = object.descriptor;
    requestorObject.type = object.type;

    var $row = $('<li />', {'class': 'list-group-item'}).data('requestor', requestorObject);
    var requestor = new AccessRequestor(this, $row, type);
    this.requestors[requestor.getId()] = requestor;
    var role = type.getInitialRole();
    if (!role) { return false; }
    var $role = $('<button />', {'class': 'btn btn-default pull-right has-role-object'}).html(role.getButtonLabel()).data('role', role).appendTo($row);
    $role.attr('type', 'button');
    role.attach(requestor, $role);
    var $header = $('<h4 />', {'class': 'list-group-item-heading'}).html(object.descriptor).appendTo($row);
    var $subheader = $('<p />', {'class': 'list-group-item-text help-text'}).html(type.getLabel()).appendTo($row);

    $row.appendTo(this.$object);
    return $row;
};

AccessManager.prototype.getDefaultRole = function() {

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

AccessManager.prototype.getTypes =  function() {
    var self = this;
    if (this._types === undefined) {
        this._types = {};
        jQuery.each(this.options.types, function(index, value) {
            self._types[index] = new AccessType(self, value);
        });
    }
    return this._types;
};

AccessManager.prototype.getType = function(type) {
    var types = this.getTypes();
    if (types[type] !== undefined) {
        return types[type];
    }
    return false;
}

AccessManager.prototype.handleFormSubmit = function($form, event) {
    $form.data('data', {'roles': this.packageRoles()});
    return true;
};

AccessManager.prototype.packageRoles = function() {
    var rolePackage = {};
    var self = this;
    jQuery.each(this.requestors, function(index, requestor) {
        var role = requestor.getRole();
        if (!role) { return true; }
        rolePackage[requestor.getId()] = role.getId();
    });
    return rolePackage;
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

function AccessRequestor(manager, $requestor, type) {
    this.manager = manager;
    this.$object = $requestor;
    this.options = jQuery.extend(true, {}, this.defaultOptions, $requestor.data('requestor'));
    if (type === undefined && this.options.type !== undefined) {
        type = manager.getType(this.options.type);
    }
    this.type = type || false;
    this.role = null;
    this.roleObject = null;
    this.getRole();
}

AccessRequestor.prototype.defaultOptions = {
    'maxRoleLevel': false,
    'editable': true
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
        this.$object.find('.has-role-object').each(function() {
            self.roleObject = $(this);
            self.role = $(this).data('role');
            return false;
        });
        if (this.role === null) {
            this.$object.find('[data-role]').each(function() {
                self.roleObject = $(this);
                var role = $(this).data('role');
                if (role.id !== undefined && (self.role = self.getPossibleRole(role.id))) {
                    self.role.attach(self, $(this));
                } else {
                    $(this).addClass('disabled');
                }
                if (self.role) {
                    return false;
                }
            });
        }
    }
    return this.role;
};

AccessRequestor.prototype.getManager = function() {
    return this.manager;
};

AccessRequestor.prototype.getObject = function() {
    return this.$object;
};

AccessRequestor.prototype.buildRoleMenu = function() {
    var self = this;
    var menu = $('<div />', {'class': 'access-role-menu clearfix'}).hide();
    var list = $('<ul />', {'class': 'list-group'}).appendTo(menu);
    jQuery.each(this.getPossibleRoles(), function(index, value) {
        var listItem = $('<a />', {'class': 'list-group-item', 'href': '#'}).appendTo(list);
        var disabled = false;
        if (value === self.getRole()) {
            listItem.addClass('active disabled');
            disabled = true;
        }
        var listItemTitle = $('<h4 />', {'class': 'list-group-item-heading'}).html(value.getLabel()).appendTo(listItem);
        if (value.getHelpText()) {
            var listItemHelpText = $('<div />', {'class': 'list-group-item-text'}).html(value.getHelpText()).appendTo(listItem);
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
    if (!newRole) { return false; }
    this.role = newRole;
    this.roleObject.find('.role-label').html(newRole.getLabel());
    newRole.check(this, this.roleObject, true);
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
        if (self.options.maxRoleLevel && self.options.maxRoleLevel !== true && value.getLevel() > self.options.maxRoleLevel) { return true; }
        if (self.getManager().options.universalMaxRoleLevel !== undefined && self.getManager().options.universalMaxRoleLevel !== true && value.getLevel() > self.getManager().options.universalMaxRoleLevel) { return true; }
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
};

function AccessMenu($object) {
    this.$object = $object;
}

AccessMenu.prototype.attach = function(requestor) {
    requestor.getObject().append(this.getObject());
};

AccessMenu.prototype.show = function(callback) {
    if (!this.$object.is(':visible') && !this.$object.is(':animated')) {
        this.$object.slideDown(callback);
    }
};

AccessMenu.prototype.hide = function(callback) {
    if (this.$object.is(':visible') && !this.$object.is(':animated')) {
        this.$object.slideUp(callback);
    }
};

AccessMenu.prototype.getObject = function() {
    return this.$object;
};

AccessMenu.prototype.isVisible = function() {
    return this.$object.is(':visible');
}

AccessMenu.prototype.destroy = function() {
    var self = this;
    if (!this.isVisible()) {
        return this.$object.remove();
    } else {
        return this.hide(function() { self.$object.remove() });
    }
}

function AccessType(manager, options) {
    var self = this;
    this.manager =  manager;
    this.options = jQuery.extend(true, {}, this.defaultOptions, options);
}

AccessType.prototype.defaultOptions = {
    'label': false,
    'possibleRoles': [],
    'initialRole': [],
    'requiredRoles': []

};

AccessType.prototype.isRequiredRole = function(role) {
    if (jQuery.inArray(role.getId(), this.options.requiredRoles) !== -1) {
        return true;
    }
    return false;
};

AccessType.prototype.getManager = function() {
    return this.manager;
};

AccessType.prototype.getInitialRole = function() {
    var role = false;
    var self = this;
    jQuery.each(this.options.initialRole, function(index, value) {
        if (jQuery.inArray(value, self.getPossibleRoles()) !== -1) {
            role = self.getManager().getRole(value);
            return false;
        }
    });
    return role;
}

AccessType.prototype.getPossibleRoles = function() {
    return this.options.possibleRoles;
};

AccessType.prototype.getRequiredRoles = function() {
    return this.options.requiredRoles;
};

AccessType.prototype.getLabel = function() {
    return this.options.label;
};

function AccessRole(options) {
    var self = this;
    this.options = jQuery.extend(true, {}, this.defaultOptions, options);
}

AccessRole.prototype.attach = function(requestor, $roleButton) {
    var self = this;
    $roleButton.data('role', this);

    $roleButton.on('click', function(e) {
        requestor.toggleRoleMenu();
    });
    this.check(requestor, $roleButton, false);
};

AccessRole.prototype.check = function(requestor, $roleButton, checkConflict) {
    var self = this;
    if (requestor.type && requestor.type.isRequiredRole(this)) {
        $roleButton.addClass('disabled required');
    } else {
        $roleButton.removeClass('disabled required');
    }
    if (checkConflict) {
        if (this.options.exclusive) {
            var manager = requestor.getManager();
            jQuery.each(manager.requestors, function(index, requestorItem) {
                if (requestorItem === requestor || !requestorItem.options.editable) {
                    return true;
                }
                if (requestorItem.role && requestorItem.role === self) {
                    var newRole = requestorItem.type.getInitialRole();
                    requestorItem.switchRole(newRole);
                }
            });
        }
    }
};

AccessRole.prototype.defaultOptions = {
    'id': null,
    'level': 0,
    'available': true,
    'label': false,
    'helpText': false,
    'exclusive': false,
    'conflictRole': 'none'
};

AccessRole.prototype.getRequestor = function() {
    return this.requestor;
};

AccessRole.prototype.getId = function() {
    return this.options.id;
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

AccessRole.prototype.getButtonLabel = function() {
    var $container = $('<div/ >');
    $container.append($('<span />').addClass('role-label').html(this.getLabel()));
    $container.append(' ');
    $container.append($('<span />').addClass('caret'));
    return $container.html();
};

AccessRole.prototype.getHelpText = function() {
    return this.options.helpText;
}

$preparer.add(function(context) {
    $('[data-access]').each(function() {
        var $this = $(this);
        var $form = $(this).parents('form').first();
        var manager = new AccessManager($this);
        $this.data('manager', manager);
        $form.on('submit', function(e) {
            manager.handleFormSubmit($(this), e);
        });
    });
});
