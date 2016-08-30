<?php
use Phalcon\Mvc\Controller;
use App\Models\Exception;

class BaseController extends Controller
{
    /**            
     * @param array $args
     * @return array
     * @throws Exception\BadRequest
     * 
     * 根据配置表，获取对应参数
     * Phalcon的`filter`都是*sanitize*，和PHP的`filter_var`不一样
     */
    protected function getParams(array $args)
    {  
        $params = [];
        foreach ($args as $name => $_args) {
            // get($name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
            $value = $this->request->get($name, ...$_args);
   
            // 改变 $notAllowEmpty 默认行为，返回400响应
            $notAllowEmpty = $_args[2] ?: false;
            if (empty($value) && $notAllowEmpty) { 
                throw new Exception\BadRequest('Invalid Params ($name)', Error::ERR_PARAMS);
            }
            $params[$name] = $value;
        }
        return $params;  
    }
   
    protected function renderMessage($model, $code)
    {
        $messages = $model->getMessage();
        return $this->responseJson($code, $messages);
    }

    // 返回JSON格式内容  
    public function responseJson($code, array $data = [])
    {
        $result = [
            'code' => $code, 
            'msg' => Error::getErrMsg($code),
            'data' => $data, 
        ];
        $this->response->setJsonContent($result);
        $this->response->setStatusCode(200);
        return $this->response->send();
    }
}                   
