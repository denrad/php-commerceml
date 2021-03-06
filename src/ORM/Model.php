<?php namespace Zenwalker\CommerceML\ORM;

use Zenwalker\CommerceML\CommerceML;

/**
 * Class Model
 *
 * @package Zenwalker\CommerceML\ORM
 * @property string id
 * @property string name
 * @property string value
 */
abstract class Model extends \ArrayObject
{
    private $namespaceRegistered = false;

    /**
     * @return array
     */
    public function defaultProperties()
    {
        return [
            'Ид' => 'id',
            'Наименование' => 'name',
            'Значение' => 'value',
        ];
    }

    /**
     * @return string
     */
    public function getClearId()
    {
        return (string)explode('#', $this->id)[0];
    }

    /**
     * @return string
     */
    public function getIdSuffix()
    {
        return (string)\array_slice(explode('#', (string)$this->id), 1)[0];
    }

    /**
     * Model constructor.
     *
     * @param CommerceML $owner
     * @param \SimpleXMLElement|null $xml
     */
    public function __construct(CommerceML $owner, \SimpleXMLElement $xml = null)
    {
        $this->owner = $owner;
        $this->xml = $xml ?: $this->loadXml();
        $this->init();
        parent::__construct();
    }

    /**
     * @param $name
     * @return mixed|null|\SimpleXMLElement|string
     */
    public function __get($name)
    {
        if (method_exists($this, $method = 'get' . ucfirst($name))) {
            return call_user_func([$this, $method]);
        }
        if ($this->xml) {
            $attributes = $this->xml;
            if (isset($attributes[$name])) {
                return (string)$attributes[$name];
            }
            if ($value = $this->xml->{$name}) {
                return $value;
            }
            if ($idx = array_search($name, $this->defaultProperties())) {
                return (string)$this->xml->{$idx};
            }
        }
        return null;
    }

    public function __set($name, $value)
    {
    }

    public function __isset($name)
    {
    }

    public function loadXml()
    {
        $this->registerNamespace();
        return null;
    }

    /**
     * @var CommerceML
     */
    public $owner;
    /**
     * @var \SimpleXMLElement
     */
    public $xml;

    public function init()
    {
        $this->registerNamespace();
    }

    protected function registerNamespace()
    {
        if ($this->xml && !$this->namespaceRegistered && ($namespaces = $this->xml->getNamespaces())) {
            $this->namespaceRegistered = true;
            foreach ($namespaces as $namespace) {
                $this->xml->registerXPathNamespace('c', $namespace);
            }
        }
    }

    /**
     * Лучше использовать данный метод, вместо стандартного xpath у SimpleXMLElement,
     * т.к. есть проблемы с неймспейсами xmlns
     *
     * Для каждого элемента необходимо указывать наймспейс "c", например:
     * //c:Свойство/c:ВариантыЗначений/c:Справочник[c:ИдЗначения = '{$id}']
     *
     * @param string $path
     * @return \SimpleXMLElement[]
     */
    public function xpath($path)
    {
        $this->registerNamespace();
        if (!$this->namespaceRegistered) {
            $path = str_replace('c:', '', $path);
        }
        return $this->xml->xpath($path);
    }
}
