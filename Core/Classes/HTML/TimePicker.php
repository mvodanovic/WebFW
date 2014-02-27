<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

class TimePicker extends Input
{
    public function __construct($name = null, $value = null, $settings = null)
    {
        parent::__construct($name, Input::INPUT_TEXT, $value);

        $this->addClass('timepicker');

        if ($settings !== null) {
            if (is_object($settings) || is_array($settings)) {
                $settings = json_encode($settings, JSON_FORCE_OBJECT);
            }

            $this->setAttribute('data-settings', $settings);
        }
    }
}
