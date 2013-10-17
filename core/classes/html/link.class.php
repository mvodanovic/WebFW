<?php

namespace WebFW\Core\Classes\HTML;

use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;

/**
 * Class Link
 *
 * A class for creating HTML links (anchors).
 *
 * @package WebFW\Core
 */
class Link extends BaseHTMLItem
{
    protected $tagName = 'a';

    /**
     * @var string If an URL is not specified, this will be used as a default URL.
     */
    protected $defaultURL = 'javascript:void(0)';

    /**
     * The constructor.
     *
     * If $jqueryUIOptions is set, the link will be converted to a jQuery UI button.
     * $jqueryUIOptions can be an array, an object or a JSON string.
     *
     * @param string|null $caption Link caption
     * @param string|null $url The URL which will be set as the href attribute of the link
     * @param string|object|array|null $jqueryUIOptions Parameters which will be given to jQuery UI button() function
     * @param string|null $class Custom class to apply to the button
     */
    public function __construct($caption = null, $url = null, $jqueryUIOptions = null, $class = null)
    {
        parent::__construct($caption);

        if ($url === null) {
            $url = $this->defaultURL;
        }
        $this->addCustomAttribute('href', $url);

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
