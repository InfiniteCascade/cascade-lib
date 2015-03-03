<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use Yii;
use infinite\base\exceptions\Exception;
use infinite\helpers\Match;

/**
 * Meta [@doctodo write class description for Meta]
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SourceFile extends \infinite\base\Object
{
    public $id;
    public $url;
    public $local;
    public $skipLines = 1;
    public $idColumn = 'id';
    public $delimeter = ',';
    public $excelSheetIndex = 0;
    public $ignore = [];

    protected $_headers;
    protected $_filePointer;
    protected $_lines;

    public function __destruct()
    {
        if (!empty($this->_filePointer)) {
            fclose($this->_filePointer);
        }
    }

    public function clean()
    {
        $this->_lines = null;
    }

    public function getLines($lazy = true, $raw = false)
    {
        if (is_null($this->_lines)) {
            $file = $this->filePointer;
            if (!$file) {
                return false;
            }
            rewind($file);
            $this->_lines = [];
            $currentLineNumber = 0;
            while (($buffer = fgetcsv($this->filePointer, 0, $this->delimeter)) !== false) {
                $currentLineNumber++;
                if ($currentLineNumber <= $this->skipLines) {
                   continue;
                }
                $line = Yii::createObject(['class' => SourceFileLine::className(), 'sourceFile' => $this, 'lineNumber' => $currentLineNumber-1, 'content' => $buffer]);
                if ($this->testIgnore($line)) { continue; }
                $lineId = $line->id;
                if (!isset($lineId)) {
                    continue;
                }
                $this->_lines[$lineId] = $line;
                if ($lazy) {
                    $line->clean();
                }
            }
        }
        return $this->_lines;
    }

    public function testIgnore($line)
    {
        foreach ($this->ignore as $field => $test) {
            $settings = [];
            if (is_numeric($field)) {
                $settings = $test;
                $field = isset($settings['field']);
                $test = $settings['test'];
            }
            $value = null;
            if (isset($line->attributes[$field])) {
                $value = $line->attributes[$field];
            }
            if ($test instanceof Match && $test->test($value)) {
                return true;
            }
        }
        return false;
    }

    public function readLine($line)
    {
        if (!$this->filePointer) { return false; }
        rewind($this->filePointer);
        $currentLineNumber = 0;
        while (($buffer = fgetcsv($this->filePointer, 0, $this->delimeter)) !== false) {
           $currentLineNumber++;
           if ($currentLineNumber === $line) {
               return $buffer;
           }
        }
        return false;
    }

    public function getFilePointer()
    {
        if (!isset($this->_filePointer)) {
            ini_set('auto_detect_line_endings', true);
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
                    } else {
                        Yii::$app->fileCache->set($fileCacheKey, file_get_contents($file), 86400);
                    }
                }
            }
            if (isset($file)) {
                $file = $this->normalizeFile($file);
            }
            if (file_exists($file)) {
                $this->_filePointer = fopen($file, 'r');
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
                $tmpfile = Yii::$app->fileStorage->getTempFile();
                return $this->convertExcel($filepath, $tmpfile, 'Excel5');
            break;
            case 'xlsx';
                $tmpfile = Yii::$app->fileStorage->getTempFile();
                return $this->convertExcel($filepath, $tmpfile);
            break;
        }
    }

    private function _loadExcel()
    {
        spl_autoload_unregister(array('BaseYii','autoload'));
        $path = INFINITE_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'phpoffice' . DIRECTORY_SEPARATOR . 'phpexcel' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php';
        require_once($path);
    }

    private function _unloadExcel()
    {
        spl_autoload_register(array('Yii','autoload'));
    }

    protected function convertExcel($filepath, $tmpfile, $filetype = 'Excel2007')
    {
        $this->_loadExcel();
        $fileCacheKey = md5(serialize([__CLASS__, __FUNCTION__, $this->id, $this->url, $this->local]));
        $test = Yii::$app->fileCache->get($fileCacheKey);
        if ($test) {
            file_put_contents($tmpfile, $test);
            return $tmpfile;
        }
        echo "Converting {$filepath}...\n";
        if (!class_exists('\PHPExcel_IOFactory')) {
            $this->_unloadExcel();
            throw new \Exception("Unable to load PHPExcel library!");
        }
        $objReader = \PHPExcel_IOFactory::createReader($filetype);
        $objPHPExcelReader = $objReader->load($filepath);
        $loadedSheetNames = $objPHPExcelReader->getSheetNames();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcelReader, 'CSV');
        foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            $objWriter->setSheetIndex($sheetIndex);
            $objWriter->save($tmpfile);
            break;
        }
        $this->_unloadExcel();
        Yii::$app->fileCache->set($fileCacheKey, file_get_contents($tmpfile), 86400);
        return $tmpfile;
    }

    protected function downloadFile($url, $savePath)
    {
        echo "Download $url...\n";
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

        if (!file_exists($savePath) || !filesize($savePath) === 0) {
            @unlink($savePath);
            return false;
        }
        return $savePath;
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
