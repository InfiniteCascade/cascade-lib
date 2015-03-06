<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

use infinite\base\exceptions\Exception;
use infinite\helpers\Match;
use Yii;

/**
 * SourceFile [[@doctodo class_description:cascade\components\dataInterface\connectors\file\SourceFile]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SourceFile extends \infinite\base\Object
{
    /**
     * @var [[@doctodo var_type:id]] [[@doctodo var_description:id]]
     */
    public $id;
    /**
     * @var [[@doctodo var_type:url]] [[@doctodo var_description:url]]
     */
    public $url;
    /**
     * @var [[@doctodo var_type:local]] [[@doctodo var_description:local]]
     */
    public $local;
    /**
     * @var [[@doctodo var_type:skipLines]] [[@doctodo var_description:skipLines]]
     */
    public $skipLines = 1;
    /**
     * @var [[@doctodo var_type:idColumn]] [[@doctodo var_description:idColumn]]
     */
    public $idColumn = 'id';
    /**
     * @var [[@doctodo var_type:delimeter]] [[@doctodo var_description:delimeter]]
     */
    public $delimeter = ',';
    /**
     * @var [[@doctodo var_type:excelSheetIndex]] [[@doctodo var_description:excelSheetIndex]]
     */
    public $excelSheetIndex = 0;
    /**
     * @var [[@doctodo var_type:ignore]] [[@doctodo var_description:ignore]]
     */
    public $ignore = [];

    /**
     * @var [[@doctodo var_type:_headers]] [[@doctodo var_description:_headers]]
     */
    protected $_headers;
    /**
     * @var [[@doctodo var_type:_filePointer]] [[@doctodo var_description:_filePointer]]
     */
    protected $_filePointer;
    /**
     * @var [[@doctodo var_type:_lines]] [[@doctodo var_description:_lines]]
     */
    protected $_lines;

    /**
     * [[@doctodo method_description:__destruct]].
     */
    public function __destruct()
    {
        if (!empty($this->_filePointer)) {
            fclose($this->_filePointer);
        }
    }

    /**
     * [[@doctodo method_description:clean]].
     */
    public function clean()
    {
        $this->_lines = null;
    }

    /**
     * Get lines.
     *
     * @param boolean $lazy [[@doctodo param_description:lazy]] [optional]
     * @param boolean $raw  [[@doctodo param_description:raw]] [optional]
     *
     * @return [[@doctodo return_type:getLines]] [[@doctodo return_description:getLines]]
     */
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
                if ($this->testIgnore($line)) {
                    continue;
                }
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

    /**
     * [[@doctodo method_description:testIgnore]].
     *
     * @param [[@doctodo param_type:line]] $line [[@doctodo param_description:line]]
     *
     * @return [[@doctodo return_type:testIgnore]] [[@doctodo return_description:testIgnore]]
     */
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

    /**
     * [[@doctodo method_description:readLine]].
     *
     * @param [[@doctodo param_type:line]] $line [[@doctodo param_description:line]]
     *
     * @return [[@doctodo return_type:readLine]] [[@doctodo return_description:readLine]]
     */
    public function readLine($line)
    {
        if (!$this->filePointer) {
            return false;
        }
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

    /**
     * Get file pointer.
     *
     * @return [[@doctodo return_type:getFilePointer]] [[@doctodo return_description:getFilePointer]]
     */
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

    /**
     * [[@doctodo method_description:normalizeFile]].
     *
     * @param [[@doctodo param_type:filepath]] $filepath [[@doctodo param_description:filepath]]
     *
     * @return [[@doctodo return_type:normalizeFile]] [[@doctodo return_description:normalizeFile]]
     */
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

    /**
     * [[@doctodo method_description:_loadExcel]].
     */
    private function _loadExcel()
    {
        spl_autoload_unregister(['BaseYii', 'autoload']);
        $path = INFINITE_APP_VENDOR_PATH . DIRECTORY_SEPARATOR . 'phpoffice' . DIRECTORY_SEPARATOR . 'phpexcel' . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php';
        require_once $path;
    }

    /**
     * [[@doctodo method_description:_unloadExcel]].
     */
    private function _unloadExcel()
    {
        spl_autoload_register(['Yii', 'autoload']);
    }

    /**
     * [[@doctodo method_description:convertExcel]].
     *
     * @param [[@doctodo param_type:filepath]] $filepath [[@doctodo param_description:filepath]]
     * @param [[@doctodo param_type:tmpfile]]  $tmpfile  [[@doctodo param_description:tmpfile]]
     * @param string                           $filetype [[@doctodo param_description:filetype]] [optional]
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     * @return [[@doctodo return_type:convertExcel]] [[@doctodo return_description:convertExcel]]
     *
     */
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
            throw new Exception("Unable to load PHPExcel library!");
        }
        $objReader = \PHPExcel_IOFactory::createReader($filetype);
        $objPHPExcelReader = $objReader->load($filepath);
        $loadedSheetNames = $objPHPExcelReader->getSheetNames();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcelReader, 'CSV');
        foreach ($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            $objWriter->setSheetIndex($sheetIndex);
            $objWriter->save($tmpfile);
            break;
        }
        $this->_unloadExcel();
        Yii::$app->fileCache->set($fileCacheKey, file_get_contents($tmpfile), 86400);

        return $tmpfile;
    }

    /**
     * [[@doctodo method_description:downloadFile]].
     *
     * @param [[@doctodo param_type:url]]      $url      [[@doctodo param_description:url]]
     * @param [[@doctodo param_type:savePath]] $savePath [[@doctodo param_description:savePath]]
     *
     * @return [[@doctodo return_type:downloadFile]] [[@doctodo return_description:downloadFile]]
     */
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
        if ($httpCode !== 200) {
            @unlink($savePath);

            return false;
        }

        if (!file_exists($savePath) || !filesize($savePath) === 0) {
            @unlink($savePath);

            return false;
        }

        return $savePath;
    }

    /**
     * Get headers.
     *
     * @return [[@doctodo return_type:getHeaders]] [[@doctodo return_description:getHeaders]]
     */
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

    /**
     * Set headers.
     *
     * @param [[@doctodo param_type:headers]] $headers [[@doctodo param_description:headers]]
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }
}
