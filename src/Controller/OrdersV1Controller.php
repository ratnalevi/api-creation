<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DelayedOrdersRepository;
use App\Repository\OrdersRepository;
use App\Repository\OrderItemsRepository;

class OrdersV1Controller extends AbstractController
{

    private $ordersRepository;
    private $delayedOrdersRepository;

    public function __construct(OrdersRepository $ordersRepository, DelayedOrdersRepository $delayedOrdersRepository)
    {
        $this->ordersRepository = $ordersRepository;
        $this->delayedOrdersRepository = $delayedOrdersRepository;
    }

    /**
     * @Route("/orders/v1", name="v1_get_orders", methods={"GET"})
     * This method lists the order based on the parameters passed to it.
     * With no parameters passed, it returns all the orders
     * With status parameter passed, we filter it based on status and return the results
     * With orderId parameter passed, we get the order corresponding to given ID and return the result
    */
    public function index(Request $request): JsonResponse
    {
        $orders = [];

        if (!empty($request->get('status'))) {
            $status = $request->get('status');
            if (!in_array($status, ['NEW', 'PROCESSING', 'DELAYED', 'DELIVERED'])) {
                return new JsonResponse([
                    'status' => 'Expecting valid Status',
                ], Response::HTTP_BAD_REQUEST);
            } else {
                $orders = $this->ordersRepository->findBy([
                    'status' => $status
                ]);
            }
        } else if (!empty($request->get('orderId'))) {
            $orderId = $request->get('orderId');
            if ($orderId < 0) {
                return new JsonResponse([
                    'status' => 'Expecting valid Order ID',
                ], Response::HTTP_BAD_REQUEST);
            } else {
                $order = $this->ordersRepository->find($orderId);
                if ($order) {
                    $orders[] = $order;
                }
            }
        } else {
            $orders = $this->ordersRepository->findAll();
        }

        $data = [];

        if (count($orders) == 0) {
            return new JsonResponse([
                'status' => 'No orders found',
                'data' => [],
            ], Response::HTTP_OK);
        }

        foreach ($orders as $order) {
            $data[] = [
                'id' => $order->getId(),
                'customerId' => $order->getCustomerId(),
                'deliveryAddress' => $order->getDeliveryAddress(),
                'billingAddress' => $order->getBillingAddress(),
                'status' => $order->getStatus(),
                'expectedDeliveryTime' => $order->getExpectedDeliveryTime()->format('Y:m:d H:i:s'),
                'createdAt' => $order->getCreatedAt()->format('Y:m:d H:i:s'),
                'updatedAt' => $order->getUpdatedAt()->format('Y:m:d H:i:s'),
            ];
        }

        $response['data'] = $data;

        return new JsonResponse([
            'status' => 'Found ' . count($orders) . ' orders',
            'data' => $data
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/orders/v1/delayed", name="v1_get_delayed_orders", methods={"GET"})
     * This method lists down the orders from delayed orders table
    */
    public function showDelayed(Request $request): JsonResponse
    {
        $data = [];
        
        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');

        if (empty($startTime) || empty($endTime)) {
            return new JsonResponse([
                'status' => 'Expecting valid Start time / End time',
            ], Response::HTTP_BAD_REQUEST);
        }

        $startTime = strtotime($startTime);
        $endTime = strtotime($endTime);
        
        if (empty($startTime) || empty($endTime)) {
            return new JsonResponse([
                'status' => 'Expecting valid Start time / End time',
            ], Response::HTTP_BAD_REQUEST);
        }

        $startTime = date('Y:m:d H:i:s', $startTime);
        $endTime = date('Y:m:d H:i:s', $endTime);
        
        $delayedOrders = $this->delayedOrdersRepository->findDelayedOrdersBetweenDates($startTime, $endTime);

        if (count($delayedOrders) == 0) {
            return new JsonResponse([
                'status' => "No delayed orders found between {$startTime} and {$endTime}",
                'data' => [],
            ], Response::HTTP_OK);
        }

        foreach ($delayedOrders as $delayedOrder) {
            $order = $delayedOrder->getOrder();
            $data[] = [
                'id' => $delayedOrder->getId(),
                'order' => [
                    'id' => $order->getId(),
                    'customerId' => $order->getCustomerId(),
                    'deliveryAddress' => $order->getDeliveryAddress(),
                    'billingAddress' => $order->getBillingAddress(),
                    'status' => $order->getStatus(),
                    'expectedDeliveryTime' => $order->getExpectedDeliveryTime()->format('Y:m:d H:i:s'),
                    'createdAt' => $order->getCreatedAt()->format('Y:m:d H:i:s'),
                    'updatedAt' => $order->getUpdatedAt()->format('Y:m:d H:i:s'),
                ],
                'createdAt' => $delayedOrder->getCreatedAt()->format('Y:m:d H:i:s'),
                'updatedAt' => $delayedOrder->getUpdatedAt()->format('Y:m:d H:i:s'),
            ];
        }
        
        return new JsonResponse([
            'status' => 'Found ' . count($delayedOrders) . ' delayed orders',
            'data' => $data
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/orders/v1/{id}", name="v1_update_status_by_order_id", methods={"PATCH"})
     * This methods updates the order data
     * Currently configured only to update status
     */
    public function update($id, Request $request): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return new JsonResponse([
                'status' => 'Expecting mandatory parameters',
            ], Response::HTTP_BAD_REQUEST);
        }

        $status = $data['status'];
        if (empty($id) || empty($status) || !in_array($status, ['NEW', 'PROCESSING', 'DELAYED', 'DELIVERED'])) {
            return new JsonResponse([
                'status' => 'Excepting valid Status or Order ID',
            ], Response::HTTP_BAD_REQUEST);
        }

        $order = $this->ordersRepository->find($id);

        if (!$order) {
            return new JsonResponse([
                'status' => 'No order found for id: ' . $id,
            ], Response::HTTP_NOT_FOUND);
        }

        if ($order->getStatus() == $status) {
            return new JsonResponse([
                'status' => 'No change in status for order id: ' . $id . '. Status is : ' . $status,
            ], Response::HTTP_NOT_FOUND);
        }

        $order->setStatus($status);
        $this->ordersRepository->updateOrder($order);

        return new JsonResponse([
            'status' => 'Order status updated',
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/orders/v1", name="v1_add_order", methods={"POST"})
     * This method is to add new order
     */
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return new JsonResponse([
                'status' => 'Expecting mandatory parameters',
            ], Response::HTTP_BAD_REQUEST);
        }

        $customerId = $data['customerId'];
        $deliveryAddress = $data['deliveryAddress'];
        $billingAddress = $data['billingAddress'];

        if (empty($customerId) || $customerId < 1 || empty($deliveryAddress) || empty($billingAddress)) {
            return new JsonResponse([
                'status' => 'Expecting mandatory parameters. Customer ID / Delivery Address / Billing Address',
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['items'])) {
            return new JsonResponse([
                'status' => 'Expecting mandatory parameters. Items with Product ID and Quantity',
            ], Response::HTTP_BAD_REQUEST);
        }

        $items = $data['items'];
        if (!OrderItemsRepository::validateOrderItems($items)) {
            return new JsonResponse([
                'status' => 'Expecting valid order items',
            ], Response::HTTP_BAD_REQUEST);
        }

        $deliveryDate = $this->ordersRepository->saveOrder($customerId, $deliveryAddress, $billingAddress, $items);

        return new JsonResponse([
            'status' => 'Order created',
            'expectedDeliveryTime' => $deliveryDate->format('Y:m:d H:i:s'),
        ], Response::HTTP_CREATED);
    }
}
