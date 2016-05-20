<?php
namespace App\Controllers\Api\Expedia;

use App\Models\Expedia\CodeType as CodeTypeModel;
use Phalcon\Filter;

class CodeType extends \Phalcon\Mvc\Controller
{
    use \App\Traits\Sanitize;

    private $_codeType = [];

    public function getList()
    {
        $request  = $this->request;
        $response = $this->response;

        $offset   = $request->get('page', Filter::FILTER_INT, 0, true);
        $limit    = $request->get('per_page', Filter::FILTER_INT, 10, true);
        $parentId = $request->get('parent_id', Filter::FILTER_INT, 0, true);

        $total  = CodeTypeModel::getTotalCount($parentId);
        $result = CodeTypeModel::getList($offset, $limit, $parentId);
        return $response
            ->setContentType('application/json')
            ->setContent(json_encode([
                'total'   => $total,
                'results' => $result
            ]));
    }

    public function post()
    {
        $request  = $this->request->getJsonRawBody(true);
        $response = $this->response;

        // create code-type
        $id = CodeTypeModel::create($request);
        if (!$id) {
            throw new \Exception('create code-type failed!');
        }

        // response
        $codeType = CodeTypeModel::get($id, 'master');
        return $response
            ->setContentType('application/json')
            ->setContent(json_encode($codeType));
    }

    public function checkCodeTypeId($id)
    {
        $codeTypeId = $this->sanitize($id, null, Filter::FILTER_INT, 0);
        $this->_codeType = CodeTypeModel::get($codeTypeId);
        if (!$this->_codeType) {
            throw new \Exception('not found code-type id!');
        }
    }

    public function get($id)
    {
        return $this->response
            ->setContentType('application/json')
            ->setContent(json_encode($this->_codeType));
    }

    public function put($id)
    {
        $request  = $this->request->getJsonRawBody(true);
        $response = $this->response;

        $codeType = $this->_codeType;
        foreach ($request as $key => $value) {
            if (array_key_exists($key, $codeType)) {
                $codeType[$key] = $value;
            }
        }

        $codeType['id'] = $this->_codeType['id'];
        $result = CodeTypeModel::update($codeType);
        if (!$result) {
            throw new \Exception('update code-type failed!');
        }

        // response
        $codeType = CodeTypeModel::get($codeType['id'], 'master');
        return $response
            ->setContentType('application/json')
            ->setContent(json_encode($codeType));
    }

    public function patch($id)
    {
        $request  = $this->request->getJsonRawBody(true);
        $response = $this->response;

        $codeType = $request;

        $codeType['id'] = $this->_codeType['id'];
        $result = CodeTypeModel::update($codeType);
        if (!$result) {
            throw new \Exception('update code-type failed!');
        }

        // response
        $codeType = CodeTypeModel::get($codeType['id'], 'master');
        return $response
            ->setContentType('application/json')
            ->setContent(json_encode($codeType));
    }

    public function delete($id)
    {
        $result = CodeTypeModel::delete($this->_codeType['id']);
        if (!$result) {
            throw new \Exception('delete code-type failed!');
        }
        return $this->response
            ->setStatusCode(204);
    }
}
