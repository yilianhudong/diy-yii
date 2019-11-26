<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\InvalidConfigException;
use yii\debug\Panel;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

/**
 * Debugger panel that collects and displays database queries performed.
 *
 * @property array $profileLogs This property is read-only.
 * @property string $summaryName Short name of the panel, which will be use in summary. This property is
 * read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbPanel extends Panel
{
    /**
     * @var string the name of the database component to use for executing (explain) queries
     */
    public $db = 'db';

    /**
     * @var array current database request timings
     */
    private $_timings;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->actions['db-explain'] = [
            'class' => 'yii\\debug\\actions\\db\\ExplainAction',
            'panel' => $this,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Database';
    }

    /**
     * Calculates given request profile timings.
     *
     * @return array timings [token, category, timestamp, traces, nesting level, elapsed time]
     */
    public function calculateTimings()
    {
        if ($this->_timings === null) {
            $this->_timings = Yii::getLogger()->calculateTimings(isset($this->data['messages']) ? $this->data['messages'] : []);
        }

        return $this->_timings;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return ['messages' => $this->getProfileLogs()];
    }

    /**
     * Returns all profile logs of the current request for this panel. It includes categories such as:
     * 'yii\db\Command::query', 'yii\db\Command::execute'.
     * @return array
     */
    public function getProfileLogs()
    {
        $target = $this->module->logTarget;

        return $target->filterMessages($target->messages, Logger::LEVEL_PROFILE, ['yii\db\Command::query', 'yii\db\Command::execute']);
    }

    /**
     * Return associative array, where key is query string
     * and value is number of occurrences the same query in array.
     *
     * @param $timings
     * @return array
     * @since 2.0.13
     */
    public function countDuplicateQuery($timings)
    {
        $query = ArrayHelper::getColumn($timings, 'info');

        return array_count_values($query);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        try {
            $this->getDb();
        } catch (InvalidConfigException $exception) {
            return false;
        }

        return parent::isEnabled();
    }



    /**
     * Returns a reference to the DB component associated with the panel
     *
     * @return \yii\db\Connection
     * @since 2.0.5
     */
    public function getDb()
    {
        return Yii::$app->get($this->db);
    }
}
