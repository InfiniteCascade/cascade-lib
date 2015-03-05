<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface\connectors\file;

/**
 * SourceFileLine [[@doctodo class_description:cascade\components\dataInterface\connectors\file\SourceFileLine]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SourceFileLine extends \infinite\base\Object
{
    /**
     * @var [[@doctodo var_type:sourceFile]] [[@doctodo var_description:sourceFile]]
     */
    public $sourceFile;
    /**
     * @var [[@doctodo var_type:lineNumber]] [[@doctodo var_description:lineNumber]]
     */
    public $lineNumber;
    /**
     * @var [[@doctodo var_type:_content]] [[@doctodo var_description:_content]]
     */
    protected $_content;
    /**
     * @var [[@doctodo var_type:_id]] [[@doctodo var_description:_id]]
     */
    protected $_id;

    /**
     * Get content.
     *
     * @return [[@doctodo return_type:getContent]] [[@doctodo return_description:getContent]]
     */
    public function getContent()
    {
        if (!isset($this->_content)) {
            $this->_content = $this->sourceFile->readLine($this->lineNumber);
        }

        return $this->_content;
    }

    /**
     * Set content.
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Get attributes.
     *
     * @return [[@doctodo return_type:getAttributes]] [[@doctodo return_description:getAttributes]]
     */
    public function getAttributes()
    {
        $attributes = [];
        foreach ($this->sourceFile->headers as $key => $header) {
            $attributes[$header] = isset($this->content[$key]) ? $this->content[$key] : null;
        }

        return $attributes;
    }

    /**
     * Get id.
     *
     * @return [[@doctodo return_type:getId]] [[@doctodo return_description:getId]]
     */
    public function getId()
    {
        if (!isset($this->_id)) {
            $this->_id = $this->generateId();
        }

        return $this->_id;
    }

    /**
     * [[@doctodo method_description:generateId]].
     *
     * @return [[@doctodo return_type:generateId]] [[@doctodo return_description:generateId]]
     */
    protected function generateId($column = null)
    {
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
            return;
        }
    }

    /**
     * [[@doctodo method_description:clean]].
     */
    public function clean()
    {
        $this->content = null;
    }
}
