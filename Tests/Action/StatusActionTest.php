<?php

namespace liamsorsby\CardNetHosted\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use liamsorsby\CardNetHosted\Action\StatusAction;
use liamsorsby\CardNetHosted\Constants;

class StatusActionTest extends GenericActionTest
{

    protected $requestClass = GetHumanStatus::class;
    protected $actionClass = StatusAction::class;

    /**
     * @test
     */
    public function shouldMarkNewIfDetailsEmpty()
    {
        $action = new StatusAction();
        $model = [];
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isNew());
    }


    /**
     * @test
     */
    public function shouldMarkFailedIfDetailsHasErrorSet()
    {
        $action = new StatusAction();
        $model = [
            'error' => [
                'type' => 'invalid_request_error',
                'message' => 'Amount must be at least 50 cents',
                'param' => 'amount',
            ],
        ];
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isFailed());
    }


    /**
     * @test
     */
    public function shouldMarkFailedIfStatusFailed()
    {
        $action = new StatusAction();
        $model = array(
            'status' => Constants::STATUS_FAILED,
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isFailed());
    }


    /**
     * @test
     */
    public function shouldMarkRefundedIfStatusSetAndRefundedTrue()
    {
        $action = new StatusAction();
        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'refunded' => true,
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isRefunded());
    }


    /**
     * @test
     */
    public function shouldNotMarkRefundedIfStatusNotSetAndRefundedTrue()
    {
        $action = new StatusAction();
        $model = array(
            'refunded' => true,
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertFalse($status->isRefunded());
        $this->assertTrue($status->isNew());
    }


    /**
     * @test
     */
    public function shouldMarkCapturedIfStatusSucceededAndCaptureAndPaidSetTrue()
    {
        $action = new StatusAction();
        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => true,
            'paid' => true,
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertTrue($status->isCaptured());
    }


    /**
     * @test
     */
    public function shouldNotMarkCapturedIfStatusSucceededAndCaptureSetTrueButPaidNotTrue()
    {
        $action = new StatusAction();
        $model = array(
            'status' => Constants::STATUS_SUCCEEDED,
            'captured' => true,
            'paid' => false,
        );
        $action->execute($status = new GetHumanStatus($model));
        $this->assertFalse($status->isCaptured());
        $this->assertTrue($status->isUnknown());
    }
}