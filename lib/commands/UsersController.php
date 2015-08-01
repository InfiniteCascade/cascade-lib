<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\commands;

use cascade\models\Group;
use cascade\models\User;
use yii\helpers\Console;

/**
 * UsersController Manage users .
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class UsersController extends \canis\console\Controller
{
    /**
     * TBD.
     */
    public function actionIndex()
    {
        echo "Boom";
    }

    /**
     * Create a user.
     */
    public function actionCreate()
    {
        $groups = Group::find()->disableAccessCheck()->orderBy('name')->all();
        $this->out("Groups");
        $options = [];
        $i = 1;
        $defaultGroup = null;
        foreach ($groups as $group) {
            $extra = '';
            if ($group->system === 'users') {
                $defaultGroup = $group->primaryKey;
                $extra = '*';
            }
            $options[$i] = $group->primaryKey;
            $this->out("$i) {$group->descriptor}{$extra}");
            $i++;
        }
        $options[''] = $defaultGroup;
        $group = Console::select("Choose", $options);
        if (empty($group)) {
            $group = $defaultGroup;
        } else {
            $group = $options[$group];
        }

        $user =  new User();
        $user->scenario = 'creation';
        $user->first_name = $this->prompt("First name");
        $user->last_name = $this->prompt("Last name");
        $user->email = $this->prompt("Email");
        $user->status = 1;
        $user->username = $this->prompt("Username");
        $user->password = $this->prompt("Password");
        $user->registerRelationModel(['parent_object_id' => $group]);
        if (!$user->validate()) {
            \d($user->errors);
            $this->stderr("User didn't validate!");
            exit;
        }
        $individual = $user->guessIndividual();
        if (empty($individual)) {
            if (!Console::confirm("No matching individual was found. Continue?")) {
                $this->stderr("Bye!");
                exit;
            }
        } elseif (is_object($individual)) {
            $user->object_individual_id = $individual->primaryKey;
            if (!Console::confirm("Matching individual was found ({$individual->descriptor})! Continue?")) {
                $this->stderr("Bye!");
                exit;
            }
        } else {
            $options = [];
            $i = 1;
            $this->out("Possible Individual Matches...");
            foreach ($individual as $ind) {
                $options[$i] = $ind->primaryKey;
                $this->out("$i) {$ind->descriptor}");
                $i++;
            }
            $user->object_individual_id = Console::select("Choose", $options);
        }

        if ($user->save()) {
            $this->out("User created!");
        } else {
            \d($user->errors);
            $this->out("Error creating user!");
        }
    }
}
