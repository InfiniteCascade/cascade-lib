<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use yii\base\Model;

/**
 * SearchForm is the model behind the search form.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SearchForm extends Model
{
    /**
     * @var [[@doctodo var_type:query]] [[@doctodo var_description:query]]
     */
    public $query;

    /**
     * [[@doctodo method_description:rules]].
     *
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // query
            [['query'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'query' => 'Search Query',
        ];
    }
}
