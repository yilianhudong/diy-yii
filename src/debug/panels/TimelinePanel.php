<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\debug\Panel;
use yii\base\InvalidConfigException;

/**
 * Debugger panel that collects and displays timeline data.
 *
 * @property array $colors
 * @property float $duration This property is read-only.
 * @property float $start This property is read-only.
 * @property array $svgOptions
 *
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0.7
 */
class TimelinePanel extends Panel
{
    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;
    /**
     * @var float Start request, timestamp (obtained by microtime(true))
     */
    private $_start;
    /**
     * @var float End request, timestamp (obtained by microtime(true))
     */
    private $_end;
    /**
     * @var float Request duration, milliseconds
     */
    private $_duration;
    /**
     * @var int Used memory in request
     */
    private $_memory;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!isset($this->module->panels['profiling'])) {
            throw new InvalidConfigException('Unable to determine the profiling panel');
        }
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Timeline';
    }

    /**
     * {@inheritdoc}
     */
    public function load($data)
    {
        if (!isset($data['start']) || empty($data['start'])) {
            throw new \RuntimeException('Unable to determine request start time');
        }
        $this->_start = $data['start'] * 1000;

        if (!isset($data['end']) || empty($data['end'])) {
            throw new \RuntimeException('Unable to determine request end time');
        }
        $this->_end = $data['end'] * 1000;

        if (isset($this->module->panels['profiling']->data['time'])) {
            $this->_duration = $this->module->panels['profiling']->data['time'] * 1000;
        } else {
            $this->_duration = $this->_end - $this->_start;
        }

        if ($this->_duration <= 0) {
            throw new \RuntimeException('Duration cannot be zero');
        }

        if (!isset($data['memory']) || empty($data['memory'])) {
            throw new \RuntimeException('Unable to determine used memory in request');
        }
        $this->_memory = $data['memory'];
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return [
            'start' => YII_BEGIN_TIME,
            'end' => microtime(true),
            'memory' => memory_get_peak_usage(),
        ];
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
            if (isset($this->module->panels['profiling']->data['messages'])) {
                $this->_models = Yii::getLogger()->calculateTimings($this->module->panels['profiling']->data['messages']);
            }
        }
        return $this->_models;
    }
}
