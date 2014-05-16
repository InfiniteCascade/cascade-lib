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
class SourceFileLine extends \infinite\base\Object
{
    public $sourceFile;
    public $lineNumber;
    protected $_content;
    protected $_id;

    public function getContent()
    {
    	if (!isset($this->_content)) {
    		$this->_content = $this->sourceFile->readLine($this->lineNumber);
    	}
    	return $this->_content;
    }

    public function setContent($content)
    {
    	$this->_content = $content;
    }

    public function getAttributes()
    {
    	$attributes = [];
    	foreach ($this->sourceFile->headers as $key => $header) {
    		$attributes[$header] = isset($this->content[$key]) ? $this->content[$key] : null;
    	}
    	return $attributes;
    }

    public function getId()
    {
    	if (!isset($this->_id)) {
    		$this->_id = $this->generateId();
    	}
    	return $this->_id;
    }

    protected function generateId($column = null) {
    	if (is_null($column)) {
    		$column = $this->sourceFile->idColumn;
    	}
    	if (is_array($column)) {
    		$id = [];
    		foreach ($column as $subcolumn) {
    			$id[] = $this->generateId($subcolumn);
    		}
    		return implode('.', $id);
    	}
    	if (isset($this->attributes[$column])) {
			return $this->attributes[$column];
		} else {
			return null;
		}
    }

    public function clean()
    {
    	$this->content = null;
    }
}
