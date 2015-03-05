<?php
/**
 * @link http://www.infinitecascade.com/
 *
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
     * @var [[@doctodo var_type:email]] [[@doctodo var_description:email]]
     */
    public $email;
    /**
     * @var [[@doctodo var_type:password]] [[@doctodo var_description:password]]
     */
    public $password;
    /**
     * @var [[@doctodo var_type:rememberMe]] [[@doctodo var_description:rememberMe]]
     */
    public $rememberMe = true;

    /**
     * [[@doctodo method_description:rules]].
     *
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['email', 'password'], 'required'],
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
        $user = User::findByEmail($this->email);
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = User::findByEmail($this->email);
            Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);

            return true;
        } else {
            return false;
        }
    }
}
