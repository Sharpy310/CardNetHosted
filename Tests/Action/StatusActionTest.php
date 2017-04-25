<?php

namespace liamsorsby\CardNetHosted\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use liamsorsby\CardNetHosted\Action\StatusAction;

class StatusActionTest extends GenericActionTest
{

    protected $requestClass = GetHumanStatus::class;
    protected $actionClass = StatusAction::class;

    /**
     * @test
     */
    public function shouldMarkFailedIfStatusFailed()
    {
        $action = new StatusAction();
        $model = array(
            'status' => "FAILED",
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isFailed());
    }


    /**
     * @test
     */
    public function shouldReturnCapturedWhenApproved()
    {
        $action = new StatusAction();
        $model = array(
            'status' => "APPROVED",
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isCaptured());
    }


    /**
     * @test
     */
    public function shouldMarkFailedIfStatusIsDeclined()
    {
        $action = new StatusAction();
        $model = array(
            'status' => "DECLINED"
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isFailed());
    }
}