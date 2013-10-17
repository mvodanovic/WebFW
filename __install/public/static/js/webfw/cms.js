function switchEditTab(button)
{
    var id = $(button).data('id');
    if (id == null) {
        return;
    }

    $('.editor .body').hide();
    $('.editor .header button').removeClass('active');
    $('.editor .body[data-tab-id=' + id + ']').show();
    $(button).addClass('active');
}

function executeMassAction(button, dialog)
{
    var buttonData = $(button).data();
    if (buttonData.url == undefined) {
        return;
    }

    if (buttonData.confirm != undefined) {
        if (!confirm(buttonData.confirm)) {
            return;
        }
    }

    var checkboxData = [];
    $("input.row_selector:checked").each(function() {
        var data = $(this).data();
        checkboxData.push(data);
    });

    if (dialog == undefined) {
        $('<form>', {
            "html": '<input type="hidden" name="keys" value="' + encodeURIComponent(JSON.stringify(checkboxData)) + '" />',
            "action": buttonData.url,
            "method": "post"
        }).appendTo(document.body).submit();
    } else {
        $.ajax({
            url: buttonData.url,
            data: checkboxData,
            type: 'post',
            beforeSend: function() { dialogAjaxInProgress(dialog); },
            error: function(response) { dialogAjaxError(response, dialog); },
            success: function(data) { dialogAjaxSuccess(data, dialog); }
        });
    }
}

function confirmAction(e)
{
    if (!confirm(e.data.params.message)) {
        e.preventDefault();
        e.stopImmediatePropagation();
    }
}

function select_nav_element(id)
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
    $('div.nav ul li a').removeClass('ui-state-focus');
    $('div.nav ul[data-parent-id=0]').show();
    var treeList = element.data('tree');
    for (var i = 0; i < treeList.length; i++) {
        var itemId = treeList[i];
        $('div.nav ul[data-parent-id='+itemId+']').show();
        $('div.nav ul li[data-id='+itemId+'] a').addClass('ui-state-focus');
    }
}

function select_nav_element_by_name(name)
{
    var element = null;
    $('div.nav ul li').each(function() {
        if ($(this).data('name') == name) {
            element = $(this);
        }
    });
    if (element == null) {
        return;
    }

    select_nav_element(element.data('id'));
}

var contentHasChanged = false;
var contentChangedConfirmSkipped = false;

function beforeSubmitEdit(e)
{
    contentChangedConfirmSkipped = true;
}

function beforeDelete(message)
{
    contentChangedConfirmSkipped = true;
    var doAction = confirm(message);
    if (!doAction) {
        contentChangedConfirmSkipped = false;
    }
    return doAction;
}

function initializePage(scope)
{
    $('a[data-events]').each(function() {
        var events = $(this).data('events');
        for (var i = 0; i < events.length; i++) {
            $(this).bind(
                events[i].eventName,
                {functionName: events[i].functionName, params: events[i].functionParameters},
                function(e) {
                    window[e.data.functionName](e);
            });
        }
        $(this).removeAttr('data-events');
    });

    $('table.list thead th input[type=checkbox]', $(scope)).change(function() {
        if ($(this).is(':checked')) {
            $('table.list tbody td input.row_selector[type=checkbox]').prop('checked', 'checked');
        } else {
            $('table.list tbody td input.row_selector[type=checkbox]').prop('checked', null);
        }
    });

    $('table.list tfoot button', $(scope)).click(function() {
        executeMassAction(this);
    });

    $('.tooltip', $(scope)).tooltip({
        content: function() {
            return $(this).data('text');
        },
        items: ".tooltip",
        show: true,
        hide: true
    }).each(function() {
            $(this).tooltip("option", "tooltipClass", $(this).data('class'));
        });

    if (typeof sortingDef !== 'undefined' && sortingDef !== null) {
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

        $('table.list tbody').sortable(
        {
            helper: fixHelper,
            cursor: "move",
            containment: $('table.list'),
            axis: "y",
            opacity: 0.75,
            revert: true,
            update: function(event, ui) {
                var itemList = $(this).sortable('toArray', {attribute: 'data-key'});
                var data = {
                    orderColumn: sortingDef.orderColumn,
                    groupColumns: sortingDef.groupColumns,
                    itemList: itemList
                };
                $.ajax(
                    {
                        url: sortingDef.url,
                        type: "post",
                        data: data
                    });
            }
        });
    }

    $(window, $(scope)).bind('beforeunload', function() {
        if (typeof unsavedChangesExistMessage !== 'undefined' && unsavedChangesExistMessage !== null) {
            if (contentHasChanged === true && contentChangedConfirmSkipped !== true) {
                return unsavedChangesExistMessage;
            }
        }

        contentChangedConfirmSkipped = false;
    });

    $(".editor input,select,textarea", $(scope)).change(function() {
        contentHasChanged = true;
    });

    $('.datepicker', $(scope)).each(function() {
        var settings = {
            dateFormat: 'yy-mm-dd',
            constrainInput: true,
            firstDay: 1
        };
        $.extend(settings, $(this).data('settings'));
        $(this).datepicker(settings);
    });

    $('.datetimepicker', $(scope)).each(function() {
        var settings = {
            dateFormat: 'yy-mm-dd',
            constrainInput: true,
            firstDay: 1
        };
        $.extend(settings, $(this).data('settings'));
        $(this).datetimepicker(settings);
    });

    $('.timepicker', $(scope)).each(function() {
        var settings = {
        };
        $.extend(settings, $(this).data('settings'));
        $(this).timepicker(settings);
    });

    $('.reference_select', $(scope)).click(function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        $(scope).data('primary-key', $(this).data('primary-key'));
        $(scope).data('caption', $(this).data('caption'));
        updateReferencePickerInputs($(scope));
        $(scope).dialog('close');
    });

    $('.reference_picker', $(scope)).each(function() {
        var url = $(this).data('url');
        var name = $(this).data('name');
        var value = $(this).data('value');
        var caption = $(this).data('caption');

        $(this).append($('<input/>').prop('type', 'text').prop('readonly', true).val(caption).css('marginRight', '5px'));
        $(this).append($('<button/>').prop('type', 'button').data('options', {
            icons: {primary: 'ui-icon-newwin'},
            label: 'Select'
        }).addClass('jquery_ui_button').click({url: url, title: caption}, function(e) {
            function createDialog(url, dialog) {
                $.ajax({
                    url: url,
                    beforeSend: function() { dialogAjaxInProgress(dialog); },
                    error: function(response) { dialogAjaxError(response, dialog); },
                    success: function(data) { dialogAjaxSuccess(data, dialog); }
                });
            }

            var windowWidth = $(window).width();
            var windowHeight = $(window).height();
            var dialog = $('<div/>').data('reference-picker', $(this).parents('.reference_picker').get(0)).dialog({
                title: e.data.title,
                modal: true,
                draggable: false,
                resizable: false,
                width: Math.round(windowWidth < 640 ? windowWidth - 10 : windowWidth * 0.9),
                height: Math.round(windowHeight < 480 ? windowHeight - 10 : windowHeight * 0.9),
                create: function() { createDialog(e.data.url, this); },
                close: function() { $(this).dialog('destroy').remove(); }
            });
        }));
        $(this).append($('<button/>').prop('type', 'button').data('options', {
            icons: {primary: 'ui-icon-close'},
            label: 'Clear'
        }).addClass('jquery_ui_button').click(function() {
            $('input', $($(this).parents('.reference_picker').get(0))).val('');
        }).css('margin-left', '3px'));
    });

    $('.jquery_ui_button', $(scope)).each(function() {
        $(this).button($(this).data('options'));
    });
}

function updateReferencePickerInputs(element)
{
    var primaryKey = element.data('primary-key');
    var caption = element.data('caption');
    var referencePicker = $(element.data('reference-picker'));

    if (primaryKey == undefined) {
        return;
    }

    if (caption == undefined) {
        caption = JSON.stringify(primaryKey);
    }

    for (var key in primaryKey) {
        if ($('input[name=' + key + ']', referencePicker).length) {
            $('input[name=' + key + ']', referencePicker).val(primaryKey[key]);
        } else {
            referencePicker.append($('<input/>').prop('type', 'hidden').prop('name', key).val(primaryKey[key]));
        }
    }

    $('input[type=text]', referencePicker).val(caption);
}

function dialogAjaxInProgress(dialog)
{
    $(dialog).html('Please wait...').append($('<div/>').progressbar({value: false}));
}

function dialogAjaxSuccess(data, dialog)
{
    $(dialog).html(data);
    initializePage(dialog);
    preemptLinkActions(dialog);
    preemptMassActions(dialog);
    preemptFormSubmits(dialog);
}

function dialogAjaxError(response, dialog)
{
    $(dialog).html(response.responseText);
    initializePage(dialog);
    preemptLinkActions(dialog);
    preemptMassActions(dialog);
    preemptFormSubmits(dialog);
}

function preemptLinkActions(dialog)
{
    $('a', $(dialog)).click(function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            url: $(this).prop('href'),
            beforeSend: function() { dialogAjaxInProgress(dialog); },
            error: function(response) { dialogAjaxError(response, dialog); },
            success: function(data) { dialogAjaxSuccess(data, dialog); }
        });
    });
}

function preemptMassActions(dialog)
{
    $('table.list tfoot button', $(dialog)).off('click').click(function() {
        executeMassAction(this, dialog);
    });
}

function preemptFormSubmits(dialog)
{
    $('form', $(dialog)).submit(function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            url: $(this).prop('action'),
            type: $(this).prop('method'),
            data: $(this).serialize(),
            beforeSend: function() { dialogAjaxInProgress(dialog); },
            error: function(response) { dialogAjaxError(response, dialog); },
            success: function(data) { dialogAjaxSuccess(data, dialog); }
        });
    });
}

$(document).ready(function() {
    initializePage(this);
});
