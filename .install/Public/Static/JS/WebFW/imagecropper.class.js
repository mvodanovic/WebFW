function ImageCropper(DOMElement) {
    this.container = $(DOMElement);
    this.fullImage = $('img.full', this.container);
    this.previewImage = $('img.preview', this.container);
    this.errorTooltip = $('.tooltip', this.container);
    this.inputX = $('input.x', this.container);
    this.inputY = $('input.y', this.container);
    this.inputF = $('input.f', this.container);
    this.jCropInstance = null;
    this.editorBoxWidth = this.container.data('editor-box-width');
    this.editorBoxHeight = this.container.data('editor-box-height');
    this.thumbnailWidth = this.container.data('thumbnail-width');
    this.thumbnailHeight = this.container.data('thumbnail-height');
    this.ratioWidth = this.container.data('ratio-width');
    this.ratioHeight = this.container.data('ratio-height');
    this.imageWidth = this.container.data('image-width');
    this.imageHeight = this.container.data('image-height');
    this.crop = this.container.data('crop');
    this.buttonIcons = null;
    this.buttonLabel = null;

    this.initializeCropper();
}

ImageCropper.prototype.initializeCropper = function() {
    var cropperInstance = this;
    $('span', this.errorTooltip).hide();

    this.fullImage.Jcrop({
        boxWidth: this.editorBoxWidth,
        boxHeight: this.editorBoxHeight,
        aspectRatio: this.ratioWidth / this.ratioHeight,
        setSelect: [this.crop.x, this.crop.y, this.crop.x + this.crop.w, this.crop.y + this.crop.h],
        onSelect: function(coords) { cropperInstance.showPreview(coords); },
        onChange: function(coords) { cropperInstance.showPreview(coords); }
    }, function() {
        cropperInstance.jCropInstance = this;
    });

    $('.thumbnail_block', this.container).css({
        width: this.thumbnailWidth + 'px',
        height: this.thumbnailHeight + 'px'
    });

    $('input', this.container).change(function() {
        cropperInstance.jCropInstance.setSelect([
            cropperInstance.inputX.val(),
            cropperInstance.inputY.val(),
            Math.round(cropperInstance.inputF.val() / cropperInstance.ratioWidth),
            Math.round(cropperInstance.inputF.val() / cropperInstance.ratioWidth * cropperInstance.thumbnailHeight / cropperInstance.thumbnailWidth)
        ]);
    });

    $('form', this.container).submit(function(e) {
        e.preventDefault();
        cropperInstance.showMessageSaveInProgress();
        $.ajax({
            url: $(this).prop('action'),
            data: $(this).serialize() + '&ajax=1',
            type: $(this).prop('method'),
            success: function (e) { cropperInstance.saveResponse(e); },
            error: function (e) { cropperInstance.saveResponse(e); }
        });
    });
};

ImageCropper.prototype.showPreview = function(coords) {
    var rx = this.thumbnailWidth / coords.w;
    var ry = this.thumbnailHeight / coords.h;

    this.previewImage.css({
        width: Math.round(rx * this.imageWidth) + 'px',
        height: Math.round(ry * this.imageHeight) + 'px',
        marginLeft: '-' + Math.round(rx * coords.x) + 'px',
        marginTop: '-' + Math.round(ry * coords.y) + 'px'
    });
    this.inputX.val(coords.x);
    this.inputY.val(coords.y);
    this.inputF.val(this.ratioWidth * coords.w);
};

ImageCropper.prototype.showMessageSaveInProgress = function() {
    if (this.buttonIcons === null) {
        this.buttonIcons = $('button', this.container).button('option', 'icons');
    }
    if (this.buttonLabel === null) {
        this.buttonLabel = $('button', this.container).button('option', 'label');
    }

    $('button', this.container).button('option', 'icons', { primary: 'ui-icon-clock' });
    $('button', this.container).button('option', 'label', 'Saving...');
    this.errorTooltip.attr('data-text', '');
    $('span', this.errorTooltip).hide();
};

ImageCropper.prototype.saveResponse = function(data) {
    this.resetButton();
    if (data.status !== 'OK') {
        this.errorTooltip.attr('data-text', data.statusText);
        $('span', this.errorTooltip).show();
    }
};

ImageCropper.prototype.resetButton = function() {
    $('button', this.container).button('option', 'icons', this.buttonIcons);
    $('button', this.container).button('option', 'label', this.buttonLabel);
};
