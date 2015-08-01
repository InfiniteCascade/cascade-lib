<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
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
