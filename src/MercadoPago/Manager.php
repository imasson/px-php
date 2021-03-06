<?php

namespace MercadoPago;

/**
 * Class Manager
 *
 * @package MercadoPago
 */
class Manager
{
    /**
     * @var RestClient
     */
    private $_client;
    /**
     * @var Config
     */
    private $_config;
    /**
     * @var
     */
    private $_entityConfiguration;
    /**
     * @var MetaDataReader
     */
    private $_metadataReader;
    /**
     * @var string
     */
    static $CIPHER = 'sha256';

    /**
     * Manager constructor.
     *
     * @param RestClient $client
     * @param Config     $config
     */
    public function __construct(RestClient $client, Config $config)
    {
        $this->_client = $client;
        $this->_config = $config;
        $this->_metadataReader = new MetaDataReader();
    }

    protected function _getEntityConfiguration($entity)
    {
        $className = $this->_getEntityClassName($entity);
        if (isset($this->_entityConfiguration[$className])) {
            return $this->_entityConfiguration[$className];
        }

        $this->_entityConfiguration[$className] = $this->_metadataReader->getMetaData($entity);

        return $this->_entityConfiguration[$className];
    }

    /**
     * @param        $entity
     * @param string $method
     * @param null   $parameters
     *
     * @return mixed
     */
    public function execute($entity, $method = 'get')
    {
        $configuration = $this->_getEntityConfiguration($entity);

        foreach ($configuration->attributes as $key => $attribute) {
            $this->validateAttribute($entity, $key, ['required']);
        }

        $this->_setDefaultHeaders($configuration->query);
        $this->_setIdempotencyHeader($configuration->query, $configuration, $method);
        $this->setQueryParams($entity);

        return $this->_client->{$method}($configuration->url, $configuration->query);
    }

    public function validateAttribute($entity, $attribute, array $properties, $value = null)
    {
        $configuration = $this->_getEntityConfiguration($entity);
        foreach ($properties as $property) {
            if ($configuration->attributes[$attribute][$property]) {
                $result = $this->_isValidProperty($attribute, $property, $entity, $configuration->attributes[$attribute], $value);
                if (!$result) {
                    throw new \Exception('Error ' . $property . ' in attribute ' . $attribute);
                }
            }
        }
    }

    protected function _isValidProperty($key, $property, $entity, $attribute, $value)
    {
        switch ($property) {
            case 'required':
                return ($entity->{$key} !== null);
            case 'maxLength':
                return (strlen($value) <= $attribute['maxLength']);
            case 'readOnly':
                return !$attribute['readOnly'];
        }

        return true;
    }

    /**
     * @param $entity
     * @param $ormMethod
     *
     * @throws \Exception
     */
    public function setEntityUrl($entity, $ormMethod)
    {
        $className = $this->_getEntityClassName($entity);
        if (!isset($this->_entityConfiguration[$className]->methods[$ormMethod])) {
            throw new \Exception('ORM method ' . $ormMethod . ' not available for entity:' . $className);
        }
        $url = $this->_entityConfiguration[$className]->methods[$ormMethod]['resource'];
        $matches = [];
        preg_match_all('/\\:\\w+/', $url, $matches);
        foreach ($matches[0] as $match) {
            $url = str_replace($match, $entity->{substr($match, 1)}, $url);
        }

        $this->_entityConfiguration[$className]->url = $url;
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
    public function setEntityMetadata($entity)
    {
        return $this->_getEntityConfiguration($this->_getEntityClassName($entity));
    }

    /**
     * @param $entity
     *
     * @return string
     */
    protected function _getEntityClassName($entity)
    {
        if (is_object($entity)) {
            $className = get_class($entity);
        } else {
            $className = $entity;
        }

        return $className;
    }

    /**
     * @param $entity
     */
    public function setEntityQueryJsonData($entity)
    {
        $className = $this->_getEntityClassName($entity);
        $result = [];
        $this->_attributesToJson($entity, $result, $this->_entityConfiguration[$className]);
        $this->_entityConfiguration[$className]->query['json_data'] = json_encode($result);
    }

    /**
     * @param $configuration
     */
    public function setQueryParams($entity, $urlParams = [])
    {
        $configuration = $this->_getEntityConfiguration($entity);
        $params = [];
        if (isset($configuration->params)) {
            foreach ($configuration->params as $value) {
                $params[$value] = $this->_config->get(strtoupper($value));
            }
            if (count($params) > 0) {
                $configuration->query['url_query'] = array_merge($urlParams, $params);
            }
        }
    }

    /**
     * Fill entity from data with nested object creation
     *
     * @param $entity
     * @param $data
     */
    public function fillFromResponse($entity, $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $className = 'MercadoPago\\' . $this->_camelize($key);
                if (class_exists($className)) {
                    $entity->{$key} = new $className;
                    $this->fillFromResponse($entity->{$key}, $value);
                } else {
                    $entity->{$key} = json_decode(json_encode($value));
                }
                continue;
            }
            $entity->{$key} = $value;
        }
    }

    /**
     * @param        $input
     * @param string $separator
     *
     * @return mixed
     */
    protected function _camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }


    /**
     * @param $entity
     * @param $result
     * @param $configuration
     */
    protected function _attributesToJson($entity, &$result)
    {
        if (!is_array($entity)) {
            $attributes = array_filter($entity->toArray());
        } else {
            $attributes = $entity;
        }

        foreach ($attributes as $key => $value) {
            if ($value instanceof Entity || is_array($value)) {
                $this->_attributesToJson($value, $result[$key]);
            } else {
                $result[$key] = $value;
            }
        }
    }

    /**
     * @param $entity
     * @param $property
     *
     * @return mixed
     */
    public function getPropertyType($entity, $property)
    {
        $metaData = $this->_getEntityConfiguration($entity);

        return $metaData->attributes[$property]['type'];
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    public function getDynamicAttributeDenied($entity)
    {
        $metaData = $this->_getEntityConfiguration($entity);

        return isset($metaData->denyDynamicAttribute);
    }

    /**
     * @param $query
     */
    protected function _setDefaultHeaders(&$query)
    {
        $query['headers']['Accept'] = 'application/json';
        $query['headers']['Content-Type'] = 'application/json';
        $query['headers']['User-Agent'] = 'Mercado Pago Php SDK v' . Version::$_VERSION;
    }

    /**
     * @param        $query
     * @param        $configuration
     * @param string $method
     */
    protected function _setIdempotencyHeader(&$query, $configuration, $method)
    {
        if (!isset($configuration->methods[$method])) {
            return;
        }

        $fields = '';
        if ($configuration->methods[$method]['idempotency']) {
            $fields = $this->_getIdempotencyAttributes($configuration->attributes);
        }

        if ($fields != '') {
            $query['headers']['x-idempotency-key'] = hash(self::$CIPHER, $fields);
        }
    }

    /**
     * @param $attributes
     *
     * @return string
     */
    protected function _getIdempotencyAttributes($attributes)
    {
        $result = [];
        foreach ($attributes as $key => $value) {
            if ($value['idempotency']) {
                $result[] = $key;
            }
        }

        return implode('&', $result);
    }
}