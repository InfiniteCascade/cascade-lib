<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
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
     */
    public $query;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // query
            [['query'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'query' => 'Search Query',
        ];
    }
}
