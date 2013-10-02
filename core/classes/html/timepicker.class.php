<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Input;

class TimePicker extends Input
{
    public function __construct($name = null, $value = null, $settings = null, $class = null, $id = null)
    {
        parent::__construct($name, $value, 'text', $class, $id);

        $this->addClass('timepicker');

        if ($settings !== null) {
            if (is_object($settings) || is_array($settings)) {
                $settings = json_encode($settings, JSON_FORCE_OBJECT);
            }

            $this->addCustomAttribute('data-settings', $settings);
        }
    }
}
