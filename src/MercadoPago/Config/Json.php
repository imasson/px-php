<?php
namespace MercadoPago\Config;
use Exception;

class Json implements ParserInterface
{

    public function parse($path)
    {
        $data = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message  = 'Syntax error';
            if (function_exists('json_last_error_msg')) {
                $error_message = json_last_error_msg();
            }
            $error = array(
                'message' => $error_message,
                'type'    => json_last_error(),
                'file'    => $path,
            );
            throw new Exception($error);
        }
        return $data;
    }
    
    public function getSupportedExtensions()
    {
        return array('json');
    }
}