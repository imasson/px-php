<?php
namespace MercadoPago;
/**
 * CompatibilityTest Class Doc Comment
 *
 * @package MercadoPago
 */
class CompatibilityTest
    extends \PHPUnit_Framework_TestCase
{
    /**
     * @var
     */
    protected $config;
    protected $_entity;

    /**
     *
     */
    protected function setUp()
    {
        $restClient = new RestClient();
        $config = new Config(null, $restClient);
        $manager = new Manager($restClient, $config);
        Entity::setManager($manager);
        $this->_entity = new DummyEntity();
    }

    /**
     */
    public function testAccessToken()
    {
        $api = new \mercadopago('446950613712741', '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv');
        $at = $api->get_access_token();
        $at = array_pop(explode('-', $at));
        $this->assertEquals('192627424', $at);
    }

    /**
     */
    public function testAccessTokenCustom()
    {
        $api = new \mercadopago('APP_USR-446950613712741-101108-fa64a32dca70c9a4721af979ba7c77a4__L_D__-192627424');
        $at = $api->get_access_token();
        $this->assertEquals('APP_USR-446950613712741-101108-fa64a32dca70c9a4721af979ba7c77a4__L_D__-192627424', $at);
    }

}