<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\helpers\VarDumper;
use yii\log\Logger;

/**
 * Debugger panel that collects and displays logs.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LogPanel extends Panel
{
    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Logs';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $target = $this->module->logTarget;
        $except = [];
        if (isset($this->module->panels['router'])) {
            $except = $this->module->panels['router']->getCategories();
        }
        
        $messages = $target->filterMessages($target->messages, Logger::LEVEL_ERROR | Logger::LEVEL_INFO | Logger::LEVEL_WARNING | Logger::LEVEL_TRACE, [], $except);
        foreach ($messages as &$message) {
            if (!is_string($message[0])) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($message[0] instanceof \Throwable || $message[0] instanceof \Exception) {
                    $message[0] = (string) $message[0];
                } else {
                    $message[0] = VarDumper::export($message[0]);
                }
            }
        }

        return ['messages' => $messages];
    }

    /**
     * Returns an array of models that represents logs of the current request.
     * Can be used with data providers, such as \yii\data\ArrayDataProvider.
     *
     * @param bool $refresh if need to build models from log messages and refresh them.
     * @return array models
     */
    protected function getModels($refresh = false)
    {
        if ($this->_models === null || $refresh) {
            $this->_models = [];

            foreach ($this->data['messages'] as $message) {
                $this->_models[] = [
                    'message' => $message[0],
                    'level' => $message[1],
                    'category' => $message[2],
                    'time' => $message[3] * 1000, // time in milliseconds
                    'trace' => isset($message[4]) ? $message[4] : [],
                ];
            }
        }

        return $this->_models;
    }
}
