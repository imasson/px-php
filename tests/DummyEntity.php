<?php
namespace MercadoPago;

use MercadoPago\Annotation\RestMethod;
use MercadoPago\Annotation\RequestParam;
use MercadoPago\Annotation\Attribute;

/**
 * @RestMethod(resource="/dummies", method="list", idempotency=true)
 * @RestMethod(resource="/dummy/:id", method="read")
 * @RestMethod(resource="/dummy/:id", method="update")
 * @RestMethod(resource="/v1/payments", method="save")
 * @RequestParam(param="access_token")
 */

class DummyEntity extends Entity
{
    /**
     * @Attribute(primaryKey = true, type="integer", idempotency=true)
     */
    protected $id;

    /**
     * @Attribute(type = "string")
     */
    protected $title;
    /**
     * @Attribute(type = "string")
     */
    protected $desc;
    /**
     * @Attribute(type = "float")
     */
    protected $price;
    /**
     * @Attribute(type = "int")
     */
    protected $quantity;
    /**
     * @Attribute(type = "date")
     */
    protected $registered_at;

    /**
     * @Attribute(type = "stdClass")
     */
    protected $object;

    /**
     * @Attribute()
     */
    protected $other;

    /**
     * @Attribute(readOnly="true")
     */
    protected $readOnlyAttribute;

    /**
     * @Attribute(maxLength=20)
     */
    protected $maxLengthAttribute;

}