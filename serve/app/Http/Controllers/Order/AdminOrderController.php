<?php

namespace App\Http\Controllers\Order;

use App\Events\Order\OrderCancelEvent;
use App\Events\Order\OrderCloseEvent;
use App\Events\Order\OrderRefundEvent;
use App\Events\Order\OrderRefundRefuseEvent;
use App\Events\Order\OrderRefundSuccessEvent;
use App\Events\Order\OrderSuccessEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\AdminOrderUpdateRequest;
use App\Http\Resources\Order\AdminOrderDetail;
use App\Http\Resources\Order\OrderCollection;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = new Order();
        $orders = $orders->orderBy('created_at', 'desc');
        $orders = $orders->paginate($request->get('pageSize'));
        return $this->jsonSuccessResponse(new OrderCollection($orders));
    }

    public function show(Order $order)
    {
        return $this->jsonSuccessResponse(new AdminOrderDetail($order));
    }

    public function status(Order $order, Request $request)
    {
        if ($request->has("status")) {
            switch ($request->get('status')) {
                case "closed":
                    event(new OrderCloseEvent($order, $request->get('reason') ?: "商家关闭订单"));
                    break;
                case "refunded":
                    event(new OrderRefundSuccessEvent($order, $request->get('reason')));
                    break;
                case "refund_refuse":
                    event(new OrderRefundRefuseEvent($order, $request->get('reason')));
                    break;
                default:
                    return $this->jsonErrorResponse(404, "无此状态码");
                    break;
            }
            return $this->jsonSuccessResponse();
        } else {
            return $this->jsonErrorResponse(404, "状态必须填写");
        }
    }

    public function update(Order $order, AdminOrderUpdateRequest $request)
    {
        if ($request->has('address')) {
            $address = $request->get('address');
            if (!isset($address['name']) && is_null($address['name'])) return $this->jsonErrorResponse(405, "姓名 不能为空");
            if (!isset($address['province']) && is_null($address['province'])) return $this->jsonErrorResponse(405, "省 不能为空");
            if (!isset($address['city']) && is_null($address['city'])) return $this->jsonErrorResponse(405, "市 不能为空");
            if (!isset($address['detail']) && is_null($address['detail'])) return $this->jsonErrorResponse(405, "详细地址 不能为空");
            if (!isset($address['mobile']) && is_null($address['mobile'])) return $this->jsonErrorResponse(405, "手机 不能为空");

            $address = [
                'name' => $address['name'],
                'province' => $address['province'],
                'city' => $address['city'],
                'district' => isset($address['district']) ? $address['district'] : "",
                'detail' => $address['detail'],
                'mobile' => $address['mobile'],
            ];
            $order->address->update($address);
        }
        if ($request->has('amount')) {
            if ($order->status != Order::ORDER_STATUS_PENDING)
                return $this->jsonErrorResponse(405, "订单不允许更改价格");
            $order->amount = $request->get('amount');
            if($order->amount == 0){
                $order->status = Order::ORDER_STATUS_PROCESSING;
            }
            $order->save();
        }
        return $this->jsonSuccessResponse();
    }
}
