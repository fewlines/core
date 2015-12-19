<?php
namespace Fewlines\Core\Template;

use Fewlines\Core\Helper\PathHelper;

class Layout
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * Tells if the layout should be
     * rendered or not
     *
     * @var boolean
     */
    private $disabled = false;

    /**
     * @param string $name
     * @param string $path
     * @param array  $routeUrlParts
     */
    public function __construct($name, $path) {
        $this->setName($name);
        $this->path = PathHelper::normalizePath($path);
    }

    /**
     * @param boolean $isDisabled
     */
    public function disable($isDisabled) {
        $this->disabled = $isDisabled;
    }

    /**
     * @return boolean
     */
    public function isDisabled() {
        return $this->disabled;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path . $this->name . '.' . LAYOUT_FILETYPE;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return \Fewlines\Core\Template\View
     */
    public function getView() {
        return $this->view;
    }

    /**
     * @param string $path the path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @param string $path the path
     */
    public function setName($name) {
        $this->name = pathinfo($name, PATHINFO_FILENAME);
    }
}
