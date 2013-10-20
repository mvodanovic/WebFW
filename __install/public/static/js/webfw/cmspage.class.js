/**
 * Initializes the CMS page.
 * If the page was loaded regularly, the scope should be the document.
 * If the page was loaded by Ajax in a dialog, the scope should be the dialog.
 *
 * @param scope The scope under which to perform the initialization, a jQuery object
 * @constructor
 */
function CMSPage(scope) {
    this.scope = scope;
    this.unsavedChangesExistMessage = 'Unsaved changes exist. Are you sure you want to leave?';
    this.contentChangedConfirmSkipped = false;
    this.contentHasChanged = false;

    this.initializeLinkDataEvents();
    this.initializeListSelectAllCheckbox();
    this.initializeMassActionButtons();
    this.initializeTooltips();
    this.initializeListSorting();
    this.initializeEditUnsavedChangesCheck();
    this.initializeDateTimePickers();
    this.initializeReferencePickers();
    this.initializeUIButtons();
}

/**
 * Sets up events for all elements which have the data-events property set.
 * The data-events property is a list of objects.
 * Each object has an eventName, functionName and functionParameters (an object with key-value pairs) defined.
 */
CMSPage.prototype.initializeLinkDataEvents = function() {
    $('[data-events]', this.scope).data('instance', this).each(function() {
        var events = $(this).data('events');
        for (var i = 0; i < events.length; i++) {
            $(this).bind(
                events[i].eventName,
                {functionName: events[i].functionName, params: events[i].functionParameters, instance: $(this).data('instance')},
                function(e) {
                    console.log(e.data);console.log(this);
                    e.data.instance[e.data.functionName](e);
                }
            );
        }
        $(this).removeAttr('data-events');
    });
}

/**
 * Initializes the "select all" checkbox in the list view.
 */
CMSPage.prototype.initializeListSelectAllCheckbox = function() {
    $('table.list thead th input[type=checkbox]', this.scope).change(function() {
        if ($(this).is(':checked')) {
            $('table.list tbody td input.row_selector[type=checkbox]').prop('checked', 'checked');
        } else {
            $('table.list tbody td input.row_selector[type=checkbox]').prop('checked', null);
        }
    });
}

/**
 * Initializes mass action buttons of the list view.
 */
CMSPage.prototype.initializeMassActionButtons = function() {
    $('table.list tfoot button', this.scope).click({instance: this}, function(e) {
        e.data.instance.executeMassAction(this);
    });
}

/**
 * Initializes all tooltips on the page.
 * This includes all info, alert and regular tooltips.
 */
CMSPage.prototype.initializeTooltips = function() {
    $('.tooltip', this.scope).tooltip({
        content: function() {
            var iconClass = null;
            var title = null;
            switch ($(this).data('class')) {
                case 'ui-state-highlight':
                    iconClass = 'ui-icon-info';
                    title = 'Info';
                    break;
                case 'ui-state-error':
                    iconClass = 'ui-icon-alert';
                    title = 'Error';
                    break;
            }
            var html = '';
            if (iconClass != null) {
                html += '<span class="ui-icon ' + iconClass + '" style="float: left; margin-right: .3em;"></span>';
                html += '<span style="font-weight: bold;">' + title + '</span>';
                html += '<br />';
            }
            html += $('<div/>').text($(this).data('text')).html();

            return html;
        },
        tooltipClass: $(this).data('class'),
        items: ".tooltip",
        show: true,
        hide: true
    }).each(function() {
        $(this).tooltip("option", "tooltipClass", $(this).data('class'));
    });
}

/**
 * Initializes the sorting functionality of the list view, if it's enabled.
 */
CMSPage.prototype.initializeListSorting = function () {
    var sortingDefinition = $('table.list', this.scope).data('sorting-definition');
    if (typeof sortingDefinition != 'undefined' && sortingDefinition != null) {
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

        $('table.list tbody', this.scope).sortable({
            helper: fixHelper,
            cursor: "move",
            containment: $('table.list'),
            axis: "y",
            opacity: 0.75,
            revert: true,
            update: function(e) {
                var itemList = $(this).sortable('toArray', {attribute: 'data-key'});
                var data = {
                    orderColumn: sortingDefinition.orderColumn,
                    groupColumns: sortingDefinition.groupColumns,
                    itemList: itemList
                };
                $.ajax({
                    url: sortingDefinition.url,
                    type: "post",
                    data: data
                });
            }
        });
    }
}

/**
 * Initializes an unsaved changes check on edit view.
 * If the user tries to leave the edit without savig changes, he will be asked to confirm first.
 */
CMSPage.prototype.initializeEditUnsavedChangesCheck = function() {
    if (this.scope.get(0) instanceof Document) {
        $(window).bind('beforeunload', {instance: this}, function(e) {
            if (e.data.instance.contentHasChanged && !e.data.instance.contentChangedConfirmSkipped) {
                return e.data.instance.unsavedChangesExistMessage;
            }
        });
    } else {
        $('a', this.scope).click({instance: this}, function(e) {
            if (e.data.instance.contentHasChanged && !e.data.instance.contentChangedConfirmSkipped) {
                if (!confirm(e.data.instance.unsavedChangesExistMessage)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            }
        });
    }

    $(".editor input, .editor select, .editor textarea", this.scope).change({instance: this}, function(e) {
        e.data.instance.contentHasChanged = true;
    });
}

/**
 * Initializes all date-, time- and datetime-pickers on the page.
 */
CMSPage.prototype.initializeDateTimePickers = function() {
    $('.datepicker', this.scope).each(function() {
        var settings = {
            dateFormat: 'yy-mm-dd',
            constrainInput: true,
            firstDay: 1
        };
        $.extend(settings, $(this).data('settings'));
        $(this).datepicker(settings);
    });

    $('.datetimepicker', this.scope).each(function() {
        var settings = {
            dateFormat: 'yy-mm-dd',
            constrainInput: true,
            firstDay: 1
        };
        $.extend(settings, $(this).data('settings'));
        $(this).datetimepicker(settings);
    });

    $('.timepicker', this.scope).each(function() {
        var settings = {
        };
        $.extend(settings, $(this).data('settings'));
        $(this).timepicker(settings);
    });
}

/**
 * Initializes all reference-pickers on the page.
 */
CMSPage.prototype.initializeReferencePickers = function() {
    if (typeof(ReferencePicker) == typeof(Function)) {
        $('.reference_select', this.scope).click({scope: this.scope}, function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var owner = e.data.scope.data('owner');
            if (typeof(ReferencePicker) == typeof(Function) && owner instanceof ReferencePicker) {
                owner.updateData($(this).data('primary-key'), $(this).data('caption'));
            }
            $(e.data.scope).dialog('close');
        });
    }

    $('.reference_picker', this.scope).each(function() {
        if (typeof(ReferencePicker) == typeof(Function)) {
            new ReferencePicker(this, $('.label', $(this).parent()).html());
        } else {
            $(this).html(CMSPage.createErrorMessage('ReferencePicker JS class definition missing!'));
        }
    });
}

/**
 * Initializes all jQuery UI buttons and button sets on the page.
 */
CMSPage.prototype.initializeUIButtons = function() {
    $('.jquery_ui_button', this.scope).each(function() {
        $(this).bind('mouseleave keyup mouseup blur', function(e) {
            if ($(this).hasClass('ui-state-persist')) {
                e.stopImmediatePropagation();
                if (e.type == 'blur') {
                    $(this).removeClass('ui-state-focus');
                }
                $(this).removeClass('ui-state-hover');
            }
        }).button($(this).data('options'));
    });

    $('.jquery_ui_buttonset', this.scope).buttonset();

    $('.jquery_ui_buttonset > button', this.scope).each(function() {
        $(this).bind('mouseleave keyup mouseup blur', function(e) {
            if ($(this).hasClass('ui-state-persist')) {
                e.stopImmediatePropagation();
                if (e.type == 'blur') {
                    $(this).removeClass('ui-state-focus');
                }
                $(this).removeClass('ui-state-hover');
            }
        });
    });
}

/**
 * An event handler used for confirming the action before continuing.
 *
 * @param e The fired event
 */
CMSPage.prototype.confirmAction = function(e) {
    if (!confirm(e.data.params.message)) {
        e.preventDefault();
        e.stopImmediatePropagation();
    }
}

/**
 * An event handler used for confirming deletion in the edit view.
 * A special case of confirmAction() to bypass the unsaved changes check.
 *
 * @param e The fired event
 * @see confirmAction
 */
CMSPage.prototype.confirmDeleteInEdit = function(e)
{
    this.contentChangedConfirmSkipped = true;
    var doAction = confirm(e.data.params.message);
    if (!doAction) {
        e.preventDefault();
        e.stopImmediatePropagation();
        this.contentChangedConfirmSkipped = false;
    }
}

/**
 * An event handler for mass action button in list view.
 *
 * @param button The button which triggered the event
 */
CMSPage.prototype.executeMassAction = function(button) {
    var buttonData = $(button).data();
    if (buttonData.url == undefined) {
        return;
    }

    var checkboxData = [];
    $("input.row_selector:checked", this.scope).each(function() {
        var data = $(this).data();
        checkboxData.push(data);
    });

    if (this.scope.get(0) instanceof Document) {
        $('<form/>').prop('action', buttonData.url).prop('method', 'post')
            .html($('<input/>').prop('type', 'hidden').prop('name', 'keys').prop('value', JSON.stringify(checkboxData)))
            .appendTo(document.body).submit();
    } else {
        $.ajax({
            url: buttonData.url,
            data: checkboxData,
            type: 'post',
            instance: this,
            beforeSend: function() { this.instance.scope.data('owner').dialogAjaxInProgress(); },
            error: function(response) { this.instance.scope.data('owner').dialogAjaxError(response); },
            success: function(data) { this.instance.scope.data('owner').dialogAjaxSuccess(data); }
        });
    }
}

/**
 * An event handler triggered before the form is submitted in edit view.
 * Used for proper handling of unsaved changes check.
 */
CMSPage.prototype.beforeSubmitEdit = function() {
    this.contentChangedConfirmSkipped = true;
}

/**
 * An event handler triggered when a tab button is clicked in edit view.
 * Switches the displayed tab.
 *
 * @param e The fired event
 */
CMSPage.prototype.switchEditTab = function(e) {
    if (e.data.params.id == null) {
        return;
    }

    $('.editor .body', this.scope).hide();
    $('.editor .header button', this.scope).removeClass('ui-state-active').removeClass('ui-state-persist');
    $('.editor .body[data-tab-id=' + e.data.params.id + ']', this.scope).show();
    $('.editor .header button[data-id=' + e.data.params.id + ']', this.scope)
        .addClass('ui-state-active').addClass('ui-state-persist');
}

/**
 * Creates an error message DIV, styled as an alert tooltip, ready to be inserted into DOM.
 *
 * @param message The message string to be used
 * @returns jQuery The message DIV ready for displaying to the user
 */
CMSPage.createErrorMessage = function(message) {
    var messageDiv = $('<div/>').addClass('message ui-widget ui-widget-content ui-corner-all ui-state-error');
    $('<span/>').addClass('ui-icon ui-icon-alert').appendTo(messageDiv);
    $('<span/>').html(message).appendTo(messageDiv);

    return messageDiv;
}

/**
 * Selects a button in the CMS navigation with the given ID.
 *
 * @param id The ID of the button to select
 * @see selectNavElementByName
 */
CMSPage.selectNavElement = function(id)
{
    var element = null;
    $('div.nav ul li').each(function() {
        if ($(this).data('id') == id) {
            element = $(this);
        }
    });
    if (element == null) {
        return;
    }

    $('div.nav ul').hide();
    $('div.nav ul li a:not(.ui-state-force-persist)').removeClass('ui-state-active').removeClass('ui-state-persist');
    $('div.nav ul[data-parent-id="0"]').show();
    var treeList = element.data('tree');
    for (var i = 0; i < treeList.length; i++) {
        var itemId = treeList[i];
        $('div.nav ul[data-parent-id='+itemId+']').show();
        $('div.nav ul li[data-id='+itemId+'] a').addClass('ui-state-active').addClass('ui-state-persist');
    }
}

/**
 * Selects a button in the CMS navigation with the given ID.
 * The preserveSelection parameter is used to keep the button of the current page always selected.
 *
 * @param name The name of the button to select
 * @param preserveSelection Optional, set to true to keep the button always selected
 * @see selectNavElement
 */
CMSPage.selectNavElementByName = function(name, preserveSelection)
{
    preserveSelection = typeof preserveSelection !== 'undefined' ? preserveSelection : false;

    var element = null;
    $('div.nav ul li').each(function() {
        if ($(this).data('name') == name) {
            element = $(this);
        }
    });
    if (element == null) {
        return;
    }

    if (preserveSelection == true) {
        $('a', element).addClass('ui-state-force-persist');
    }

    CMSPage.selectNavElement(element.data('id'));
}

/**
 * Page initialization, fires when the whole page is ready.
 * Creates the new CMSPage object and selects the proper navigation button.
 */
$(document).ready(function() {
    new CMSPage($(this));
    CMSPage.selectNavElementByName($('body').data('selected-menu-item'), true);
});
