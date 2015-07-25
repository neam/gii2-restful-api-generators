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
    public static function getApiAttributes($item)
    {
        $return = parent::getApiAttributes($item);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getListableAttributes($item)
    {
        $return = parent::getListableAttributes($item);
        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getRelatedAttributes($item)
    {
        $return = parent::getRelatedAttributes($item);
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
