<?php 

/**
 * This file contains the validators 
 *
 * @author Francis Genet
 * @license MPL / GPLv2 / LGPL
 * @package Provisioner
 * @version 5.0
 */

class Validator {
    static function validateEdit($data, $fields)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields))
                throw new RestException(400, "$key is not suppose to be there");
        }
        return $data;
    }

    // TODO: this function must not allow some attribute as well
    static function validateAdd($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
        }
        return $data;
    }
}
    
?>