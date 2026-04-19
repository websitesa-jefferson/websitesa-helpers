<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Log;

use Websitesa\Yii2\Helpers\Helper\CheckHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\log\LogRuntimeException;

class FileTarget extends Target
{
    /**  */
    public string $logFile = '';
    /**  */
    public bool $enableRotation = true;
    /**  */
    public int $maxFileSize = 10240; // in KB
    /**  */
    public int $maxLogFiles = 5;
    /**  */
    public ?int $fileMode = null;
    /**  */
    public int $dirMode = 0775;
    /**  */
    public bool $rotateByCopy = true;

    public function init(): void
    {
        parent::init();
        if (CheckHelper::valueExists($this->logFile) === false) {
            $this->logFile = Yii::$app->getRuntimePath() . '/logs/app.log';
        } else {
            $this->logFile = Yii::getAlias($this->logFile);
        }
        if ($this->maxLogFiles < 1) {
            $this->maxLogFiles = 1;
        }
        if ($this->maxFileSize < 1) {
            $this->maxFileSize = 1;
        }
    }

    public function export(): void
    {
        $text = implode("\n", array_map([$this, 'formatMessage'], $this->messages)) . "\n";

        if (trim($text) === '') {
            return; // No messages to export, so we exit the function early
        }

        if (strpos($this->logFile, '://') === false || strncmp($this->logFile, 'file://', 7) === 0) {
            $logPath = dirname($this->logFile);
            FileHelper::createDirectory($logPath, $this->dirMode, true);
        }

        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new InvalidConfigException("Unable to append to log file: {$this->logFile}");
        }
        @flock($fp, LOCK_EX);
        if ($this->enableRotation) {
            // clear stat cache to ensure getting the real current file size and not a cached one
            // this may result in rotating twice when cached file size is used on subsequent calls
            clearstatcache();
        }
        if ($this->enableRotation && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            $this->rotateFiles();
        }
        $writeResult = @fwrite($fp, $text);
        if ($writeResult === false) {
            $error = error_get_last();
            $message = is_array($error) ? $error['message'] : 'Unknown error';
            throw new LogRuntimeException("Unable to export log through file ({$this->logFile})!: {$message}");
        }
        $textSize = strlen($text);
        if ($writeResult < $textSize) {
            throw new LogRuntimeException("Unable to export whole log through file ({$this->logFile})! Wrote $writeResult out of $textSize bytes.");
        }
        @fflush($fp);
        @flock($fp, LOCK_UN);
        @fclose($fp);

        if ($this->fileMode !== null) {
            @chmod($this->logFile, $this->fileMode);
        }
    }

    protected function rotateFiles(): void
    {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i >= 0; --$i) {
            // $i == 0 is the original log file
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->maxLogFiles) {
                    @unlink($rotateFile);
                    continue;
                }
                $newFile = $this->logFile . '.' . ($i + 1);
                $this->rotateByCopy($rotateFile, $newFile);
                if ($i === 0) {
                    $this->clearLogFile($rotateFile);
                }
            }
        }
    }

    /**
     * @param string $rotateFile
     */
    private function clearLogFile($rotateFile): void
    {
        if (($filePointer = @fopen($rotateFile, 'a')) !== false) {
            @ftruncate($filePointer, 0);
            @fclose($filePointer);
        }
    }

    /**
     * @param string $rotateFile
     * @param string $newFile
     */
    private function rotateByCopy($rotateFile, $newFile): void
    {
        @copy($rotateFile, $newFile);
        if ($this->fileMode !== null) {
            @chmod($newFile, $this->fileMode);
        }
    }
}
