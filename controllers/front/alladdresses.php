<?php

require_once __DIR__ . '/../AbstractRESTController.php';

class BinshopsrestAlladdressesModuleFrontController extends AbstractRESTController
{

    protected function processGetRequest()
    {
        $customer = $this->context->customer;
        $psdata = $customer->getSimpleAddresses(
            $this->context->language->id,
            true // no cache
        );

        $this->ajaxRender(json_encode([
            'success' => true,
            'code' => 200,
            'psdata' => $psdata
        ]));
        die;
    }

    protected function processPostRequest(){
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'POST not supported on this path'
        ]));
        die;
    }

    protected function processPutRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'put not supported on this path'
        ]));
        die;
    }

    protected function processDeleteRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'delete not supported on this path'
        ]));
        die;
    }
}

