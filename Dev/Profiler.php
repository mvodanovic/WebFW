<?php

namespace WebFW\Framework\Dev;

use WebFW\Framework\Core\Classes\BaseClass;
use WebFW\Framework\Core\Config;
use WebFW\Framework\Core\Exception;
use WebFW\Framework\Externals\PHPTemplate;

/**
 * Class Profiler
 *
 * Used to monitor request execution from framework entry to final data output.
 * Monitors execution time and memory usage.
 *
 * @package WebFW\Framework\Dev
 */
class Profiler extends BaseClass
{
    protected static $instance = null;
    protected static $class = null;

    protected $moments = array();
    protected $queries = array();
    protected $startTime = null;
    protected $endTime = null;
    protected $templateDir;
    protected $templateName;

    /**
     * Returns the Profiler instance.
     *
     * @return Profiler
     * @throws Exception if an invalid profilerClass is set in config
     */
    public static function getInstance()
    {
        if (static::$class === null) {
            static::$class = static::className();
        }

        $className = Config::get('Developer', 'profilerClass');
        if ($className === null) {
            $className = Profiler::className();
        } elseif (!is_subclass_of($className, Profiler::className())) {
            throw new Exception($className . ' not an instance of ' . Profiler::className());
        }

        if (static::$instance === null) {
            static::$instance = new $className();
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
        $time = $this->getTime();
        if ($this->startTime === null) {
            $this->startTime = $time;
        }
        $this->endTime = $time;

        $this->moments[] = array(
            'time' => $this->formatTime($time - $this->startTime),
            'memory' => memory_get_usage(true),
            'queryCount' => $this->getQueryCount(),
            'description' => $description,
        );
    }

    /**
     * Adds a new query to the list of executed queries
     *
     * @param string $query The query
     */
    public function addQuery($query)
    {
        $this->queries[] = array(
            'query' => $query,
            'time' => $this->getTime(),
        );
    }

    public function completeQuery()
    {
        $queryDef = array_pop($this->queries);
        $queryDef['time'] = $this->formatTime($this->getTime() - $queryDef['time']);
        $this->queries[] = $queryDef;
    }

    /**
     * Calculates and returns total execution time.
     * Total time is calculated by subtracting time of the first moment from the time of the last moment.
     *
     * @return string The calculated time
     */
    public function getTotalTime()
    {
        return $this->formatTime($this->endTime - $this->startTime);
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
     * Return the list of executed queries.
     *
     * @return array The list of queries
     */
    public function getQueries()
    {
        return $this->queries;
    }

    /**
     * Returns the total number of executed queries.
     *
     * @return int The number of executed queries
     */
    public function getQueryCount()
    {
        return count($this->queries);
    }

    /**
     * Returns Profiler's HTML formatted output so it could be human-readable easily.
     *
     * @return string Profiler output in HTML
     */
    public function getHTMLOutput()
    {
        $template = new PHPTemplate($this->templateName, $this->templateDir);
        return $template->fetch();
    }

    protected function getTime()
    {
        $time = explode(' ', microtime());
        return $time[0] + ($time[1] % 10000);
    }

    protected function formatTime($time)
    {
        return number_format($time, 6);
    }

    protected function __construct()
    {
        $this->templateDir = \WebFW\Framework\Core\FW_PATH
            . DIRECTORY_SEPARATOR . 'Dev'
            . DIRECTORY_SEPARATOR . 'Templates';
        $this->templateName = 'profiler.template.php';
    }

    private function __clone() {}
}
