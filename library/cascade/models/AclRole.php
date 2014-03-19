<?php

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;

class AclRole extends \infinite\db\models\AclRole
{
	use ActiveRecordTrait;
	public static $queryClass = 'cascade\\models\\AclRoleQuery';
}
