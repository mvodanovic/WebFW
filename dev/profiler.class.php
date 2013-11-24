<?php

namespace WebFW\Dev;

use WebFW\Core\Classes\BaseClass;
use WebFW\Externals\PHPTemplate;

/**
 * Class Profiler
 *
 * Used to monitor request execution from framework entry to final data output.
 * Monitors execution time and memory usage.
 *
 * @package WebFW\Dev
 */
class Profiler extends BaseClass
{
    protected static $instance = null;
    protected $moments = array();
    protected $startTime = null;
    protected $endTime = null;
    protected $templateDir;
    protected $templateName;

    /**
     * Returns the Profiler instance.
     *
     * @return Profiler
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Adds a new moment for which current data will be taken.
     *
     * @param string $description Description of the moment
     */
    public function addMoment($description)
    {
        $time = explode(' ', microtime());
        $time = $time[0] + ($time[1] % 10000);
        if ($this->startTime === null) {
            $this->startTime = $time;
        }
        $this->endTime = $time;

        $this->moments[] = array(
            'time' => number_format($time - $this->startTime, 6),
            'memory' => memory_get_usage(true),
            'description' => $description,
        );
    }

    /**
     * Calculates and returns total execution time.
     * Total time is calculated by subtracting time of the first moment from the time of the last moment.
     *
     * @return string The calculated time
     */
    public function getTotalTime()
    {
        return number_format($this->endTime - $this->startTime, 6);
    }

    /**
     * Return the list of moment definitions for all registered moments.
     * Each moment definition is represented with 'time', 'memory' and 'description'.
     *
     * @return array The list of moments
     */
    public function getMoments()
    {
        return $this->moments;
    }

    /**
     * Return the maximum amount of used memory while processing the request.
     *
     * @return int Peak memory usage
     */
    public function getPeakMemoryUsage()
    {
        return memory_get_peak_usage(true);
    }

    /**
     * Returns Profiler's HTML formatted output so it could be human-readable easily.
     *
     * @return string Profiler output in HTML
     */
    public function getHTMLOutput()
    {
        $template = new PHPTemplate($this->templateName, $this->templateDir);
        $template->set('profiler', $this);
        return $template->fetch();
    }

    protected function __construct()
    {
        $this->templateDir = \WebFW\Core\FW_PATH
            . DIRECTORY_SEPARATOR
            . 'dev'
            . DIRECTORY_SEPARATOR
            . 'templates'
            . DIRECTORY_SEPARATOR;
        $this->templateName = 'profiler.template.php';
    }

    private function __clone() {}
}
