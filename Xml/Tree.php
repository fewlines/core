<?php

namespace Fewlines\Core\Xml;

use Fewlines\Core\Xml\Tree\Element;

class Tree
{
	/**
	 * Array of all elements
	 * (recursive)
	 *
	 * @var \Fewlines\Core\Xml\Tree\Element
	 */
	private $tree;

	/**
	 * Init the tree and create elements
	 *
	 * @param \SimpleXmlElement $root
	 */
	public function __construct($root = null)
	{
		if ($root instanceof \SimpleXmlElement) {
			// "Transform" the node to a element
			$this->tree = new Element($root->getName(),
				(array) $root->attributes(), trim((string) $root));

			// Add all child elements
			foreach($root as $node)
			{
				$this->addChild($node, $this->tree);
			}
		}
		else {
			$this->tree = null;
		}
	}

	/**
	 * Add the node to a position under the tree
	 *
	 * @param \SimpleXmlElement|Element $node
	 * @param Element $parent
	 */
	public function addChild($node, $parent = null)
	{
		if ($node instanceof \SimpleXmlElement) {
			$name       = $node->getName();
			$attributes = (array) $node->attributes();
			$content    = trim((string) $node);
			$element    = new Element($name, $attributes, $content);

			if ( ! $this->tree) {
				$this->tree = $element;
			}
			else {
				if ( ! $parent) {
					$parent = $this->tree;
				}

				$parent->addChild($element);
			}

			// Add child elements recursive
			if($node->count() > 0)
			{
				foreach($node as $childNode)
				{
					$this->addChild($childNode, $element);
				}
			}
		}
		else if ($node instanceof Element) {
			if ( ! $this->tree) {
				$this->tree = $node;
			}
			else {
				if ( ! $parent) {
					$parent = $this->tree;
				}

				$parent->addChild($node);
			}
		}
	}

	/**
	 * Returns the element of the tree
	 *
	 * @return \Fewlines\Core\Xml\Tree\Element
	 */
	public function getElement()
	{
		return $this->tree;
	}

	/**
	 * Tells if the tree has a root element
	 *
	 * @return boolean
	 */
	public function hasRoot()
	{
		return $this->tree != null;
	}
}