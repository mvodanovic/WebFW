<?php

namespace mvodanovic\WebFW\Core\Classes\HTML;

use mvodanovic\WebFW\Core\Classes\HTML\Base\SimpleFormItem;

/**
 * Class Button
 *
 * A class for creating HTML buttons.
 *
 * @package mvodanovic\WebFW
 */
class Button extends SimpleFormItem
{
    const BUTTON_BUTTON = 'button';
    const BUTTON_SUBMIT = 'submit';
    const BUTTON_RESET = 'reset';

    protected $type = null;

    /**
     * @param string|null $caption Button caption
     * @param string|null $type Button's type attribute
     * @param string|object|array|null $jqueryUIOptions Parameters which will be given to jQuery UI button() function
     */
    public function __construct($caption = null, $type = self::BUTTON_BUTTON, $jqueryUIOptions = null)
    {
        $this->type = $type;
        $this->setInnerHTML(htmlspecialchars($caption));

        parent::__construct(static::TYPE_BUTTON);

        if ($jqueryUIOptions !== null) {
            if (is_array($jqueryUIOptions) || is_object($jqueryUIOptions)) {
                $jqueryUIOptions = json_encode($jqueryUIOptions, JSON_FORCE_OBJECT);
            }
            $this->addClass('jquery_ui_button');
            $this->setAttribute('data-options', $jqueryUIOptions);
        }
    }

    public function parse()
    {
        $this->setAttribute('type', $this->type);

        return parent::parse();
    }
}
