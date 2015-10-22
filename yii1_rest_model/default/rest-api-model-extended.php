<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$modelClassSingular = get_class($model);
$modelClassSingularWords = Inflector::camel2words($modelClassSingular);
$modelClassPluralWords = Inflector::pluralize($modelClassSingularWords);
$modelClassPlural = Inflector::camelize($modelClassPluralWords);

echo "<?php\n";
?>

class RestApi<?=$modelClassSingular?> extends BaseRestApi<?=$modelClassSingular."\n"?>
{

    /**
     * @inheritdoc
     */
    public static function getApiAttributes(\propel\models\<?=$modelClassSingular?> $item, $level = 0)
    {
        $return = parent::getApiAttributes($item, $level);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getListableAttributes(\propel\models\<?=$modelClassSingular?> $item, $level = 0)
    {
        $return = parent::getListableAttributes($item, $level);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getRelatedAttributes(\propel\models\<?=$modelClassSingular?> $item, $level)
    {
        $return = parent::getRelatedAttributes($item, $level);
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
