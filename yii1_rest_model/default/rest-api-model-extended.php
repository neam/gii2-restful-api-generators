<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$modelClassSingular = str_replace('propel\\models\\', '', get_class($model));
$modelClassSingularWords = Inflector::camel2words($modelClassSingular);
$modelClassPluralWords = Inflector::pluralize($modelClassSingularWords);
$modelClassPlural = Inflector::camelize($modelClassPluralWords);

echo "<?php\n";
?>

use Propel\Runtime\Connection\ConnectionInterface;

class RestApi<?=$modelClassSingular?> extends BaseRestApi<?=$modelClassSingular."\n"?>
{

    /**
     * @inheritdoc
     */
    public static function getApiAttributes(\propel\models\<?=$modelClassSingular?> $item, ConnectionInterface $con = null)
    {
        $return = parent::getApiAttributes($item, $con);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getWrapperAttributes(\propel\models\<?=$modelClassSingular?> $item = null, ConnectionInterface $con = null)
    {
        $return = parent::getWrapperAttributes($item, $con);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getItemAttributes(\propel\models\<?=$modelClassSingular?> $item, ConnectionInterface $con = null)
    {
        $return = parent::getItemAttributes($item, $con);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function setCreateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        parent::setCreateAttributes($item, $requestAttributes);
    }

    /**
     * @inheritdoc
     */
    public static function setUpdateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        parent::setUpdateAttributes($item, $requestAttributes);
    }

    /**
     * @inheritdoc
     */
    public static function setItemAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        parent::setItemAttributes($item, $requestAttributes);
    }

}
