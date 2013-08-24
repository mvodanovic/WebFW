<?php

namespace WebFW\Core;

use WebFW\Externals\PHPTemplate;
use WebFW\Core\Framework;
use Config\Specifics\Data;

abstract class HTMLController extends Controller
{
    protected $baseTemplate = 'default';
    protected $pageTitle = '';
    protected $simpleOutput = false;
    protected $urlJS = array();
    protected $urlCSS = array();
    protected $htmlMeta = array();
    protected $customHtmlHead = '';

    public function __construct()
    {
        parent::__construct();

        $value = Data::GetItem('DEFAULT_BASE_TEMPLATE');
        if ($value !== null) {
            $this->baseTemplate = $value;
        }
    }

    public function getTitle()
    {
        return $this->pageTitle;
    }

    public function processOutput()
    {
        parent::processOutput();

        $htmlHead = $this->customHtmlHead;

        foreach ($this->urlJS as &$url) {
            $htmlHead .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
        }

        foreach ($this->urlCSS as &$data) {
            switch ($data['xhtml']) {
                case true:
                    $htmlHead .= '<link rel="stylesheet" type="text/css" href="' . $data['url'] . '" />' . "\n";
                    break;
                default:
                    $htmlHead .= '<link rel="stylesheet" type="text/css" href="' . $data['url'] . '">' . "\n";
                    break;
            }
        }

        /// key, content, keyType=name, scheme='', xhtml=true
        foreach ($this->htmlMeta as &$data) {
            if ($data['keyType'] !== 'name' && $data['keyType'] !== 'http-equiv') {
                continue;
            }

            if ($data['scheme'] !== '') {
                $data['scheme'] = ' scheme="' . $data['scheme'] . '"';
            }

            switch ($data['xhtml']) {
                case true:
                    $htmlHead .= '<meta ' . $data['keyType'] . '="' . $data['key'] . '" content="' . $data['content'] . '"' . $data['scheme'] . ' />' . "\n";
                    break;
                default:
                    $htmlHead .= '<meta ' . $data['keyType'] . '="' . $data['key'] . '" content="' . $data['content'] . '"' . $data['scheme'] . '>' . "\n";
                    break;
            }
        }

        if ($this->simpleOutput === false) {
            $templateDir = \WebFW\Config\BASE_TEMPLATE_PATH . DIRECTORY_SEPARATOR;

            try {
                $template = new PHPTemplate($this->baseTemplate . '.template.php', $templateDir);
            } catch (Exception $e) {
                throw new Exception('Base template missing: ' . $templateDir . $this->baseTemplate . '.template.php');
            }
            foreach ($this->templateVariables as $name => &$value) {
                $template->set($name, $value);
            }
            $template->set('htmlBody', $this->output);
            $template->set('htmlHead', $htmlHead);
            $template->set('pageTitle', $this->pageTitle);
            $template->set('controller', $this);

            $this->output = $template->fetch();
        }
    }

    protected function setLinkedJavaScript($url)
    {
        $this->urlJS[] = $url;
    }

    protected function setLinkedCSS($url, $xhtml = true)
    {
        $this->urlCSS[] = array(
            'url'    => $url,
            'xhtml'  => $xhtml === true ? true : false
        );
    }

    protected function setHtmlMeta($key, $content, $keyType = 'name', $scheme = '', $xhtml = true)
    {
        $this->htmlMeta[] = array(
            'key'       => $key,
            'content'   => $content,
            'keyType'   => strtolower($keyType) === 'name' ? 'name' : 'http-equiv',
            'scheme'    => $scheme,
            'xhtml'     => $xhtml === true ? true : false
        );
    }

    protected function setCustomHtmlHead($html)
    {
        $this->customHtmlHead = $html;
    }
}
