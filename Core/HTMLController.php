<?php

namespace WebFW\Framework\Core;

use WebFW\Framework\Core\Traits\tDualTemplated;
use WebFW\Framework\Externals\PHPTemplate;

abstract class HTMLController extends Controller
{
    use tDualTemplated;

    protected $pageTitle = '';
    protected $simpleOutput = false;
    protected $headHTML = array();
    protected $headJS = array();
    protected $headCSS = array();

    const DEFAULT_TEMPLATE_NAME = 'default';

    protected function __construct()
    {
        parent::__construct();

        if ($this->action !== static::DEFAULT_ACTION_NAME) {
            $this->template = $this->action;
        } else {
            $this->template = static::DEFAULT_TEMPLATE_NAME;
        }

        $templateDir = explode('\\', static::className());
        $templateDir = end($templateDir);
        $this->templateDirectory = \WebFW\Framework\Core\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir;

        $this->baseTemplate = static::DEFAULT_TEMPLATE_NAME;
        $this->baseTemplateDirectory = \WebFW\Framework\Core\BASE_TEMPLATE_PATH;
    }

    public function getTitle()
    {
        return $this->pageTitle;
    }

    public function processOutput()
    {
        parent::processOutput();

        if ($this->simpleOutput === false) {
            if ($this->pageTitle !== '') {
                $this->addHeadHTML('<title>' . $this->pageTitle . '</title>');
            }

            if (!empty($this->headCSS)) {
                $this->addHeadHTML(
                    "<style type=\"text/css\">\n"
                    . "/* <![CDATA[\n */"
                    . implode("\n", $this->headCSS) , "\n"
                    . "/* ]]> */\n"
                    . "</style>"
                );
            }

            if (!empty($this->headJS)) {
                $this->addHeadHTML(
                    "<script type=\"text/javascript\">\n"
                    . "// <![CDATA[\n"
                    . implode("\n", $this->headJS) . "\n"
                    . "// ]]>\n"
                    . "</script>"
                );
            }

            try {
                $template = new PHPTemplate($this->baseTemplate . '.template.php', $this->baseTemplateDirectory);
            } catch (Exception $e) {
                throw new Exception('Base template missing in controller ' . static::className(), 500, $e);
            }
            foreach ($this->templateVariables as $name => &$value) {
                $template->set($name, $value);
            }
            $template->set('htmlBody', $this->output);
            $template->set('htmlHead', implode("\n", $this->headHTML));
            $template->set('controller', $this);

            $this->output = $template->fetch();
        }
    }

    protected function addHeadHTML($html)
    {
        $this->headHTML[] = $html;
    }

    protected function addLinkedJS($url)
    {
        $url = htmlspecialchars($url);
        $this->addHeadHTML('<script type="text/javascript" src="' . $url . '"></script>');
    }

    protected function addHeadJS($js)
    {
        $this->headJS[] = $js;
    }

    protected function addLinkedCSS($url, $xhtml = true)
    {
        $url = htmlspecialchars($url);
        $closingTag = ($xhtml === true) ? '" />' : '">';
        $this->addHeadHTML('<link rel="stylesheet" type="text/css" href="' . $url . $closingTag);
    }

    protected function addHeadCSS($css)
    {
        $this->headCSS[] = $css;
    }

    protected function addHeadMeta($key, $content, $keyType = 'name', $scheme = '', $xhtml = true)
    {
        $keyType = htmlspecialchars($keyType);
        $key = htmlspecialchars($key);
        $content = htmlspecialchars($content);
        $scheme = ($scheme !== '') ? ' scheme="' . htmlspecialchars($scheme) . '"' : '';
        $closingTag = ($xhtml === true) ? ' />' : '>';
        $this->addHeadHTML('<meta ' . $keyType . '="' . $key . '" content="' . $content . '"' . $scheme . $closingTag);
    }
}
