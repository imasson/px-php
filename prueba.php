<?php
require "vendor/autoload.php";
//require_once "mercadopago.php";
//
//MercadoPago\MercadoPagoSdk::initialize();
//MercadoPago\MercadoPagoSdk::config()->set('ACCESS_TOKEN', 'TEST-446950613712741-091715-092a6109a25bb763aa94c61688ada0cd__LC_LA__-192627424');
//MercadoPago\MercadoPagoSdk::config()->set('CLIENT_ID', '2272101328791208');
//MercadoPago\MercadoPagoSdk::config()->set('CLIENT_SECRET', 'cPi6Mlzc7bGkEaubEJjHRipqmojXLtKm');

//$preference = new MercadoPago\Preference();
////
//$item = new MercadoPago\Item();
//$item->id = "item-ID-1234";
//$item->title = "Practical Granite Shirt";
//
//$item->external_reference = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ultricies est sed elit interdum, quis consectetur libero mollis. ";
//$item->picture_url = "http://placehold.it/350x150";
////$item->currency_id = "USD";
//$item->quantity = 1;
//$item->unit_price = 14.5;
//$item->category_id = 'art';
//
//$preference->items = [$item];
//
//$payer = new MercadoPago\Payer();
//$payer->name = "test";
//$payer->surname = "user";
//$payer->email = "test_user_98856744@testuser.com";
//$payer->phone = ["area_code" => 1234,
//                 "number"    => 12345678];
//$payer->identification = ["type"   => "DNI",
//                          "number" => "12345678"];
//$payer->address = ["street_name"   => 'Faker::Address.street_name',
//                   "street_number" => 'Faker::Address.building_number',
//                   "zip_code"      => '7000'];
//
//$preference->payer = $payer;

//$preference->save();
//$customer = new MercadoPago\Customer();
//$customer->email = 'test_user_62249480@testuser.com';
////$customer->save();
////$customer->email = 'test_user_99529216@testuser.com';
//$payment = new MercadoPago\Payment();
//$payment->token = '78a6a294634114a858925518ded88f75';
//$payment->payment_method_id = 'visa';
//$payment->transaction_amount = 100;
//$payment->installments = '1';
//$payment->payer = ['email' => $customer->email];

//$payment->save();

$api = new mercadopago('446950613712741','0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv');
$preapproval_data = array(
    "payer_email" => "test_user_58666377@testuser.com",
    "back_url" => "http://63ce236d.ngrok.io/mercadopago/notifications/standard",
    "reason" => "Monthly subscription to premium package",
    "external_reference" => "OP-1234",
    "auto_recurring" => array(
        "frequency" => 1,
        "frequency_type" => "months",
        "transaction_amount" => 60,
        "currency_id" => "ARS",
        "start_date" => "2016-12-10T14:58:11.778-03:00",
        "end_date" => "2017-06-10T14:58:11.778-03:00"
    )
);

//$preapproval = $api->create_preapproval_payment($preapproval_data);
$api->get_preapproval_payment('53dfb6edc4434d1ebd8803c3e9154d80');
//MercadoPago\MercadoPagoSdk::config()->set('ACCESS_TOKEN', 'TEST-446950613712741-091715-092a6109a25bb763aa94c61688ada0cd__LC_LA__-192627424');
//MercadoPago\MercadoPagoSdk::config()->set('CLIENT_ID', );
//MercadoPago\MercadoPagoSdk::config()->set('CLIENT_SECRET', '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv');
//$api->get_payment(1769531);