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
 */
class SearchForm extends Model
{
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
}
