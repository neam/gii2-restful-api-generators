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
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

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
    public function setCreateAttributes($requestAttributes)
    {
        parent::setCreateAttributes($requestAttributes);
    }

    /**
     * @inheritdoc
     */
    public function setUpdateAttributes($requestAttributes)
    {
        parent::setUpdateAttributes($requestAttributes);
    }

    /**
     * @inheritdoc
     */
    public function setItemAttributes($requestAttributes)
    {
        parent::setItemAttributes($requestAttributes);
    }

}
