<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Job;

use Websitesa\Yii2\Helpers\Helper\EmailHelper;
use Yii;
use yii\queue\JobInterface;

/**
 * Job para envio de e-mail em segundo plano.
 */
class SendMailJob implements JobInterface
{
    /** @var string|array<string,string> */
    public $from;

    /** @var string|array<string,string> */
    public $to;

    /** @var string|array<string,string>|null */
    public $bcc;

    /** @var string */
    public $subject;

    /** @var string */
    public $view;

    /** @var array<string,mixed> */
    public $params = [];

    /**
     * @param array{
     *   from?: string|array<string,string>,
     *   to?: string|array<string,string>,
     *   bcc?: string|array<string,string>,
     *   subject?: string,
     *   view?: string,
     *   params?: array<string,mixed>
     * } $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if ($this->from === null || $this->from === '' || $this->from === []) {
            $this->from = Yii::$app->params['mailFrom'] ?? '';
        }

        if ($this->bcc === null || $this->bcc === '' || $this->bcc === []) {
            $this->bcc = Yii::$app->params['mailTo'] ?? null;
        }
    }

    /**
     * Executa o envio do e-mail.
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        EmailHelper::sendMail(
            from: $this->from,
            to: $this->to,
            bcc: $this->bcc,
            subject: $this->subject,
            view: $this->view,
            params: $this->params
        );
        return null;
    }
}
