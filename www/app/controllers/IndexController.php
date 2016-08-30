<?php
class IndexController extends BaseController
{
    public function indexAction()
    {
        $name = $this->dispatcher->getControllerName();
        return $this->responseJson(Error::ERR_OK, [$name]);
    }
}
