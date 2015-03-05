<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\helpers;

use Yii;

/**
 * StringHelper [[@doctodo class_description:cascade\components\helpers\StringHelper]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class StringHelper extends \infinite\helpers\StringHelper
{
    /**
     * @inheritdoc
     */
    public static function parseInstructions()
    {
        $instructions = parent::parseInstructions();
        $instructions['type'] = function ($instructions) {
            if (count($instructions) >= 2) {
                $placementType = array_shift($instructions);
                $placementItem = Yii::$app->collectors['types']->getOne($placementType);
                if (isset($placementItem)) {
                    $placementItem = $placementItem->object;
                }
                while (!empty($placementItem) && is_object($placementItem) && !empty($instructions)) {
                    $nextInstruction = array_shift($instructions);
                    if (isset($placementItem->{$nextInstruction})) {
                        $placementItem = $placementItem->{$nextInstruction};
                    } else {
                        $placementItem = null;
                    }
                }
                if (is_null($placementItem)) {
                    return;
                }

                return (string) $placementItem;
            }
        };

        return $instructions;
    }
}
