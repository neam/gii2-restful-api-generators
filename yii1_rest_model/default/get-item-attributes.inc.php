<?php

use yii\helpers\Inflector;

$modelClassSingular = $modelClass;
$modelClassSingularWords = Inflector::camel2words($modelClassSingular);
$itemTypeSingularRef = Inflector::camel2id($modelClassSingular, '_');
$modelClassPluralWords = Inflector::pluralize($modelClassSingularWords);
$modelClassPlural = Inflector::camelize($modelClassPluralWords);

if ($level > 1) {
    echo "// Recursion limit reached at level $level\n";
    return;
}
$level++;

foreach ($itemTypeAttributes as $attribute => $attributeInfo):

    // Deep attributes are handled indirectly via their parent attributes
    if (strpos($attribute, '/') !== false) {
        continue;
    }

    switch ($attributeInfo["type"]) {
        case "has-many-relation":
        case "many-many-relation":

            if (!isset($attributeInfo['relatedModelClass'])) {
?>
            // "<?=$modelClass?>.<?=$attribute?>" - No relation information available
<?php
                break;
            }

            // tmp until memory allocation has been resolved (likely via pagination and/or returning metadata about relations instead of the actual objects)
            break;

            // hint that an array is expected
?>
            '<?=$attribute?>' => [],
<?php
            break;
        case "has-one-relation":
        case "belongs-to-relation":

            if (!isset($attributeInfo['relatedModelClass'])) {
                throw new Exception(
                    "$modelClass.$attribute - No relation information available"
                );
            }

            $relatedItemReferenceBase = $itemReferenceBase . '->' . $attributeInfo["relatedItemGetterMethod"] . '()';

?>
            '<?=$attribute?>' => [
                'id' => $relatedPk = (<?= $relatedItemReferenceBase ?> ? <?= $relatedItemReferenceBase ?>->getPrimaryKey() : null),
<?php if (in_array($modelClass, array_keys(\ItemTypes::where('is_graph_relatable')))): ?>
                'node_id' => $relatedPk ? (int) /* <?= $relatedItemReferenceBase ?>->ensureNode()->id */ -1 : null,
<?php endif; ?>
                'item_type' => '<?= $itemTypeSingularRef ?>',
                'item_label' => $relatedPk ? <?= $relatedItemReferenceBase ?>->getItemLabel() : '[[none]]',
<?php if (array_key_exists('deepAttributes', $attributeInfo)): ?>
                'attributes' => [
<?php
echo $this->render('get-item-attributes.inc.php',
    [
        "itemTypeAttributes" => $attributeInfo['deepAttributes'],
        "level" => $level,
        "modelClass" => $attributeInfo["relatedModelClass"],
        "itemReferenceBase" => $relatedItemReferenceBase,
    ]
);
?>
                ],
<?php endif; ?>
            ],
<?php
            break;
        case "ordinary":
        case "primary-key":
            $camelizedAttribute = Inflector::camelize($attribute);
?>
            '<?=$attribute?>' => <?= $itemReferenceBase ?>->get<?=$camelizedAttribute?>("Y-m-d H:i:s"),
<?php
            break;
        default:
            // ignore
            break;
    }

endforeach;
