<?php
namespace cascade\components\rest;

/**
 * UrlRule [[@doctodo class_description:cascade\components\rest\UrlRule]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class UrlRule extends \yii\rest\UrlRule
{
    /**
     * @inheritdoc
     */
    public $controller = 'api';
    /**
     * @inheritdoc
     */
    public $pluralize = false;
    /**
     * @inheritdoc
     */
    public $patterns = [
        'PUT,PATCH {type}/{id}' => 'update',
        'DELETE {type}/{id}' => 'delete',
        'GET,HEAD {type}/{id}' => 'view',
        'POST {type}' => 'create',
        'GET,HEAD {type}' => 'index',
        '{type}/{id}' => '{type}/options',
        '{type}' => 'options',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tokens['{id}'] = '<id:\S+>';
        $this->tokens['{type}'] = '<type:\S+>';
        parent::init();
    }
}
