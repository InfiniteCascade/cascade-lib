<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\types;

use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

/**
 * ActiveRecord is the model class for table "{{%active_record}}".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveRecord extends \cascade\components\db\ActiveRecord implements Linkable
{
    use ActiveRecordTrait {
        behaviors as baseBehaviors;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), self::baseBehaviors(), []);
    }

    /**
     * Get links.
     *
     * @return [[@doctodo return_type:getLinks]] [[@doctodo return_description:getLinks]]
     */
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['object/view', 'id' => $this->primaryKey], true),
        ];
    }
}
