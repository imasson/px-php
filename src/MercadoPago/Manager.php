<?php

namespace MercadoPago;

use Symfony\Component\Config\Definition\Exception\Exception;

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
     * @var MetaData
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
        $this->_metadataReader = new MetaData();
    }

    /**
     * @param $entity
     *
     */
    public function getEntityMetaData($entity)
    {
        if (isset($this->_entityConfiguration[$entity])) {
            return $this->_entityConfiguration[$entity];
        }

        $this->_entityConfiguration[$entity] = $this->_metadataReader->getMetaData($entity);

        return $this->_entityConfiguration[$entity];
    }

    /**
     * @param        $entity
     * @param string $method
     * @param null   $parameters
     *
     * @return mixed
     */
    public function execute($entity, $method = 'get', $parameters = null)
    {
        if (is_object($entity)) {
            $className = get_class($entity);
        } else {
            $className = $entity;
        }

        $configuration = $this->getEntityMetaData($className);

        $ormFunction = end(debug_backtrace())['function'];
        if (!isset($configuration->methods[$ormFunction])) {
            throw new Exception('ORM method ' . $ormFunction . ' not available for entity:' . $className);
        }


        $query = [];
        $params = [];
        $this->_setDefaultHeaders($query);
        $this->_setIdempotencyHeader($query, $configuration, $method);

        if (isset($configuration->params)) {
            foreach ($configuration->params as $value) {
                $params[$value] = $this->_config->get(strtoupper($value));
            }
            if (count($params) > 0) {
                $query['url_query'] = $params;
            }
        }

        $result = [];
        $this->_attributesToJson($entity, $result, $configuration);
        $query['json_data'] = json_encode($result);

        $response = $this->_client->{$method}($configuration->methods[$ormFunction]['resource'], $query);

        if ($response['code'] == "200" || $response['code'] == "201") {
            $this->_fillFromResponse($entity, $response['body']);
        }
    }

    /**
     *
     * @return mixed
     */
    protected function _fillFromResponse($entity, $data)
    {
        foreach ($data as $key => $value) {
            //if (is_array($value)) {
            //    continue; // TODO build object nested structure
            //}
            $attribute = 'set' . str_replace('_', '', ucwords($key, '_'));
            $entity->$attribute($value);
        }
    }

    /**
     *
     * @return mixed
     */
    protected function _attributesToJson($entity, &$result, $configuration)
    {
        $attributes = array_filter($entity->toArray($configuration->attributes));
        foreach ($attributes as $key => $value) {
            if ($value instanceof Entity) {
                $this->_attributesToJson($value, $result[$key], $configuration);
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
        $metaData = $this->getEntityMetaData($entity);

        return $metaData->attributes[$property]['type'];
    }

    /**
     * @param $query
     */
    protected function _setDefaultHeaders(&$query)
    {
        $query['headers']['Accept'] = 'application/json';
        $query['headers']['User-Agent'] = 'Mercado Pago Php SDK v' . Version::$_VERSION;
    }

    /**
     * @param        $query
     * @param        $configuration
     * @param string $method
     */
    protected function _setIdempotencyHeader($query, $configuration, $method)
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