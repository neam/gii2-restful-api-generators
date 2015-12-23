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
     * Returns rest api attributes for this resource, consisting of the item's core attributes
     * wrapped in an array containing the item's virtual attributes (item_label, item_type etc)
     *
     * @return array
     */
    public static function getApiAttributes(\propel\models\<?=$modelClassSingular?> $item)
    {
        return [
            'id' => $item->getPrimaryKey(),
<?php if (in_array($modelClassSingular, array_keys(\ItemTypes::where('is_graph_relatable')))): ?>
            'node_id' => $item->getPrimaryKey() ? (int) /* $item->ensureNode()->id */ -1 : null,
<?php endif; ?>
            'item_type' => '<?= $itemTypeSingularRef ?>',
            'item_label' => $item->getPrimaryKey() ? $item->getItemLabel() : '[[none]]',
            'attributes' => static::getItemAttributes($item),
        ];
    }

    /**
     * Returns the item's core attributes
     *
     * @return array
     */
    public static function getItemAttributes(\propel\models\<?=$modelClassSingular?> $item)
    {
        return [
<?php
echo $this->render('item-type-attributes-data-schema.inc.php',
    [
        "itemTypeAttributes" => $itemTypeAttributes,
        "level" => $level = 0,
        "modelClass" => $modelClassSingular,
        "itemReferenceBase" => '$item',
    ]
);
?>
        ];
    }

    public static function setCreateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        static::setApiAttributes($item, $requestAttributes);
    }

    public static function setUpdateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        static::setApiAttributes($item, $requestAttributes);
    }

    /**
     * Sets the item's core attributes based on request attributes.
     */
    public static function setItemAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        $row = [];
<?php
if (!method_exists($model, 'itemTypeAttributes')) {
    throw new Exception("Model ".get_class($model)." does not have method itemTypeAttributes()");
}
foreach ($itemTypeAttributes as $attribute => $attributeInfo):

    // Deep attributes are handled indirectly via their parent attributes
    if (array_key_exists('throughAttribute', $attributeInfo)) {
        continue;
    }

    switch ($attributeInfo["type"]) {
        case "has-many-relation":
        case "many-many-relation":
        case "belongs-to-relation":

            // tmp ignore for now - may be implemented later
            // requires some refactoring, proper use of transactions and handling of various edge cases
            echo "        // {$attributeInfo["type"]} $attribute TODO\n";
            break;

?>
        RelatedItems::set|saveRelatedItems(
            "<?=$attributeInfo['relatedModelClass']?>",
            '<?=$attribute?>',
            $requestAttributes->attributes-><?=$attribute?>
        ),
<?php
            break;
        case "has-one-relation":
?>
        RelatedItems::setRelatedItemAttributes('<?= $attributeInfo['relatedModelClass'] ?>', $item, $requestAttributes, '<?=$attribute?>', '<?=$attributeInfo['fkAttribute']?>', '<?=$attributeInfo['relatedItemSetterMethod']?>');
<?php
            break;
        case "ordinary":
        case "primary-key":
?>
        $row['<?=$attribute?>'] = $requestAttributes->attributes-><?=$attribute?>;
<?php
            break;
        default:
            // ignore
            break;
    }

endforeach;
?>

        // Use $row contents to set item attributes
        $item->fromArray($row, TableMap::TYPE_FIELDNAME);

    }

}
