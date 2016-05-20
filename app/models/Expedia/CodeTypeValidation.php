<?php
namespace App\Models\Expedia;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Filter;

class CodeTypeValidation extends Validation
{
    public function initialize()
    {
        $this->add(
            'name',
            new PresenceOf()
        )
        ->add(
            'name',
            new StringLength([
              'max' => 50,
              'min' => 2
            ])
        )
        ->add(
            'name',
            new Regex([
                'pattern' => '/^\S.*\S$/'
            ])
        );

        $this->add(
            'parent_id',
            new Regex([
                'pattern' => '/\d+/'
            ])
        );
    }

    public function afterValidation($data, $entity, $messages)
    {
        if (count($messages)) {
            $errorMessages = [];
            foreach ($messages as $message) {
                array_push($errorMessages, [
                    'message' => $message->getMessage(),
                    'field' => $message->getField()
                ]);
            }
            throw new \Exception(json_encode($errorMessages));
        }

        if ($data['parent_id']) {
            $id = $this->filter->sanitize($data['parent_id'], Filter::FILTER_INT);
            $parentCodeType = CodeType::get($id);
            if (!$parentCodeType) {
                throw new \Exception('invalid parameter', 400);
            }
        }
    }
}
