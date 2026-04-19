<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Logs;

use Websitesa\Yii2\Helpers\Jobs\SendMailJob;
use Yii;
use yii\log\EmailTarget as LogEmailTarget;

class EmailTarget extends LogEmailTarget
{
    public function export(): void
    {
        $messages = array_map([$this, 'formatMessage'], $this->messages);

        $body = wordwrap(implode("\n", $messages), 70);

        /** @var array<string, string> $to */
        $to = is_array($this->message['to']) ? $this->message['to'] : [(string)$this->message['to'] => 'Desenvolvedor'];

        Yii::$app->queue->push(new SendMailJob([
            'from'    => (string)($this->message['from'] ?? ''),
            'to'      => $to,
            'subject' => (string)($this->message['subject'] ?? 'Log Message'),
            'view'    => 'layouts/html',
            'params'  => ['content' => $body],
        ]));
    }
}
