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
    public function getAllAttributes()
    {
        $return = parent::getAllAttributes();
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
    public function getListableAttributes()
    {
        $return = parent::getListableAttributes();
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function getRelatedAttributes()
    {
        $return = parent::getRelatedAttributes();
        return $return;
    }

    /**
     * @inheritdoc
     */
    public function setItemAttributes($requestAttributes)
    {
        parent::setItemAttributes($requestAttributes);
    }

}
