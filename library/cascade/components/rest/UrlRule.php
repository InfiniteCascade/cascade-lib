<?php
namespace cascade\components\rest;

class UrlRule extends \yii\rest\UrlRule {
	public $controller = 'api';
    public $pluralize = false;
	public $patterns = [
        'PUT,PATCH {type}/{id}' => 'update',
        'DELETE {type}/{id}' => 'delete',
        'GET,HEAD {type}/{id}' => 'view',
        'POST {type}' => 'create',
        'GET,HEAD {type}' => 'index',
        '{type}/{id}' => '{type}/options',
        '{type}' => 'options',
    ];

    public function init()
    {
    	$this->tokens['{id}'] = '<id:\S+>';
    	$this->tokens['{type}'] = '<type:\S+>';
    	parent::init();
    }
}
?>