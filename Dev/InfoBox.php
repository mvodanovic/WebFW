<?php

namespace mvodanovic\WebFW\Dev;

use mvodanovic\WebFW\Core\Classes\BaseClass;
use mvodanovic\WebFW\Externals\PHPTemplate;

/**
 * Class InfoBox
 *
 * Represents an info box used to display additional info on pages in dev mode.
 *
 * @package mvodanovic\WebFW
 */
class InfoBox extends BaseClass
{
    protected $content = null;
    protected $title = null;
    protected $dataRows = array();
    protected $templateDir;
    protected $templateName;

    public function __construct()
    {
        $this->templateDir = \mvodanovic\WebFW\Core\FW_PATH
            . DIRECTORY_SEPARATOR . 'Dev'
            . DIRECTORY_SEPARATOR . 'Templates';
        $this->templateName = 'infobox.template.php';
    }

    /**
     * Sets the box's inner content.
     *
     * @param string $content HTML-unescaped content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Sets the box's title.
     *
     * @param string $title The title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Adds a key-value pair to be displayed in the box.
     *
     * @param string $key The key under which to store the data; also the caption of the data
     * @param string $data The actual data
     */
    public function addData($key, $data)
    {
        $this->dataRows[$key] = $data;
    }

    /**
     * Returns HTML prepared to be displayed to the user.
     *
     * @return string The prepared HTML
     */
    public function parse()
    {
        $template = new PHPTemplate($this->templateName, $this->templateDir);
        $template->set('title', $this->title);
        $template->set('dataRows', $this->dataRows);
        $template->set('content', $this->content);
        return $template->fetch();
    }
}
