<?php
ini_set('max_execution_time', 999999999);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$entityTypeCode = \Magento\Catalog\Model\Product::ENTITY;
$attributeCode = 'shipping_group';
$file_name = "duplicate_credit_memo.csv";

$duplicateOrders = [];
$orderCollection = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\CollectionFactory')->create();
$refundOrders = $orderCollection
    ->addAttributeToSelect('*')
    ->addFieldToFilter(
        'base_total_refunded',
        ['gt' => 1]
    );

$list = [
    ['orderId', 'credit memo Id']
];

foreach ($refundOrders as $_order) {
    $creditMemos = $_order->getCreditmemosCollection();
    if (count($creditMemos) >= 2) {
        array_push($duplicateOrders, $_order->getId());
        $cmIds = [];
        $cmData = [];
        foreach ($creditMemos as $cm) {
            array_push($cmIds, $cm->getId());
        }
        // echo 'Order id : ' . $_order->getIncrementId() . ', credit memo id: ' . implode(", ", $cmIds) . '<br/>';
        array_push($list, [$_order->getIncrementId(), implode(", ", $cmIds)]);
    }
}
$file = fopen($file_name, "w");

foreach ($list as $line) {
    fputcsv($file, $line);
}

fclose($file);
exit;
