<?php
namespace App\Controllers\Api\Expedia;

use App\Validation;
use Peanut\Phalcon\Pdo\Mysql as Db;
use Phalcon\Validation\Validator\Regex;
use App\Models\Expedia\Code as CodeModel;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use App\Models\Expedia\CodeType as CodeTypeModel;

class Code extends \Phalcon\Mvc\Controller
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @return mixed
     */
    public function getList()
    {
        $request  = $this->request;
        $response = $this->response;

        // validate input
        $validation = new Validation();
        $validation
            ->add(['page', 'per_page', 'type'], new Regex([
                'pattern'    => '/\d+/',
                'allowEmpty' => true
            ]))
            ->validate($request->getQuery());

        $offset = $request->get('page', 'int', 0);
        $limit  = $request->get('per_page', 'int', 10);
        $typeId = $request->get('type', 'int', 0);

        list($total, $results) = CodeModel::getList($offset, $limit, $typeId);

        return $response
            ->setContentType('application/json')
            ->setJsonContent([
                'total'   => $total,
                'results' => $results
            ]);
    }

    /**
     * @param $data
     */
    private function codeValidate($data)
    {
        $validation = new Validation();
        $validation
            ->add('type_id', new Regex([
                'pattern' => '/\d+/'
            ]))
            ->add('name', new PresenceOf())
            ->add('name', new StringLength([
                'max' => 50,
                'min' => 2
            ]))
            ->add('name', new Regex([
                'pattern' => '/^\S.*\S$/'
            ]))
            ->add('description', new Regex([
                'pattern'    => '/^\S.*\S$/',
                'allowEmpty' => true
            ]))
            ->validate($data);

        if (!CodeTypeModel::get($data['type_id'])) {
            throw new \Exception('invalid type id!');
        }

        if (array_key_exists('sub_codes', $data) && is_array($data['sub_codes'])) {
            $validation = new Validation();
            $validation->add('id', new Regex([
                'pattern' => '/\d+/'
            ]));

            foreach ($data['sub_codes'] as $codeId) {
                $validation->validate(['id' => $codeId]);
            }
        }

        return [
            'type_id'     => $data['type_id'],
            'name'        => $data['name'],
            'description' => array_key_exists('description', $data) ? $data['description'] : '',
            'sub_codes'   => $data['sub_codes']
        ];
    }

    /**
     * @return mixed
     */
    public function post()
    {
        $request  = $this->request;
        $response = $this->response;

        $code = $this->codeValidate($request->getJsonRawBody(true));

        // create code
        $id = Db::name('master')->transaction(function () use ($code) {
            return CodeModel::create($code);
        });

        if (!$id) {
            throw new \Exception('create code failed!');
        }

        return $response
            ->setContentType('application/json')
            ->setJsonContent(CodeModel::get($id, 'master'));
    }

    /**
     * @param $id
     */
    public function checkCodeId($id)
    {
        // validate input
        $validation = new Validation();
        $validation
            ->add('id', new Regex([
                'pattern' => '/\d+/'
            ]))
            ->validate(['id' => $id]);

        $this->data = CodeModel::get($id);

        if (!$this->data) {
            throw new \Exception('not found code id!');
        }
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->response
            ->setContentType('application/json')
            ->setJsonContent($this->data);
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function put($id)
    {
        $request  = $this->request;
        $response = $this->response;

        $inputData = $request->getJsonRawBody(true);
        $code      = $this->data;

        foreach ($inputData as $key => $value) {
            if (array_key_exists($key, $code)) {
                $code[$key] = $value;
            }
        }

        $code       = $this->codeValidate($code);
        $code['id'] = $this->data['id'];
        $result     = Db::name('master')->transaction(function () use ($code) {
            return CodeModel::update($code);
        });

        if (!$result) {
            throw new \Exception('update code failed!');
        }

        return $response
            ->setContentType('application/json')
            ->setJsonContent(CodeModel::get($code['id'], 'master'));
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function patch($id)
    {
        $request  = $this->request;
        $response = $this->response;

        $code       = $this->codeValidate($request->getJsonRawBody(true));
        $code['id'] = $this->data['id'];
        $result     = Db::name('master')->transaction(function () use ($code) {
            return CodeModel::update($code);
        });

        if (!$result) {
            throw new \Exception('update code failed!');
        }

        return $response
            ->setContentType('application/json')
            ->setJsonContent(CodeModel::get($code['id'], 'master'));
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function delete($id)
    {
        $result = CodeModel::delete($this->data['id']);

        if (!$result) {
            throw new \Exception('delete code failed!');
        }

        return $this->response->setStatusCode(204);
    }
}
