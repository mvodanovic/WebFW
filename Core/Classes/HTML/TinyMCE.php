<?php

namespace WebFW\Framework\Core\Classes\HTML;

class TinyMCE extends Textarea
{
    public function __construct($name = null, array $options = null, $value = null)
    {
        parent::__construct($name, $value);

        $this->addClass('tinymce');

        if ($options !== null) {
            $options = array();
        }

        $options = array_merge(array(
            'theme' => 'modern',
            'skin' => 'xenmce',
            'convert_fonts_to_spans' => true,
            'fix_list_elements' => true,
            'paste_as_text' => true,
            'browser_spellcheck' => true,
            'plugins' => 'advlist anchor autolink charmap code contextmenu emoticons fullscreen hr image link'
                . ' lists media paste preview searchreplace table textcolor visualblocks visualchars wordcount',
            'contextmenu' => 'undo redo | cut copy paste pastetext | link image inserttable | searchreplace formats',
            'menubar' => false,
            'tools' => 'inserttable',
            'toolbar_items_size' => 'small',
            'toolbar1' => 'bold italic underline strikethrough subscript superscript | alignleft aligncenter'
                . ' alignright alignjustify | bullist numlist outdent indent',
            'toolbar2' => 'formatselect | fontsizeselect | forecolor backcolor removeformat'
                . ' | fullscreen visualblocks visualchars',
            'toolbar3' => 'undo redo | hr emoticons preview searchreplace code'
                . ' | link unlink anchor image table blockquote media',
        ), $options);

        $this->setAttribute('data-options', json_encode($options, JSON_FORCE_OBJECT));
    }
}
