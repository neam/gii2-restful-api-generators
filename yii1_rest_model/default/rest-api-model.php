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

class BaseRestApi<?=$modelClassSingular?> extends <?=$modelClassSingular."\n"?>
{

    /**
     * @inheritdoc
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns "all" attributes for this resource.
     *
     * @return array
     */
    public function getAllAttributes()
    {
        return array(
            'id' => (int) $this->id,
<?php if (in_array($modelClassSingular, array_keys(\ItemTypes::where('is_graph_relatable')))): ?>
            'node_id' => (int) $this->ensureNode()->id,
<?php endif; ?>
            'item_type' => '<?= $itemTypeSingularRef ?>',
            'item_label' => $this->itemLabel,
            'attributes' => array_merge(
                $this->getListableAttributes(),
                array()
            ),
        );

    }

    public function setCreateAttributes($requestAttributes)
    {
        $this->setItemAttributes($requestAttributes);
    }

    public function setUpdateAttributes($requestAttributes)
    {
        $this->setItemAttributes($requestAttributes);
    }

    /**
     * @inheritdoc
     */
    public function getListableAttributes()
    {
        return array(
<?php
if (!method_exists($model, 'itemTypeAttributes')) {
    throw new Exception("Model ".get_class($model)." does not have method itemTypeAttributes()");
}
$relations = $model->relations();
foreach ($model->itemTypeAttributes() as $attribute => $attributeInfo):

    switch ($attributeInfo["type"]) {
        case "has-many-relation":
        case "many-many-relation":

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = "RestApi".$relationInfo[1];

            // tmp until memory allocation has been resolved (likely via pagination and/or returning metadata about relations instead of the actual objects)
            break;

?>
            '<?=$attribute?>' => RelatedItems::formatItems(
                "<?=$relatedModelClass?>",
                $this-><?=$attribute."\n"?>
            ),
<?php
            break;
        case "has-one-relation":
        case "belongs-to-relation":

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = "RestApi".$relationInfo[1];

?>
            '<?=$attribute?>' => RelatedItems::formatItem(
                "<?=$relatedModelClass?>",
                $this-><?=$attribute."\n"?>
            ),
<?php
            break;
        case "ordinary":
        case "primary-key":
?>
            '<?=$attribute?>' => $this-><?=$attribute?>,
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
    public function getRelatedAttributes()
    {
        $listableAttributes = $this->getListableAttributes();
        // remote attributes that cause recursion here
        return $listableAttributes;
    }

    /**
     * Sets the underlying item attributes.
     */
    public function setItemAttributes($requestAttributes)
    {
<?php
if (!method_exists($model, 'itemTypeAttributes')) {
    throw new Exception("Model ".get_class($model)." does not have method itemTypeAttributes()");
}
$relations = $model->relations();
foreach ($model->itemTypeAttributes() as $attribute => $attributeInfo):

    switch ($attributeInfo["type"]) {
        case "has-many-relation":
        case "many-many-relation":
        case "belongs-to-relation":

            if (!isset($relations[$attribute])) {
                throw new Exception("Model ".get_class($model)." does not have a relation '$attribute'");
            }
            $relationInfo = $relations[$attribute];
            $relatedModelClass = "RestApi".$relationInfo[1];

            // tmp ignore for now - may be implemented later
            // requires some refactoring, proper use of transactions and handling of various edge cases
            break;

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
        $this-><?=$fkAttribute?> = $requestAttributes['attributes']-><?=$attribute?>->id;
<?php
            break;
        case "ordinary":
        case "primary-key":
?>
        $this-><?=$attribute?> = $requestAttributes['attributes']-><?=$attribute?>;
<?php
            break;
        default:
            // ignore
            break;
    }

endforeach;
?>
    }

}
