<?php

namespace WebFW\Framework\Core\Classes\HTML;

use WebFW\Framework\Core\Classes\HTML\Base\GeneralHTMLItem;

/**
 * Class Link
 *
 * A class for creating HTML links (anchors).
 *
 * @package WebFW\Framework\Core
 */
class Link extends GeneralHTMLItem
{
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
     */
    public function __construct($caption = null, $url = null, $jqueryUIOptions = null)
    {
        if ($url === null) {
            $url = $this->defaultURL;
        }
        $this->setAttribute('href', $url);

        if ($jqueryUIOptions !== null) {
            if (is_array($jqueryUIOptions) || is_object($jqueryUIOptions)) {
                $jqueryUIOptions = json_encode($jqueryUIOptions, JSON_FORCE_OBJECT);
            }
            $this->addClass('jquery_ui_button');
            $this->setAttribute('data-options', $jqueryUIOptions);
        }

        parent::__construct('a');
    }
}
