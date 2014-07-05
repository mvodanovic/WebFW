<?php

namespace mvodanovic\WebFW\CLI\Writer;

use mvodanovic\WebFW\Core\Classes\BaseClass;

class Style extends BaseClass implements iString
{
    const ESC = "\033[";
    const ESC_SEQ_PATTERN = "\033[%sm";

    const RESET = '0';
    const BOLD = '1';
    const DARK = '2';
    const ITALIC = '3';
    const UNDERLINE = '4';
    const BLINK = '5';
    const REVERSE = '7';
    const CONCEALED = '8';

    const VT_BLACK = '0';
    const VT_RED = '1';
    const VT_GREEN = '2';
    const VT_YELLOW = '3';
    const VT_BLUE = '4';
    const VT_MAGENTA = '5';
    const VT_CYAN = '6';
    const VT_WHITE = '7';
    const VT_DEFAULT = '9';

    const FG = '3';
    const BG = '4';

    protected $isReset = false;
    protected $isBold = false;
    protected $isDark = false;
    protected $isItalic = false;
    protected $isUnderline = false;
    protected $isBlink = false;
    protected $isReverse = false;
    protected $isConcealed = false;

    protected $fgColor = null;
    protected $bgColor = null;

    public function setReset($flag = true)
    {
        $this->isReset = (bool) $flag;
        if ($this->isReset) {
            $this->isBold = false;
            $this->isDark = false;
            $this->isItalic = false;
            $this->isUnderline = false;
            $this->isBlink = false;
            $this->isReverse = false;
            $this->isConcealed = false;
            $this->fgColor = null;
            $this->bgColor = null;
        }
        return $this;
    }

    public function setBold($flag = true)
    {
        $this->isBold = (bool) $flag;
        return $this;
    }

    public function setDark($flag = true)
    {
        $this->isDark = (bool) $flag;
        return $this;
    }

    public function setItalic($flag = true)
    {
        $this->isItalic = (bool) $flag;
        return $this;
    }

    public function setUnderline($flag = true)
    {
        $this->isUnderline = (bool) $flag;
        return $this;
    }

    public function setBlink($flag = true)
    {
        $this->isBlink = (bool) $flag;
        return $this;
    }

    public function setReverse($flag = true)
    {
        $this->isReverse = (bool) $flag;
        return $this;
    }

    public function setConcealed($flag = true)
    {
        $this->isConcealed = (bool) $flag;
        return $this;
    }

    public function setColor($fgColor = null, $bgColor = null)
    {
        $this->fgColor = $fgColor;
        $this->bgColor = $bgColor;
        return $this;
    }

    public function __toString()
    {
        $styleString = '';

        if ($this->isReset) {
            $styleString .= sprintf(static::ESC_SEQ_PATTERN, static::RESET);
        }

        if ($this->isBold) {
            $styleString .= sprintf(static::ESC_SEQ_PATTERN, static::BOLD);
        }

        $colorStrings = [];
        if ($this->fgColor) {
            $colorStrings[] = static::FG . $this->fgColor;
        }
        if ($this->bgColor) {
            $colorStrings[] = static::BG . $this->bgColor;
        }
        if (!empty($colorStrings)) {
            $styleString .= sprintf(static::ESC_SEQ_PATTERN, implode(';', $colorStrings));
        }
        return $styleString;
    }
}
