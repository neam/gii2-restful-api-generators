<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$modelClassSingular = get_class($model);
$modelClassSingularWords = Inflector::camel2words($modelClassSingular);
$itemTypeSingularRef = Inflector::camel2id($modelClassSingular, '_');
$modelClassPluralWords = Inflector::pluralize($modelClassSingularWords);
$modelClassPlural = Inflector::camelize($modelClassPluralWords);

echo "<?php\n";
?>

use Propel\Runtime\Map\TableMap;

class BaseRestApi<?=$modelClassSingular."\n"?>
{

    /**
     * Returns "all" attributes for this resource.
     *
     * @return array
     */
    public function getAllAttributes()
    {
        $item = \propel\models\<?=$modelClassSingular?>Query::create()->findOneById($this->id);
        return static::getApiAttributes($item);
    }

    /**
     * Returns rest api attributes for this resource.
     *
     * @return array
     */
    public static function getApiAttributes(\propel\models\<?=$modelClassSingular?> $item, $level = 0)
    {
        return array(
            'id' => $item->getPrimaryKey(),
<?php if (in_array($modelClassSingular, array_keys(\ItemTypes::where('is_graph_relatable')))): ?>
            'node_id' => $item->getPrimaryKey() ? (int) /* $item->ensureNode()->id */ -1 : null,
<?php endif; ?>
            'item_type' => '<?= $itemTypeSingularRef ?>',
            'item_label' => $item->getPrimaryKey() ? $item->getItemLabel() : '[[none]]',
            'attributes' => array_merge(
                static::getListableAttributes($item, $level),
                array()
            ),
        );

    }

    /**
     * @inheritdoc
     */
    public static function getListableAttributes(\propel\models\<?=$modelClassSingular?> $item, $level = 0)
    {
        // Only supply related attributes at root and first level
        if ($level > 1) {
            return array("_suppressed_at_level" => $level);
        }
        return array(
<?php
if (!method_exists($model, 'itemTypeAttributes')) {
    throw new Exception("Model ".get_class($model)." does not have method itemTypeAttributes()");
}
$relations = $model->relations();

foreach ($model->itemTypeAttributes() as $attribute => $attributeInfo):

    // Do not consider attributes referencing other item types
    if (strpos($attribute, '/') !== false) {
        continue;
    }

    switch ($attributeInfo["type"]) {
        case "has-many-relation":
        case "many-many-relation":

            // tmp until memory allocation has been resolved (likely via pagination and/or returning metadata about relations instead of the actual objects)
            break;

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = $relationInfo[1];
            $relationAttribute = $relationInfo[2];

?>
            '<?=$attribute?>' => RelatedItems::formatItems(
                "<?=$relatedModelClass?>",
                $item,
                "<?=Inflector::camelize($relationAttribute)?>",
                $level
            ),
<?php
            break;
        case "has-one-relation":
        case "belongs-to-relation":

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = $relationInfo[1];
            $relationAttribute = $relationInfo[2];

?>
            '<?=$attribute?>' => RelatedItems::formatItem(
                "<?=$relatedModelClass?>",
                $item,
                "<?=Inflector::camelize($relationAttribute)?>",
                $level
            ),
<?php
            break;
        case "ordinary":
        case "primary-key":
            $camelizedAttribute = Inflector::camelize($attribute);
?>
            '<?=$attribute?>' => $item->get<?=$camelizedAttribute?>("Y-m-d H:i:s"),
<?php
            break;
        default:
            // ignore
            break;
    }

endforeach;
?>
        );
    }

    /**
     * @inheritdoc
     */
    public static function getRelatedAttributes(\propel\models\<?=$modelClassSingular?> $item, $level)
    {
        $attributes = static::getApiAttributes($item, $level);
        // remote attributes that cause recursion here
        return $attributes;
    }

    public static function setCreateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        static::setItemAttributes($item, $requestAttributes);
    }

    public static function setUpdateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        static::setItemAttributes($item, $requestAttributes);
    }

    /**
     * Sets the underlying item attributes.
     */
    public static function setItemAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        $row = [];
<?php
if (!method_exists($model, 'itemTypeAttributes')) {
    throw new Exception("Model ".get_class($model)." does not have method itemTypeAttributes()");
}
$relations = $model->relations();
$deepAttributes = [];
foreach ($model->itemTypeAttributes() as $attribute => $attributeInfo):

    // Special consideration for attributes referencing other item types
    if (strpos($attribute, '/') !== false) {
        $deepAttributes[$attribute] = $attributeInfo;
        continue;
    }

    switch ($attributeInfo["type"]) {
        case "has-many-relation":
        case "many-many-relation":
        case "belongs-to-relation":

            // tmp ignore for now - may be implemented later
            // requires some refactoring, proper use of transactions and handling of various edge cases
            break;

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = "RestApi".$relationInfo[1];

?>
        RelatedItems::set|saveRelatedItems(
            "<?=$relatedModelClass?>",
            '<?=$attribute?>',
            $requestAttributes['attributes']['<?=$attribute?>']
        ),
<?php
            break;
        case "has-one-relation":

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = "RestApi".$relationInfo[1];
            $fkAttribute = $relationInfo[2];

?>
        $row['<?=$fkAttribute?>'] = $requestAttributes['attributes']-><?=$attribute?>->id;
<?php
            break;
        case "ordinary":
        case "primary-key":
?>
        $row['<?=$attribute?>'] = $requestAttributes['attributes']-><?=$attribute?>;
<?php
            break;
        default:
            // ignore
            break;
    }

endforeach;
?>
        $item->fromArray($row, TableMap::TYPE_FIELDNAME);

<?php if (!empty($deepAttributes)): ?>
/* TODO:
<?php
print_r($deepAttributes);
?>
*/
<?php endif; ?>
    }

}
