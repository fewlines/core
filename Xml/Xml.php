<?php
namespace Fewlines\Core\Xml;

use Fewlines\Core\Helper\PathHelper;
use Fewlines\Core\Helper\ArrayHelper;
use Fewlines\Core\Xml\Tree;
use Fewlines\Core\Xml\Tree\Element;

class Xml
{
    /**
     * @var string
     */
    private $version = '1.0';

    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * Holds the plain xml element
     *
     * @var \Fewlines\Core\Xml\Tree
     */
    private $tree;

    /**
     * The config file loaded
     *
     * @var string
     */
    private $file;

    /**
     * Defines if an error occured
     * while parsing
     *
     * @var boolean
     */
    private $valid = false;

    /**
     * Creates a new xml config object
     *
     * @param string $file
     */
    public function __construct($file) {
        $this->file = $file;

        try {
            $this->tree = new Tree(@new \SimpleXMLElement($file, 0, true));
            $this->valid = true;
        }
        catch(\Exception $e) {
            $this->tree = new Tree();
            $this->valid = false;
        }
    }

    /**
     * Gets the tree instance
     *
     * @return array
     */
    public function getTree() {
        return $this->tree;
    }

    /**
     * Gets the tree Element
     *
     * @return Element
     */
    public function getTreeElement() {
        return $this->tree->getElement();
    }

    /**
     * @param \SimpleXmlElement|Element $node
     */
    public function addChild($node) {
        $this->tree->addChild($node);
    }

    /**
     * @return boolean
     */
    public function isValid() {
        return $this->valid;
    }

    /**
     * @return boolean
     */
    public function hasRoot() {
        return $this->tree->hasRoot();
    }

    /**
     * Gets all element by a
     * path sequence. It creates
     * the list only for the last path
     * segment
     *
     * @param  string  $path
     * @param  boolean $collect
     * @return \Fewlines\Core\Xml\Tree\Element|boolean|array
     */
    public function getElementsByPath($path, $collect = true) {
        $parts = explode("/", $path);
        $parts = ArrayHelper::clean($parts);
        $rootName = $parts[0];
        $treeElement = $this->getTreeElement();

        if ($treeElement->getName() != $rootName || (!$this->isValid() || !$this->tree->hasRoot())) {
            return false;
        }

        $result = $treeElement;
        $resultList = array();

        for ($i = 1, $partsLen = count($parts); $i < $partsLen; $i++) {
            if (true == $collect && $i == $partsLen - 1) {
                $resultList = $result->getChildrenByName($parts[$i]);
            }

            $result = $result->getChildByName($parts[$i]);
        }

        if (true == $collect) {
            if (false == empty($resultList)) {
                return $resultList;
            }
            else {
                if (false == empty($result)) {
                    return array($result);
                }
                else {
                    return array();
                }
            }
        }

        return $result;
    }

    /**
     * Gets one element from
     * the tree with a given path
     * sequence
     *
     * @param  string $path
     * @return \Fewlines\Core\Xml\Tree\Element|boolean
     */
    public function getElementByPath($path) {
        return $this->getElementsByPath($path, false);
    }

    /**
     * Saves the xml structure to the file
     */
    public function save() {
        $writer = new \XMLWriter();

        $writer->openURI($this->file);
        $writer->startDocument($this->version, $this->encoding);
        $writer->setIndent(true);
        $writer->setIndentString("\t");

        $this->tree->getElement()->save($writer);

        $writer->endDocument();
        $writer->flush();
    }
}
