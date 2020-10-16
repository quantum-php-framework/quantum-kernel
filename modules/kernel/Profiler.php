<?php

namespace Quantum;

/**
 * Class Profiler
 * @package Quantum
 */
class Profiler
{

    /**
     * Timers for code profiling
     *
     * @var array
     */
    static private $_timers = array();
    /**
     * @var bool
     */
    static private $_enabled = false;
    /**
     * @var bool
     */
    static private $_memory_get_usage = false;

    /**
     *
     */
    public static function enable()
    {
        self::$_enabled = true;
        self::$_memory_get_usage = function_exists('memory_get_usage');
    }

    public static function enableIfProfilerFileExists()
    {
        if (file_exists(__DIR__.'/../../../../local/var/locks/profiler.lock'))
            self::$_enabled = true;
        else
            self::$_enabled = false;

        self::$_memory_get_usage = function_exists('memory_get_usage');
    }

    /**
     *
     */
    public static function disable()
    {
        self::$_enabled = false;
    }

    /**
     * @param $timerName
     */
    public static function reset($timerName)
    {
        self::$_timers[$timerName] = array(
            'start'=>false,
            'count'=>0,
            'sum'=>0,
            'realmem'=>0,
            'emalloc'=>0,
        );
    }

    /**
     * @param $timerName
     */
    public static function resume($timerName)
    {
        if (!self::$_enabled) {
            return;
        }

        if (empty(self::$_timers[$timerName])) {
            self::reset($timerName);
        }
        if (self::$_memory_get_usage) {
            self::$_timers[$timerName]['realmem_start'] = memory_get_usage(true);
            self::$_timers[$timerName]['emalloc_start'] = memory_get_usage();
        }
        self::$_timers[$timerName]['start'] = microtime(true);
        self::$_timers[$timerName]['count'] ++;
    }

    /**
     * @param $timerName
     */
    public static function start($timerName)
    {
        if (!self::$_enabled) {
            return;
        }

        self::resume($timerName);
    }

    /**
     * @param $timerName
     */
    public static function pause($timerName)
    {
        if (!self::$_enabled) {
            return;
        }

        $time = microtime(true); // Get current time as quick as possible to make more accurate calculations

        if (empty(self::$_timers[$timerName])) {
            self::reset($timerName);
        }
        if (false!==self::$_timers[$timerName]['start']) {
            self::$_timers[$timerName]['sum'] += $time-self::$_timers[$timerName]['start'];
            self::$_timers[$timerName]['start'] = false;
            if (self::$_memory_get_usage) {
                self::$_timers[$timerName]['realmem'] += memory_get_usage(true)-self::$_timers[$timerName]['realmem_start'];
                self::$_timers[$timerName]['emalloc'] += memory_get_usage()-self::$_timers[$timerName]['emalloc_start'];
            }
        }
    }

    /**
     * @param $timerName
     */
    public static function stop($timerName)
    {
        if (!self::$_enabled) {
            return;
        }

        self::pause($timerName);
    }

    /**
     * @param $timerName
     * @param string $key
     * @return bool|mixed
     */
    public static function fetch($timerName, $key='sum')
    {
        if (empty(self::$_timers[$timerName])) {
            return false;
        } elseif (empty($key)) {
            return self::$_timers[$timerName];
        }
        switch ($key) {
            case 'sum':
                $sum = self::$_timers[$timerName]['sum'];
                if (self::$_timers[$timerName]['start']!==false) {
                    $sum += microtime(true)-self::$_timers[$timerName]['start'];
                }
                return $sum;

            case 'count':
                $count = self::$_timers[$timerName]['count'];
                return $count;

            case 'realmem':
                if (!isset(self::$_timers[$timerName]['realmem'])) {
                    self::$_timers[$timerName]['realmem'] = -1;
                }
                return self::$_timers[$timerName]['realmem'];

            case 'emalloc':
                if (!isset(self::$_timers[$timerName]['emalloc'])) {
                    self::$_timers[$timerName]['emalloc'] = -1;
                }
                return self::$_timers[$timerName]['emalloc'];

            default:
                if (!empty(self::$_timers[$timerName][$key])) {
                    return self::$_timers[$timerName][$key];
                }
        }
        return false;
    }

    /**
     * @return array
     */
    public static function getTimers()
    {
        return self::$_timers;
    }

    /**
     * Output SQl Zend_Db_Profiler
     *
     */
    public static function getSqlProfiler($res) {
        if(!$res){
            return '';
        }
        $out = '';
        $profiler = $res->getProfiler();
        if($profiler->getEnabled()) {
            $totalTime    = $profiler->getTotalElapsedSecs();
            $queryCount   = $profiler->getTotalNumQueries();
            $longestTime  = 0;
            $longestQuery = null;

            foreach ($profiler->getQueryProfiles() as $query) {
                if ($query->getElapsedSecs() > $longestTime) {
                    $longestTime  = $query->getElapsedSecs();
                    $longestQuery = $query->getQuery();
                }
            }

            $out .= 'Executed ' . $queryCount . ' queries in ' . $totalTime . ' seconds' . "<br>";
            $out .= 'Average query length: ' . $totalTime / $queryCount . ' seconds' . "<br>";
            $out .= 'Queries per second: ' . $queryCount / $totalTime . "<br>";
            $out .= 'Longest query length: ' . $longestTime . "<br>";
            $out .= 'Longest query: <br>' . $longestQuery . "<hr>";
        }
        return $out;
    }

    /**
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$_enabled;
    }

    /**
     * @return bool|string
     */
    public static function toHtml()
    {
        if (!self::$_enabled)
            return false;

        $timers = self::$_timers;

        #$out = '<div style="position:fixed;bottom:5px;right:5px;opacity:.1;background:white" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.1">';
        #$out = '<div style="opacity:.1" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=.1">';
        $out = "<a href=\"javascript:void(0)\" onclick=\"\$('profiler_section').style.display=\$('profiler_section').style.display==''?'none':''\">[profiler]</a>";
        $out .= '<div id="profiler_section" style="background:white; display:block">';
        $out .= '<pre>Memory usage: real: ' .  Utilities::formatBytes(memory_get_usage(true)) . ', emalloc: ' .  Utilities::formatBytes(memory_get_usage()) . '</pre>';
        $out .= '<table border="1" cellspacing="0" cellpadding="2" style="width:auto">';
        $out .= '<tr><th>Code Profiler</th><th>Time</th><th>Count</th><th>Emalloc</th><th>RealMem</th></tr>';
        foreach ($timers as $name => $timer) {

            $sum = self::fetch($name, 'sum');
            $count = self::fetch($name, 'count');
            $realmem = self::fetch($name, 'realmem');
            $emalloc = self::fetch($name, 'emalloc');

            $out .= '<tr>' . '<td align="left">' . $name . '</td>' . '<td>' . number_format($sum, 16) . '</td>' . '<td align="right">' . $count . '</td>' . '<td align="right">' . Utilities::formatBytes ($emalloc) . '</td>' . '<td align="right">' .  Utilities::formatBytes($realmem) . '</td>' . '</tr>';

        }
        $out .= '</table>';
        /*
        $out .= '<pre>';
        $out .= print_r(self::getSqlProfiler(Mage::getSingleton('kernel/resource')->getConnection('core_write')), 1);
        $out .= '</pre>';
        */
        $out .= '</div>';
        return $out;
    }
}