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
<?php if (in_array($modelClassSingular, array_keys(\ItemTypes::where('is_graph_relatable')))): ?>
            'node_id' => (int) $this->ensureNode()->id,
<?php endif; ?>
            'item_type' => '<?= $itemTypeSingularRef ?>',
            'attributes' => array_merge(
                $this->getListableAttributes(),
                array()
            ),
            'relations' => array_merge(
                $this->getRelationAttributes(),
                array()
            ),
        );

    }

    /**
     * @inheritdoc
     */
    public function getRelationAttributes()
    {
        return array(
<?php
foreach ($model->relations() as $relation => $relationInfo):
$relatedModelClass = $relationInfo[1];
?>
            '<?=$relation?>' => RelatedItems::formatItems(
                "<?=$relatedModelClass?>",
                $this-><?=$relation."\n"?>
            ),
<?php
endforeach;
?>
        );
    }

    /**
     * @inheritdoc
     */
    public function getListableAttributes()
    {
        return array(
            'id' => $this->id,
<?php
foreach ($model->getSafeAttributeNames() as $attributeName):
?>
            '<?=$attributeName?>' => $this-><?=$attributeName?>,
<?php
endforeach;
?>
        );
    }

    /**
     * @inheritdoc
     */
    public function getRelatedAttributes()
    {
        return array_merge(
            array(
                'id' => $this->id,
            ),
            $this->getListableAttributes()
        );
    }

}
