<?php

namespace WebFW\Core\Classes\HTML;

class DatePicker extends Input
{
    public function __construct($name = null, $value = null, $settings = null, $class = null)
    {
        parent::__construct($name, 'text', $value, $class);

        $this->addClass('datepicker');

        if ($settings !== null) {
            if (is_object($settings) || is_array($settings)) {
                $settings = json_encode($settings, JSON_FORCE_OBJECT);
            }

            $this->addCustomAttribute('data-settings', $settings);
        }
    }
}
