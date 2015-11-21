<?php
/*
 * WellCommerce Open-Source E-Commerce Platform
 *
 * This file is part of the WellCommerce package.
 *
 * (c) Adam Piotrowski <adam@wellcommerce.org>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace WellCommerce\CoreBundle\Component\Form\Formatter;

use WellCommerce\CoreBundle\Component\Form\Dependencies\DependencyInterface;
use WellCommerce\CoreBundle\Component\Form\Elements\Attribute;
use WellCommerce\CoreBundle\Component\Form\Elements\AttributeCollection;
use WellCommerce\CoreBundle\Component\Form\Elements\ElementInterface;
use Zend\Json\Expr;
use Zend\Json\Json;

/**
 * Class JavascriptFormatter
 *
 * @author Adam Piotrowski <adam@wellcommerce.org>
 */
class JavascriptFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatAttributes(array $attributes = [])
    {
        $json    = Json::encode($attributes, false, ['enableJsonExprFinder' => true]);
        $content = Json::prettyPrint($json, ['indent' => '    ']);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function formatElement(ElementInterface $element)
    {
        $collection = new AttributeCollection();
        $element->prepareAttributesCollection($collection);

        return $this->formatAttributesCollection($collection);
    }

    /**
     * {@inheritdoc}
     */
    public function formatAttributesCollection(AttributeCollection $collection)
    {
        $attributes = [];
        $collection->forAll(function (Attribute $attribute) use (&$attributes) {
            $attributes[$attribute->getName()] = $this->formatAttributeValue($attribute);
        });

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function formatDependencies(array $dependencies)
    {
        $formattedDependencies = [];
        foreach ($dependencies as $dependency) {
            $formattedDependencies[] = $this->formatDependency($dependency);
        }

        return $formattedDependencies;
    }

    /**
     * Formats single dependency
     *
     * @param DependencyInterface $dependency
     *
     * @return Expr
     */
    protected function formatDependency(DependencyInterface $dependency)
    {
        return new Expr($dependency->renderJs());
    }

    /**
     * Formats attributes value
     *
     * @param Attribute $attribute
     *
     * @return mixed|Expr
     */
    protected function formatAttributeValue(Attribute $attribute)
    {
        $value = $attribute->getValue();

        if ($attribute->getType() === Attribute::TYPE_FUNCTION && strlen($value)) {
            return new Expr($value);
        }

        return $value;
    }
}