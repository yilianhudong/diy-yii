<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\helpers\Html;
use yii\log\Logger;
use yii\debug\Panel;
use yii\helpers\VarDumper;

/**
 * Dump panel that collects and displays debug messages (Logger::LEVEL_TRACE).
 *
 * @author Pistej <pistej2@gmail.com>
 * @author Simon Karlen <simi.albi@outlook.com>
 * @since 2.1.0
 */
class DumpPanel extends Panel
{
    /**
     * @var array the message categories to filter by. If empty array, it means
     * all categories are allowed
     */
    public $categories = ['application'];
    /**
     * @var bool whether the result should be syntax-highlighted
     */
    public $highlight = true;
    /**
     * @var int maximum depth that the dumper should go into the variable
     */
    public $depth = 10;
    /**
     * @var callable callback that replaces the built-in var dumper. The signature of
     * this function should be: `function (mixed $data, DumpPanel $panel)`
     * @since 2.1.3
     */
    public $varDumpCallback;

    /**
     * @var array log messages extracted to array as models, to use with data provider.
     */
    private $_models;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Dump';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $except = [];
        if (isset($this->module->panels['router'])) {
            $except = $this->module->panels['router']->getCategories();
        }

        $messages = $this->getLogMessages(Logger::LEVEL_TRACE, $this->categories, $except);

        foreach ($messages as &$message) {
            if (!isset($message[0])) {
                continue;
            }

            $message[0] = $this->varDump($message[0]);
        }

        return $messages;
    }

    /**
     * Called by `save()` to format the dumped variable.
     *
     * @since 2.1.3
     */
    public function varDump($var)
    {
        if (is_callable($this->varDumpCallback)) {
            return call_user_func($this->varDumpCallback, $var, $this);
        }

        $message = VarDumper::dumpAsString($var, $this->depth, $this->highlight);

        //don't encode highlighted variables
        if (!$this->highlight) {
            $message = Html::encode($message);
        }

        return $message;
    }
}
