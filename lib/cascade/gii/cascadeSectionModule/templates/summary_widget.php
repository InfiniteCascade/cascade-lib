<?php
/**
 * This is the template for generating a controller class within a module.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\module\Generator $generator
 */
echo "<?php\n";
?>

namespace <?=$generator->getWidgetNamespace(); ?>;

class Summary extends \cascade\components\web\widgets\core\Summary
{
<?php

?>
}
