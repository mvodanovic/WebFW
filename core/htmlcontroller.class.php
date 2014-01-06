<?php

namespace WebFW\Core;

use WebFW\Externals\PHPTemplate;

abstract class HTMLController extends TemplatedController
{
    protected $baseTemplate = 'default';
    protected $pageTitle = '';
    protected $simpleOutput = false;
    protected $headHTML = array();
    protected $headJS = array();
    protected $headCSS = array();

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

            $templateDir = \WebFW\Core\BASE_TEMPLATE_PATH . DIRECTORY_SEPARATOR;

            try {
                $template = new PHPTemplate($this->baseTemplate . '.template.php', $templateDir);
            } catch (Exception $e) {
                throw new Exception('Base template missing: ' . $templateDir . $this->baseTemplate . '.template.php');
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
