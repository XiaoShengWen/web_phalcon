<?php
class IndexController extends BaseController
{
    public function indexAction()
    {
        var_dump("ceshi");
        $name = $this->dispatcher->getControllerName();
        $this->responseJson(Error::ERR_OK, [$name]);
    }
}
