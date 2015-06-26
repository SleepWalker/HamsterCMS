<?php
/**
 * LogContext class is a filter that adds some context info to logged messages.
 * The only difference from default CLogger is that the context in bound directly
 * to messages
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @copyright  Copyright &copy; 2012 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

namespace hamster\components;

class LogContext extends \CLogFilter
{
    /**
     * Filters the given log messages.
     * This is the main method of CLogFilter. It processes the log messages
     * by adding context information, etc.
     * @param array $logs the log messages
     * @return array
     */
    public function filter(&$logs)
    {
        if (!empty($logs)) {
            if (($message = $this->getContext()) !== '') {
                foreach ($logs as &$log) {
                    $log[0] .= "\n".$message."\n---\n";
                }
            }

            $this->format($logs);
        }
        return $logs;
    }
}
