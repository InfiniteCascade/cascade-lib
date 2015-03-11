<?php
/**
 * @link http://www.tealcascade.com/
 *
 * @copyright Copyright (c) 2014 Teal Software
 * @license http://www.tealcascade.com/license/
 */

namespace cascade\components\section;

interface SectionInterface
{
    // extends \teal\web\RenderInterface
    public static function generateSectionId($name);
    public function setTitle($title);
    public function getSectionTitle();
    public function defaultItems($parent = null);
    public function getTitle();
}
