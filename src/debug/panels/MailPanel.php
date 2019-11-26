<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\Event;
use yii\debug\models\search\Mail;
use yii\debug\Panel;
use yii\mail\BaseMailer;
use yii\helpers\FileHelper;
use yii\mail\MessageInterface;

/**
 * Debugger panel that collects and displays the generated emails.
 *
 * @property array $messagesFileName This property is read-only.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class MailPanel extends Panel
{
    /**
     * @var string path where all emails will be saved. should be an alias.
     */
    public $mailPath = '@runtime/debug/mail';

    /**
     * @var array current request sent messages
     */
    private $_messages = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Event::on('yii\mail\BaseMailer', BaseMailer::EVENT_AFTER_SEND, function ($event) {
            /* @var $message MessageInterface */
            $message = $event->message;
            $messageData = [
                'isSuccessful' => $event->isSuccessful,
                'from' => $this->convertParams($message->getFrom()),
                'to' => $this->convertParams($message->getTo()),
                'reply' => $this->convertParams($message->getReplyTo()),
                'cc' => $this->convertParams($message->getCc()),
                'bcc' => $this->convertParams($message->getBcc()),
                'subject' => $message->getSubject(),
                'charset' => $message->getCharset(),
            ];

            // store message as file
            $fileName = $event->sender->generateMessageFileName();
            $mailPath = Yii::getAlias($this->mailPath);
            FileHelper::createDirectory($mailPath);
            file_put_contents($mailPath . '/' . $fileName, $message->toString());
            $messageData['file'] = $fileName;

            $this->_messages[] = $messageData;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Mail';
    }

    /**
     * Save info about messages of current request. Each element is array holding
     * message info, such as: time, reply, bc, cc, from, to and other.
     * @return array messages
     */
    public function save()
    {
        return $this->_messages;
    }

    /**
     * Return array of created email files
     * @return array
     */
    public function getMessagesFileName()
    {
        $names = [];
        foreach ($this->_messages as $message) {
            $names[] = $message['file'];
        }

        return $names;
    }

    /**
     * @param mixed $attr
     * @return string
     */
    private function convertParams($attr)
    {
        if (is_array($attr)) {
            $attr = implode(', ', array_keys($attr));
        }

        return $attr;
    }
}
