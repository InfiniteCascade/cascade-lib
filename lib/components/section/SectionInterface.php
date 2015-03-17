<?php
/**
 * @link http://canis.io/
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace cascade\components\section;

interface SectionInterface
{
    // extends \canis\web\RenderInterface
    public static function generateSectionId($name);
    public function setTitle($title);
    public function getSectionTitle();
    public function defaultItems($parent = null);
    public function getTitle();
}
