<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseFormItem;

/**
 * Class Button
 *
 * A class for creating HTML buttons.
 *
 * @package WebFW\Core
 */
class Button extends BaseFormItem
{
    protected $tagName = 'button';
    protected $skipInnerHTMLDecoration = true;

    /**
     * @param string|null $caption Button caption
     * @param string|null $type Button's type attribute
     * @param string|object|array|null $jqueryUIOptions Parameters which will be given to jQuery UI button() function
     * @param string|null $class Custom class to apply to the button
     */
    public function __construct($caption = null, $type = null, $jqueryUIOptions = null, $class = null)
    {
        parent::__construct(null, $caption);

        if ($type !== null) {
            $this->addCustomAttribute('type', $type);
        }

        if ($jqueryUIOptions !== null) {
            if (is_array($jqueryUIOptions) || is_object($jqueryUIOptions)) {
                $jqueryUIOptions = json_encode($jqueryUIOptions, JSON_FORCE_OBJECT);
            }
            $this->addClass('jquery_ui_button');
            $this->addCustomAttribute('data-options', $jqueryUIOptions);
        }

        if ($class !== null) {
            $this->addClass($class);
        }
    }
}
