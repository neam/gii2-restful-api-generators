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

            // tmp ignore for now - may be implemented later
            // requires some refactoring, proper use of transactions and handling of various edge cases
            echo "        // {$attributeInfo["type"]} $attribute TODO\n";
            break;
?>
        RelatedItems::set|saveRelatedItems(
            "<?=$attributeInfo['relatedModelClass']?>",
            '<?=$attribute?>',
            <?=$requestAttributesReferenceBase?>-><?=$attribute?>
        ),
<?php
        case "has-one-relation":
        case "belongs-to-relation":

            if (!isset($attributeInfo['relatedModelClass'])) {
                throw new Exception(
                    "$modelClass.$attribute - No relation information available"
                );
            }

            $relatedItemReferenceBase = '$related' . $attributeInfo['relatedModelClass'];

?>
<?php if (array_key_exists('deepAttributes', $attributeInfo)): ?>
        // Existing or new
        if (<?=$requestAttributesReferenceBase?>-><?=$attribute?>->id === null) {
            <?=$relatedItemReferenceBase?> = new \propel\models\<?=$attributeInfo['relatedModelClass']?>;
        } else {
            <?=$relatedItemReferenceBase?> = \propel\models\<?=$attributeInfo['relatedModelClass']?>Query::create()->findPk(
                <?=$requestAttributesReferenceBase?>-><?=$attribute?>->id
            );
        }
<?php
echo $this->render('set-item-attributes.inc.php',
    [
        "itemTypeAttributes" => $attributeInfo['deepAttributes'],
        "level" => $level,
        "modelClass" => $attributeInfo["relatedModelClass"],
        "itemReferenceBase" => $relatedItemReferenceBase,
        "requestAttributesReferenceBase" => $requestAttributesReferenceBase . '->' . $attributeInfo["ref"] . '->attributes',
    ]
);
?>
        <?= $itemReferenceBase ?>-><?=$attributeInfo['relatedItemSetterMethod']?>(<?=$relatedItemReferenceBase?>);
<?php else:
            $camelizedAttribute = Inflector::camelize($attributeInfo['fkAttribute']);
?>
        <?= $itemReferenceBase ?>->set<?=$camelizedAttribute?>(<?=$requestAttributesReferenceBase?>-><?=$attribute?>->id);
<?php endif;

            break;
        case "ordinary":
        case "primary-key":
            $camelizedAttribute = Inflector::camelize($attribute);
?>
        <?= $itemReferenceBase ?>->set<?=$camelizedAttribute?>(<?=$requestAttributesReferenceBase?>-><?=$attribute?>);
<?php
            break;
        default:
            // ignore
            break;
    }

endforeach;
