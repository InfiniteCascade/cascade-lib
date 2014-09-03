<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use Yii;
use yii\base\Model;

/**
 * SearchForm is the model behind the search form.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class SearchForm extends Model
{
    /**
     * @var __var_query_type__ __var_query_description__
     */
    public $query;

    /**
     * __method_rules_description__
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
