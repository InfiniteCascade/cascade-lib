<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\section;

interface SectionInterface { // extends \infinite\web\RenderInterface
    public static function generateSectionId($name);
    public function setTitle($title);
    public function getSectionTitle();
    public function defaultItems($parent = null);
    public function getTitle();
}
