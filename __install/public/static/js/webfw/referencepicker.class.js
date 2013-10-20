/**
 * Prototype for the ReferencePicker class.
 *
 * @param element The block element to set up as a ReferencePicker
 * @param dialogTitle Title of the dialog opened by the ReferencePicker
 * @constructor
 */
function ReferencePicker(element, dialogTitle) {
    this.element = element;
    this.dialogTitle = dialogTitle;
    this.dialog = null;

    this.url = $(this.element).data('url');
    if (this.url == undefined || this.url == '') {
        $(this.element).html(CMSPage.createErrorMessage("The element hasn't got an URL specified!"));
        return;
    }

    this.fieldName = $(this.element).data('field-name');
    if (this.fieldName == undefined || this.fieldName == '') {
        $(this.element).html(CMSPage.createErrorMessage("The element hasn't got a field name specified!"));
        return;
    }

    this.values = {};

    this.captionDisplay = $('<span/>').addClass('caption').css('display', 'inline-block').css('margin-right', '.3em');

    this.selectButton = $('<button/>').prop('type', 'button').data('options', {
        icons: {primary: 'ui-icon-pencil'},
        text: false
    }).addClass('jquery_ui_button').click({instance: this}, function(e) {
        e.data.instance.openDialog();
    }).css('margin-right', '.3em');

    this.clearButton = $('<button/>').prop('type', 'button').data('options', {
        icons: {primary: 'ui-icon-close'},
        text: false
    }).addClass('jquery_ui_button').click({instance: this}, function(e) {
        e.data.instance.clearData();
    });

    $(this.element).html(this.captionDisplay).append(this.selectButton).append(this.clearButton);
    this.updateData($(this.element).data('values'), $(this.element).data('caption'));

    if (Object.keys(this.values).length == 0) {
        this.clearButton.hide();
    }
}

/**
 * Initializes & displays the dialog.
 *
 * @internal
 */
ReferencePicker.prototype.openDialog = function() {
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();

    this.dialog = $('<div/>').data('owner', this).dialog({
        title: this.dialogTitle,
        modal: true,
        draggable: false,
        resizable: false,
        width: Math.round(windowWidth < 640 ? windowWidth - 10 : windowWidth * 0.9),
        height: Math.round(windowHeight < 480 ? windowHeight - 10 : windowHeight * 0.9),
        create: function() { $(this).data('owner').fetchDialogData(); },
        close: function() { $(this).data('owner').deleteDialog(); }
    });
}

/**
 * Fetches the dialog's content using an Ajax request.
 *
 * @internal
 */
ReferencePicker.prototype.fetchDialogData = function() {
    $.ajax({
        url: this.url,
        instance: this,
        beforeSend: function() { this.instance.dialogAjaxInProgress(); },
        error: function(response) { this.instance.dialogAjaxError(response); },
        success: function(data) { this.instance.dialogAjaxSuccess(data); }
    });
}

/**
 * Removes dialog's reference in the instance and in the DOM.
 *
 * @internal
 */
ReferencePicker.prototype.deleteDialog = function() {
    $(this.dialog).dialog('destroy').remove();
    this.dialog = null;
}

/**
 * Adds the "Please wait" message to the dialog to be shown while the actual data is loading.
 *
 * @internal
 */
ReferencePicker.prototype.dialogAjaxInProgress = function() {
    $(this.dialog).html('Please wait...').append($('<div/>').progressbar({value: false}));
}

/**
 * If an error occurs while loading the dialog's data, this handler is called.
 *
 * @param response The response object containing info on the error
 * @internal
 */
ReferencePicker.prototype.dialogAjaxError = function(response) {
    $(this.dialog).html(response.responseText);
    new CMSPage(this.dialog);
    this.preemptLinkActions();
    this.preemptFormSubmits();
}

/**
 * Handler called on successful load of the dialog's data.
 *
 * @param data The data returned from the server
 * @internal
 */
ReferencePicker.prototype.dialogAjaxSuccess = function(data) {
    $(this.dialog).html(data);
    new CMSPage(this.dialog);
    this.preemptLinkActions();
    this.preemptFormSubmits();
}

/**
 * Preempts all links in the dialog so their actions are bound only to the dialog, and not the whole page.
 * Instead of links opening new pages in the browser, they open them in the dialog using Ajax calls.
 *
 * @internal
 */
ReferencePicker.prototype.preemptLinkActions = function() {
    $('a', $(this.dialog)).click({instance: this}, function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            url: $(this).prop('href'),
            instance: e.data.instance,
            beforeSend: function() { this.instance.dialogAjaxInProgress(); },
            error: function(response) { this.instance.dialogAjaxError(response); },
            success: function(data) { this.instance.dialogAjaxSuccess(data); }
        });
    });
}

/**
 * Preempts all forms in the dialog so they are bound only to the dialog, and not the whole page.
 * Instead of forms being submitted with a redirect to a new page, they are submitted via Ajax calls.
 *
 * @internal
 */
ReferencePicker.prototype.preemptFormSubmits = function ()
{
    $('form', $(this.dialog)).submit({instance: this}, function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();

        $.ajax({
            url: $(this).prop('action'),
            type: $(this).prop('method'),
            data: $(this).serialize(),
            instance: e.data.instance,
            beforeSend: function() { this.instance.dialogAjaxInProgress(); },
            error: function(response) { this.instance.dialogAjaxError(response); },
            success: function(data) { this.instance.dialogAjaxSuccess(data); }
        });
    });
}

/**
 * Updates form data with data selected by the dialog.
 *
 * @param primaryKey A list of key-value pairs to add to the form
 * @param caption A caption to display which describes the selected item
 * @internal
 */
ReferencePicker.prototype.updateData = function(primaryKey, caption) {
    if (primaryKey == undefined) {
        return;
    }

    if (caption == undefined || caption == '') {
        caption = JSON.stringify(primaryKey);
    }

    for (var key in primaryKey) {
        if (key in this.values) {
            this.values[key].val(primaryKey[key]);
        } else {
            this.values[key] = $('<input/>').prop('type', 'hidden')
                .prop('name', this.fieldName + '[' + key + ']').val(primaryKey[key]);
            $(this.element).append(this.values[key]);
        }
    }

    this.captionDisplay.html(caption);

    if (Object.keys(this.values).length > 0) {
        this.clearButton.show();
    } else {
        this.clearButton.hide();
    }
}

/**
 * Clears all previously selected data and resets the caption.
 *
 * @internal
 */
ReferencePicker.prototype.clearData = function() {
    this.captionDisplay.html('');
    for (var i in this.values) {
        this.values[i].remove();
    }
    this.values = {};
    this.clearButton.hide();
}
