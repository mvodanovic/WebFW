<?php

namespace WebFW\Core;

use \WebFW\Externals\PHPTemplate;
use \WebFW\Core\Framework;
use \Config\Specifics\Data;
use \ReflectionMethod;

abstract class Controller
{
    protected $template = 'default';
    protected $baseTemplate = 'default';
    protected $pageTitle = '';
    protected $useTemplate = true;
    protected $simpleOutput = false;
    protected $redirectUrl = null;
    protected $templateVariables = array();

    protected $_action;
    protected $_className;
    private $_urlJS = array();
    private $_urlCSS = array();
    private $_htmlMeta = array();
    private $_customHtmlHead = '';

    const DEFAULT_ACTION_NAME = 'execute';

    public function __construct()
    {
        $this->_action = $this->getDefaultActionName();

        $value = '';
        if (array_key_exists('action', $_REQUEST)) {
            $value = trim($_REQUEST['action']);
        }
        if ($value !== null && $value !== '') {
            $this->_action = $value;
        }

        $value = Data::GetItem('DEFAULT_CTL_TEMPLATE');
        if ($value !== null) {
            $this->template = $value;
        }

        $value = Data::GetItem('DEFAULT_BASE_TEMPLATE');
        if ($value !== null) {
            $this->baseTemplate = $value;
        }

        $this->_className = get_class($this);
    }

    final public function GetTitle()
    {
        return $this->pageTitle;
    }

    final public function Init()
    {
        $action = $this->_action;

        if (!method_exists($this, $action)) {
            $this->error404('Action not defined: ' . $action . ' (in controller ' . $this->_className . ')');
        }

        $reflection = new ReflectionMethod($this, $action);
        if (!$reflection->isPublic()) {
            $this->error404('Action not declared as public: ' . $action . ' (in controller ' . $this->_className . ')');
        }

        if ($reflection->isStatic()) {
            $this->error404('Action declared as static: ' . $action . ' (in controller ' . $this->_className . ')');
        }

        if ($this->_action !== $this->getDefaultActionName()) {
            $this->template = strtolower($action);
        }

        $this->$action();

        if ($this->redirectUrl !== null) {
            $this->setRedirectUrl($this->redirectUrl, true);
        }

        if ($this->useTemplate !== true) {
            return;
        }

        $templateDir = explode('\\', $this->_className);
        $templateDir = strtolower(end($templateDir));
        $templateDir = \WebFW\Config\CTL_TEMPLATE_PATH . DIRECTORY_SEPARATOR . $templateDir . DIRECTORY_SEPARATOR;

        try {
            $template = new PHPTemplate($this->template . '.template.php', $templateDir);
        } catch (Exception $e) {
            throw new Exception('Controller template missing: ' . $templateDir . $this->template . '.template.php');
        }
        foreach ($this->templateVariables as $name => &$value) {
            $template->set($name, $value);
        }

        $wFW_HtmlBody = $template->fetch();
        $wFW_HtmlHead = $this->_customHtmlHead;

        foreach ($this->_urlJS as &$url) {
            $wFW_HtmlHead .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
        }

        foreach ($this->_urlCSS as &$data) {
            switch ($data['xhtml']) {
                case true:
                    $wFW_HtmlHead .= '<link rel="stylesheet" type="text/css" href="' . $data['url'] . '" />' . "\n";
                    break;
                default:
                    $wFW_HtmlHead .= '<link rel="stylesheet" type="text/css" href="' . $data['url'] . '">' . "\n";
                    break;
            }
        }

        /// key, content, keyType=name, scheme='', xhtml=true
        foreach ($this->_htmlMeta as &$data) {
            if ($data['keyType'] !== 'name' && $data['keyType'] !== 'http-equiv') {
                continue;
            }

            if ($data['scheme'] !== '') {
                $data['scheme'] = ' scheme="' . $data['scheme'] . '"';
            }

            switch ($data['xhtml']) {
                case true:
                    $wFW_HtmlHead .= '<meta ' . $data['keyType'] . '="' . $data['key'] . '" content="' . $data['content'] . '"' . $data['scheme'] . ' />' . "\n";
                    break;
                default:
                    $wFW_HtmlHead .= '<meta ' . $data['keyType'] . '="' . $data['key'] . '" content="' . $data['content'] . '"' . $data['scheme'] . '>' . "\n";
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
            $template->set('wFW_HtmlBody', $wFW_HtmlBody);
            $template->set('wFW_HtmlHead', $wFW_HtmlHead);
            $template->set('wFW_PageTitle', $this->pageTitle);
            $template->set('wFW_Controller', $this);

            $wFW_HtmlBody = $this->doctype . $template->fetch();
        }

        echo $wFW_HtmlBody;
    }

    final protected function setLinkedJavaScript($url)
    {
        $this->_urlJS[] = $url;
    }

    final protected function setLinkedCSS($url, $xhtml = true)
    {
        $this->_urlCSS[] = array(
            'url'    => $url,
            'xhtml'  => $xhtml === true ? true : false
        );
    }

    final protected function setHtmlMeta($key, $content, $keyType = 'name', $scheme = '', $xhtml = true)
    {
        $this->_htmlMeta[] = array(
            'key'       => $key,
            'content'   => $content,
            'keyType'   => strtolower($keyType) === 'name' ? 'name' : 'http-equiv',
            'scheme'    => $scheme,
            'xhtml'     => $xhtml === true ? true : false
        );
    }

    final protected function setCustomHtmlHead($html)
    {
        $this->_customHtmlHead = $html;
    }

    protected function error404($debugMessage = '404 Not Found')
    {
        Framework::Error404($debugMessage);
    }

    final protected function SetTplVar($name, $value)
    {
        $this->templateVariables[$name] = $value;
    }

    protected function setRedirectUrl($url, $doRedirectNow = false)
    {
        if ($doRedirectNow === true) {
            if (array_key_exists('redirect_debug', $_REQUEST) && $_REQUEST['redirect_debug'] == 1) {
                trigger_error('Redirect: ' . $url);
            }
            header('Location: ' . $url);
            die;
        }

        $this->redirectUrl = $url;
    }

    protected function getDefaultActionName()
    {
        $action = Data::GetItem('DEFAULT_CTL_ACTION');
        if ($action === null || $action === '') {
            $action = static::DEFAULT_ACTION_NAME;
        }

        return $action;
    }
}
