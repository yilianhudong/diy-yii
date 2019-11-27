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
 * @property array $profileLogs Returns all profile logs of the current request for this panel. This property is read-only.
 * @property string $summaryName Short name of the panel, which will be use in summary. This property is read-only.
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Database';
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
        return $this->getLogMessages(Logger::LEVEL_PROFILE, ['yii\db\Command::query', 'yii\db\Command::execute']);
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
     * @throws InvalidConfigException
     */
    public function getDb()
    {
        return Yii::$app->get($this->db);
    }
}
