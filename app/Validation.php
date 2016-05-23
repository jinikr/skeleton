<?php
namespace App;

use Phalcon\Validation\ValidatorInterface;
use Phalcon\Validation as PhalconValidation;

class Validation extends PhalconValidation
{
    /**
     * @param  $field
     * @param  ValidatorInterface $validator
     * @return mixed
     */
    public function add($field, ValidatorInterface $validator)
    {
        if (is_array($field)) {
            foreach ($field as $f) {
                parent::add($f, $validator);
            }

            return $this;
        }

        return parent::add($field, $validator);
    }

    /**
     * @param $data
     * @param $entity
     * @param $messages
     */
    public function afterValidation($data, $entity, $messages)
    {
        if (count($messages)) {
            $errorMessages = [];

            foreach ($messages as $message) {
                array_push($errorMessages, [
                    'message' => $message->getMessage(),
                    'field'   => $message->getField()
                ]);
            }

            throw new \Exception(json_encode($errorMessages));
        }
    }
}
