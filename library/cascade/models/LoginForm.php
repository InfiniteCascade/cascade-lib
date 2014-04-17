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
 * LoginForm is the model behind the login form.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class LoginForm extends Model
{
    /**
     * @var __var_username_type__ __var_username_description__
     */
    public $username;
    /**
     * @var __var_password_type__ __var_password_description__
     */
    public $password;
    /**
     * @var __var_rememberMe_type__ __var_rememberMe_description__
     */
    public $rememberMe = true;

    /**
     * __method_rules_description__
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // password is validated by validatePassword()
            [['password'], 'validatePassword'],
            // rememberMe must be a boolean value
            [['rememberMe'], 'boolean'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        $user = User::findByUsername($this->username);
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = User::findByUsername($this->username);
            Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);

            return true;
        } else {
            return false;
        }
    }
}
