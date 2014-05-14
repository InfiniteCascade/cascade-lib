<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use infinite\base\exceptions\Exception;

/**
 * Meta [@doctodo write class description for Meta]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SourceFile extends \infinite\base\Object
{
    public $lazy = true;
    public $url;
    public $local;
    public $delimeter = ',';

    protected $_headers;
    protected $_filePointer;

    public function __destruct()
    {
        if (!empty($this->_filePointer)) {
            fclose($this->_filePointer);
        }
    }

    public function load()
    {
        $file = $this->filePointer;
        if (!$file) {
            return false;
        }

    }

    public function getFilePointer()
    {
        if (!isset($this->_filePointer)) {
            $this->_filePointer = false;
            $file = null;
            if (isset($this->local) && file_exists($this->local)) {
                $file = $this->local;
                $pathinfo = pathinfo($this->local);
            } elseif (isset($this->url)) {
                $fileCacheKey = md5(__CLASS__ . __FUNCTION__ . $this->url);
                $fileContent = Yii::$app->fileCache->get($fileCacheKey);
                $pathinfo = pathinfo($this->url);
                $file = Yii::$app->fileStorage->getTempFile(false, $pathinfo['extension']);
                if ($fileContent) {
                    file_put_contents($file, $fileContent);
                } else {
                    if (!$this->downloadFile($this->url, $file)) {
                        $file = null;
                    }
                }
            }
            if (isset($file)) {
                $file = $this->normalizeFile($file);
            }
        }
        return $this->_filePointer;
    }

    public function normalizeFile($filepath)
    {
        $pathinfo = pathinfo($filepath);
        switch ($pathinfo['extension']) {
            case 'csv':
                return $filepath;
            break;
            case 'xls':
            case 'xlsx';
            break;
        }
    }

    protected function downloadFile($url, $savePath)
    {
        $fp = fopen($savePath, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        //curl_setopt($ch, CURLOPT_HEADER, 1);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if($httpCode !== 200) {
            @unlink($savePath);
            return false;
        }

        if (!file_exists($savePath) OR !filesize($savePath) === 0) {
            @unlink($savePath);
            return false;
        }
        return $savePath;
    }

    public function readLine($line)
    {
        if (!$this->filePointer) { return false; }
        rewind($this->filePointer);
        $currentLineNumber = 1;
        while (($buffer = fgetcsv($this->filePointer, 0, $this->delimeter)) !== false) {
           if ($currentLineNumber === $line) {
               return $buffer;
           }   
           $currentLineNumber++;
        }
        return false;
    }

    public function getHeaders()
    {
        if (!isset($this->_headers)) {
            $this->_headers = $this->readLine(1);
            if (!$this->_headers) {
                $this->_headers = [];
            }
        }
        return $this->_headers;
    }

    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }
}
