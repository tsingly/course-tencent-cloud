<?php

namespace App\Http\Api\Controllers;

use App\Services\Logic\Refund\RefundCancel as RefundCancelService;
use App\Services\Logic\Refund\RefundConfirm as RefundConfirmService;
use App\Services\Logic\Refund\RefundCreate as RefundCreateService;
use App\Services\Logic\Refund\RefundInfo as RefundInfoService;

/**
 * @RoutePrefix("/api/refund")
 */
class RefundController extends Controller
{

    /**
     * @Get("/confirm", name="api.refund.confirm")
     */
    public function confirmAction()
    {
        $sn = $this->request->getQuery('sn', 'string');

        $service = new RefundConfirmService();

        $confirm = $service->handle($sn);

        return $this->jsonSuccess(['confirm' => $confirm]);
    }

    /**
     * @Get("/info", name="api.refund.info")
     */
    public function infoAction()
    {
        $sn = $this->request->getQuery('sn', 'string');

        $service = new RefundInfoService();

        $refund = $service->handle($sn);

        return $this->jsonSuccess(['refund' => $refund]);
    }

    /**
     * @Post("/create", name="api.refund.create")
     */
    public function createAction()
    {
        $service = new RefundCreateService();

        $refund = $service->handle();

        $service = new RefundInfoService();

        $refund = $service->handle($refund->sn);

        return $this->jsonSuccess(['refund' => $refund]);
    }

    /**
     * @Post("/cancel", name="api.refund.cancel")
     */
    public function cancelAction()
    {
        $sn = $this->request->getPost('sn', 'string');

        $service = new RefundCancelService();

        $service->handle($sn);

        $service = new RefundInfoService();

        $refund = $service->handle($sn);

        return $this->jsonSuccess(['refund' => $refund]);
    }

}
