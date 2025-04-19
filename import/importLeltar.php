<?php

$path = __DIR__;


//tömbösítjük a /-ek mentén a névteret
$path = explode(DIRECTORY_SEPARATOR, $path);

//kivesszük a nem szükséges névtereket (pop)
$path = array_splice($path, 0, -4);

$path = array_merge($path, array("include", "configuration.php"));

$path = implode(DIRECTORY_SEPARATOR, $path);


require $path;

use Control\StockManagement\Inventory_Control\Inventory_Control as Inventory_ControlInventory_Control;
use Control\StockManagement\Item\Item as ItemItem;
use DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse as WarehouseWarehouse;
use DI\Model\Entity\StockManagement\Inventory_Control\Inventory_Control;
use DI\Model\Entity\StockManagement\Inventory_Control\Inventory_Control_Item;
use DI\Model\Entity\StockManagement\Item\Item;



$__app->Start();

$inputFileType = 'xlsx';
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load("order 23.10.2023 .xlsx");
$spreadsheet->setActiveSheetIndex(1);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
$dItems = array();
$itemsWithOutBarCode[] = array();
unset($rows[0]);
unset($rows[1]);
unset($rows[2]);

$inventoryControl = new Inventory_Control();
$inventoryControl->name = "START";
$inventoryControl->currencyId = 1;
$inventoryControl->currencyCode = "HUF";
$inventoryControl->inventoryDate = date("Y-m-d");
$inventoryControl->createDate = date("Y-m-d");
$inventoryControl->comment = "Kezdő készlet";
$inventoryControl->isClosed = 0;


$inventoryControlItems = array("insertedRows" => []);

foreach ($rows as $key => $row) {
    $item;
    try {
        $item = new Item();
        $item->code = $row[2];
        $item->name = empty($row[3])?$item->code:$row[3];

        $item->isInventoryItem = 1;
        $item->isSellItem = 1;
        $item->isBuyItem = 1;
        $item->buyUnit = "db";
        $item->sellUnit = "db";
        $item->inventoryUnit = "db";
        $item->itemGroupId = 2;
        $item->minimumLevel = 500;
        $item->defaultWarehouseId = WarehouseWarehouse::Get(array("isDefault" => 1))->id;

        try {
            $item = Item::Get(array("code" => $item->code));
        } catch (\Throwable $th) {
            $item = ItemItem::Add(array("item" => $item->ToArray()));
            //throw $th;
        }

        

        $inventoryControlItem = new Inventory_Control_Item();
        $inventoryControlItem->itemId = $item->id;
        $inventoryControlItem->code = $item->code;
        $inventoryControlItem->name = $item->name;
        $inventoryControlItem->warehouseId = $item->defaultWarehouseId;
        $inventoryControlItem->stockQuantity = 0;
        $inventoryControlItem->currentQuantity = $row[5];
        $inventoryControlItem->quantity = $row[5];
        $inventoryControlItem->quantityUnit = $item->inventoryUnit;
        $inventoryControlItem->netPrice = floatval($row[6] * 385);

        $inventoryControlItems["insertedRows"][] = $inventoryControlItem->ToArray();


    } catch (\Throwable $th) {
        echo $item->name;
    }

    

}

Inventory_ControlInventory_Control::Add(array("inventoryControl" => $inventoryControl->ToArray(), "inventoryControlItem" => $inventoryControlItems));

exit();
?>