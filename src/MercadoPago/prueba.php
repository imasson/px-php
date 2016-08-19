<?php
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

require_once "../../vendor/autoload.php";
use MercadoPago\Entities;

//AnnotationRegistry::registerLoader('class_exists');
//AnnotationRegistry::loadAnnotationClass('MercadoPago\\Annotation\\RestMethod');

//$reader = new AnnotationReader();
//$converter = new StandardObjectConverter($reader);

$payment = new Entities\Shared\Payment();
//$standardObject = $converter->convert($payment);
$rest = new \MercadoPago\RestClient();
$config = new \MercadoPago\Config(null, $rest);

$config->set('ACCESS_TOKEN', 'TEST-446950613712741-091715-092a6109a25bb763aa94c61688ada0cd__LC_LA__-192627424');
$config->set('CLIENT_ID', '446950613712741');
$config->set('CLIENT_SECRET', '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv');
$rest->setHttpParam('address', $config->get('base_url'));

$manager = new MercadoPago\Manager($rest, $config);
\MercadoPago\Entity::setManager($manager);


$customer = new Entities\Flavor1\Customer();
$customer->setEmail("test_user_58666377@testuser.com");
$customer->setId("204943005-G4n3trGt71WZEb");

$payment->setToken('ac6ac0b8d9dc5189030ab4ac6aaede25');
$payment->setPaymentMethodId('visa');
$payment->setTransactionAmount(100);
$payment->setInstallments('1');
$payment->setPayer($customer);

$payment->save();


var_dump($payment->toArray());
