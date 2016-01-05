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
     * wrapped in an array containing the item's wrapper attributes (item_label, item_type etc)
     *
     * @return array
     */
    public static function getApiAttributes(\propel\models\<?=$modelClassSingular?> $item)
    {
        return array_merge(
            static::getWrapperAttributes($item),
            [
                'attributes' => static::getItemAttributes($item),
            ]
        );
    }

    /**
     * Returns the item's wrapper attributes (item_label, item_type etc)
     *
     * @return array
     */
    public static function getWrapperAttributes(\propel\models\<?=$modelClassSingular?> $item = null)
    {
        return [
            'id' => $item ? $item->getPrimaryKey() : null,
<?php if (in_array($modelClassSingular, array_keys(\ItemTypes::where('is_graph_relatable')))): ?>
            'node_id' => ($item && $item->getPrimaryKey()) ? (int) /* $item->ensureNode()->id */ -1 : null,
<?php endif; ?>
            'item_type' => '<?= $itemTypeSingularRef ?>',
            'item_label' => ($item && $item->getPrimaryKey()) ? $item->getItemLabel() : '[[none]]',
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
echo $this->render('get-item-attributes.inc.php',
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
        static::setItemAttributes($item, $requestAttributes);
    }

    public static function setUpdateAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
        static::setItemAttributes($item, $requestAttributes);
    }

    /**
     * Sets the item's core attributes based on request attributes.
     */
    public static function setItemAttributes(\propel\models\<?=$modelClassSingular?> $item, $requestAttributes)
    {
<?php
echo $this->render('set-item-attributes.inc.php',
    [
        "itemTypeAttributes" => $itemTypeAttributes,
        "level" => $level = 0,
        "modelClass" => $modelClassSingular,
        "itemReferenceBase" => '$item',
        "requestAttributesReferenceBase" => '$requestAttributes->attributes',
    ]
);
?>
    }

}
