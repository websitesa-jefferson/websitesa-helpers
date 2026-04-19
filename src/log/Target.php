<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Log;

use Websitesa\Yii2\Helpers\Helper\CheckHelper;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\web\Request;

abstract class Target extends Component
{
    /** @var array<int, string> */
    public array $categories = [];
    /** @var array<int, string> */
    public array $except = [];
    /** @var array<int, string> */
    public array $logVars = [
        '_GET',
        '_POST',
        '_FILES',
        '_COOKIE',
        '_SESSION',
        '_SERVER',
    ];
    /** @var array<int, string> */
    public array $maskVars = [
        '_SERVER.HTTP_AUTHORIZATION',
        '_SERVER.PHP_AUTH_USER',
        '_SERVER.PHP_AUTH_PW',
    ];
    /** @var callable|null */
    public $prefix;
    /**  */
    public int $exportInterval = 1000;
    /** @var array<int, array<int, mixed>> */
    public array $messages = [];
    /**  */
    public bool $microtime = false;
    /**  */
    private int $_levels = 0;
    /** @var bool|callable */
    private $_enabled = true;

    abstract public function export(): void;

    /**
     * @param array<int, array<int, mixed>> $messages
     * @param bool $final
     */
    public function collect($messages, $final): void
    {
        $this->messages = array_merge($this->messages, static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except));
        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            if (($context = $this->getContextMessage()) !== '') {
                $beginTime = defined('YII_BEGIN_TIME') ? constant('YII_BEGIN_TIME') : microtime(true);
                $this->messages[] = [$context, Logger::LEVEL_INFO, 'application', $beginTime, [], 0];
            }
            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }

    protected function getContextMessage(): string
    {
        $context = ArrayHelper::filter($GLOBALS, $this->logVars);
        foreach ($this->maskVars as $var) {
            if (ArrayHelper::getValue($context, $var) !== null) {
                ArrayHelper::setValue($context, $var, '***');
            }
        }
        $result = [];
        foreach ($context as $key => $value) {
            $result[] = "\${$key} = " . VarDumper::dumpAsString($value);
        }

        return implode("\n\n", $result);
    }

    public function getLevels(): int
    {
        return $this->_levels;
    }

    /**
     * @param array<int, string>|int $levels
     */
    public function setLevels($levels): void
    {
        static $levelMap = [
            'error'   => Logger::LEVEL_ERROR,
            'warning' => Logger::LEVEL_WARNING,
            'info'    => Logger::LEVEL_INFO,
            'trace'   => Logger::LEVEL_TRACE,
            'profile' => Logger::LEVEL_PROFILE,
        ];
        if (is_array($levels)) {
            $this->_levels = 0;
            foreach ($levels as $level) {
                if (isset($levelMap[$level])) {
                    $this->_levels |= $levelMap[$level];
                } else {
                    throw new InvalidConfigException("Unrecognized level: $level");
                }
            }
        } else {
            /** @var int $bitmapValues */
            $bitmapValues = array_reduce($levelMap, function (int $carry, int $item) {
                return $carry | $item;
            }, 0);
            if (($bitmapValues & $levels) === 0 && $levels !== 0) {
                throw new InvalidConfigException("Incorrect $levels value");
            }
            $this->_levels = $levels;
        }
    }

    /**
     * @param array<int, array<int, mixed>> $messages
     * @param int $levels
     * @param array<int, string> $categories
     * @param array<int, string> $except
     * @return array<int, array<int, mixed>>
     */
    public static function filterMessages($messages, $levels = 0, $categories = [], $except = []): array
    {
        foreach ($messages as $i => $message) {
            if ($levels !== 0 && (!isset($message[1]) || !is_int($message[1]) || ($levels & $message[1]) === 0)) {
                unset($messages[$i]);
                continue;
            }

            $matched = $categories === [];
            foreach ($categories as $category) {
                if ($message[2] === $category || ($category !== '' && substr_compare($category, '*', -1, 1) === 0 && strpos(is_string($message[2]) ? $message[2] : '', rtrim($category, '*')) === 0)) {
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                foreach ($except as $category) {
                    $prefix = rtrim($category, '*');
                    if (($message[2] === $category || $prefix !== $category) && is_string($message[2]) && strpos($message[2], $prefix) === 0) {
                        $matched = false;
                        break;
                    }
                }
            }

            if ($matched === false) {
                unset($messages[$i]);
            }
        }

        return $messages;
    }

    /**
     * @param array<int, mixed> $message
     */
    public function formatMessage($message): string
    {
        $text = $message[0];
        $level = is_int($message[1]) ? $message[1] : Logger::LEVEL_INFO;
        $category = is_string($message[2]) ? $message[2] : 'application';
        $timestamp = $message[3];

        $levelName = Logger::getLevelName($level);
        if (!is_string($text)) {
            // exceptions may not be serializable if in the call stack somewhere is a Closure
            if ($text instanceof \Exception || $text instanceof \Throwable) {
                $text = (string) $text;
            } else {
                $text = VarDumper::export($text);
            }
        }
        $traces = [];
        if (isset($message[4]) && is_array($message[4])) {
            foreach ($message[4] as $trace) {
                if (is_array($trace) && isset($trace['file'], $trace['line'])) {
                    $file = is_string($trace['file']) ? $trace['file'] : 'unknown';
                    $line = is_scalar($trace['line']) ? (string)$trace['line'] : 'unknown';
                    $traces[] = "in {$file}:{$line}";
                }
            }
        }

        $prefix = $this->getMessagePrefix($message);

        $prefixStr = is_string($prefix) ? $prefix : "";

        $sendLog = [
            'timestamp' => $this->getTime(is_numeric($timestamp) ? $timestamp : microtime(true)),
            'app'       => Yii::$app->id,
            'level'     => $levelName,
            'service'   => $category,
            'message'   => $text,
            'env'       => defined('YII_ENV') ? constant('YII_ENV') : 'prod',
            'trace_id'  => is_array($prefix) ? $prefix['sessionid'] : '-',
            'user_id'   => is_array($prefix) ? $prefix['userid'] : '-',
            'ip'        => is_array($prefix) ? $prefix['ip'] : '-',
            // 'trace' => $sendTrace,
        ];

        return Json::encode($sendLog);
    }

    /**
     * @param array<int, mixed> $message
     * @return string|array{ip: string, userid: string|int, sessionid: string}
     */
    public function getMessagePrefix($message)
    {
        if ($this->prefix !== null) {
            $prefix = call_user_func($this->prefix, $message);
            if (is_array($prefix)) {
                return [
                    'ip'        => isset($prefix['ip']) && is_scalar($prefix['ip']) ? (string)$prefix['ip'] : '-',
                    'userid'    => isset($prefix['userid']) && is_scalar($prefix['userid']) ? (string)$prefix['userid'] : '-',
                    'sessionid' => isset($prefix['sessionid']) && is_scalar($prefix['sessionid']) ? (string)$prefix['sessionid'] : '-',
                ];
            }
            return is_string($prefix) ? $prefix : '';
        }

        if (CheckHelper::valueExists(Yii::$app) === false) {
            return '';
        }

        $request = Yii::$app->getRequest();
        $ip = $request instanceof Request ? $request->getUserIP() : '-';

        /* @var $user \yii\web\User */
        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
        if ($user && ($identity = $user->getIdentity(false))) {
            $userID = $identity->getId();
        } else {
            $userID = '-';
        }

        /* @var $session \yii\web\Session */
        $session = Yii::$app->has('session', true) ? Yii::$app->get('session') : null;
        $sessionID = $session && $session->getIsActive() ? $session->getId() : '-';

        return [
            'ip'        => (string)$ip,
            'userid'    => (string)$userID,
            'sessionid' => (string)$sessionID,
        ];
    }

    /**
     * @param bool|callable $value
     */
    public function setEnabled($value): void
    {
        $this->_enabled = $value;
    }

    /**  */
    public function getEnabled(): bool
    {
        if (is_callable($this->_enabled)) {
            return call_user_func($this->_enabled, $this);
        }

        return $this->_enabled;
    }

    /**
     * @param float|string|int $timestamp
     */
    protected function getTime($timestamp): string
    {
        $parts = explode('.', sprintf('%F', (float)$timestamp));

        return date('Y-m-d H:i:s', (int)$parts[0]) . ($this->microtime ? ('.' . $parts[1]) : '');
    }
}
