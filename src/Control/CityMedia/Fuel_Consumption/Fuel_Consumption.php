<?php

namespace Control\CityMedia\Fuel_Consumption;

use Control\Administration\Definitions\DocumentNumber\Document_Number as Document_Number;
use Control\Application;

class Fuel_Consumption extends \Control\EntityController{

	/**
	 *
	 * Üzemanyag felhasználás felvétele.
	 *
	 * @param array $data Üzemanyag felhasználás adatok.
	 * @return \DI\Model\Entity\CityMedia\Fuel_Consumption\Fuel_Consumption
	 * @throws \Exception
	 */
	public static function Add(array $data): \DI\Model\Entity\CityMedia\Fuel_Consumption\Fuel_Consumption{
		$dao = Application::GetInstance()->GetSql();

		try{

			$dao->StartTransaction();



			$data["fuelConsumption"]["documentNumber"] = Document_Number::GenerateSave(self::CreateObject());


			//számoljuk a tételek alapján a felhasznált mennyiséget
			$usedFuelQuantity = 0;

			if(count($data["fuelConsumptionItem"]["insertedRows"]) > 0){
				foreach($data["fuelConsumptionItem"]["insertedRows"] as $one){
					$usedFuelQuantity += $one["usedFuelQuantity"];
				}
			}


			$quantities = self::HandleQuantity();
			$allFuelQuantity = $quantities["allFuelQuantity"];

			$data["fuelConsumption"]["usedFuelQuantitySum"] = $usedFuelQuantity;
			$data["fuelConsumption"]["remainedFuelQuantitySum"] = $allFuelQuantity - $usedFuelQuantity;



			$fuelConsumption = parent::Add($data["fuelConsumption"]);


			if(isset($data["fuelConsumptionItem"])){
				$fuelConsumptionItem = self::HandleFuelConsumptionItem($data["fuelConsumptionItem"], $fuelConsumption);



				//üzemanyag áttárolás
				$inventoryTransfer = new \DI\Model\Entity\StockManagement\StoreTransactions\Inventory_Transfer\Inventory_Transfer();
				$inventoryTransfer->taxDate = date('Y-m-d H:i:s');
				$inventoryTransfer->orderDate = date('Y-m-d H:i:s');


				$inventoryTransferItem = array();

				$fuel = \DI\Model\Entity\StockManagement\Item\Item::Get(array("code" => "UZEMANYAG"));
				$defWarehouse = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::Get(array("code" => "DEF"));

				foreach($fuelConsumptionItem as $one){
					$localInventoryTransferItem = new \DI\Model\Entity\StockManagement\StoreTransactions\Inventory_Transfer\Inventory_Transfer_Item();
					$localInventoryTransferItem->itemId = $fuel->id;
					$localInventoryTransferItem->itemName = $fuel->name;
					$localInventoryTransferItem->itemCode = $fuel->code;
					$localInventoryTransferItem->quantity = $one->usedFuelQuantity;
					$localInventoryTransferItem->quantityUnit = $fuel->inventoryUnit;
					$localInventoryTransferItem->sourceWarehouseId = $defWarehouse->id;
					$localInventoryTransferItem->destWarehouseId = $one->warehouseId;


					$inventoryTransferItem[] = $localInventoryTransferItem->ToArray();
				}


				\Control\StockManagement\StoreTransactions\Inventory_Transfer\Inventory_Transfer::Add(array("inventoryTransfer" => $inventoryTransfer->ToArray(), "inventoryTransferItem" => $inventoryTransferItem));




			}





			$dao->Commit();

			return $fuelConsumption;

		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}

	/**
	 *
	 * Üzemanyag felhasználás módosítása.
	 *
	 * @param array $data Üzemanyag felhasználás adatok.
	 * @return \DI\Model\Entity\CityMedia\Fuel_Consumption\Fuel_Consumption
	 * @throws \Exception
	 */
	public static function Update(array $data): \DI\Model\Entity\CityMedia\Fuel_Consumption\Fuel_Consumption{
		$dao = Application::GetInstance()->GetSql();

		try{

			throw new \Exception("Figyelem! Ez a funkció nem elérhető átmenetileg!");

			$dao->StartTransaction();

			$fuelConsumption = parent::Update($data["fuelConsumption"]);


			if(isset($data["fuelConsumptionItem"])){
				$fuelConsumptionItem = self::HandleFuelConsumptionItem($data["fuelConsumptionItem"], $fuelConsumption);
			}

			$dao->Commit();

			return $fuelConsumption;

		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}


	/**
	 *
	 * Üzemanyag beszerzés tételek kezelése.
	 *
	 * @param array $data Üzemanyag felhasználás tétel adatok.
	 * @param \DI\Model\Entity\CityMedia\Fuel_Consumption\Fuel_Consumption $fuelConsumption Üzemanyag felhasználás objektum.
	 * @return array
	 * @throws \Exception
	 */
	private static function HandleFuelConsumptionItem(array $data, \DI\Model\Entity\CityMedia\Fuel_Consumption\Fuel_Consumption $fuelConsumption): array{
		$localFuelConsumptionItem = array();

		if(isset($data["insertedRows"])){
			$data["insertedRows"] = array_filter($data["insertedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]) && $a["usedFuelQuantity"] > 0;
			});


			foreach ($data["insertedRows"] as $one){
				$one["documentId"] = $fuelConsumption->id;

				$localFuelConsumptionItem[] = Fuel_Consumption_Item::Add($one);
			}
		}

		if(isset($data["modifiedRows"])){
			$data["modifiedRows"] = array_filter($data["modifiedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]) && $a["usedFuelQuantity"] > 0;
			});


			foreach ($data["modifiedRows"] as $one){
				$one["documentId"] = $fuelConsumption->id;

				Fuel_Consumption_Item::Update($one);
				$localFuelConsumptionItem[] = $one["id"];
			}
		}

		if(isset($data["deletedRows"])){
			$data["deletedRows"] = array_filter($data["deletedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]) && $a["usedFuelQuantity"] > 0;
			});

			foreach ($data["deletedRows"] as $one){
				Fuel_Consumption_Item::Delete($one);
			}
		}

		return $localFuelConsumptionItem;
	}


	/**
	 *
	 * Visszaadja melyik raktáraknál mennyi kiszállás történt.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function HandleQuantity(): array{
		$__DAO = Application::GetInstance()->GetSql();

		//lekérjük az adott hónap jegyzőkönyveit a kiszállásokról
		$monthFirstDay = date("Y-m-01 00:00:00");
		$monthLastDay = date("Y-m-t 23:59:59");

		$q = "select warehouseId, count(id) as counter from ct_proceeding where createDate >= ? and createDate <= ? and type in (2, 4) group by warehouseId";
		$proceedingQuantity = $__DAO->GetRows($q, array($monthFirstDay, $monthLastDay));


		$result = array();




		if(count($proceedingQuantity) > 0){

			//üzemanyag mennyiségét a default raktárból kikérjük
			$warehouse = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::Get(array("code" => "DEF"));

			$fuel = \DI\Model\Entity\StockManagement\Item\Item::Get(array("code" => "UZEMANYAG"));
			$fuelTree = $fuel->GetStockManagementTree(array("warehouse" => intval($warehouse->id)));
			$quantity = 0;

			$fuel->GetItemStockQuantity($fuelTree, $quantity);


			//az adott raktárankénti kiszállásból kiszámoljuk a hónapban történt összes kiszállást
			$allDeparture = 0;
			foreach($proceedingQuantity as $one){
				$allDeparture += $one["counter"];
			}


			//a kiszállások alapján adunk egy becslést az üzemanyag felhasználásra az egyes raktáraknál
			foreach($proceedingQuantity as $key => $one){
				$proceedingQuantity[$key]["fuelQuantity"] = ($quantity / $allDeparture) * $one["counter"];
			}


			$result["proceedingList"] = $proceedingQuantity;
			$result["allFuelQuantity"] = $quantity;


		}






		return $result;
	}


	/**
	 *
	 * Árlista tétel lista táblázatot hozza létre.
	 *
	 * @param $dataSource Adatszerkezet.
	 * @param null $pagination Oldalszámozás.
	 * @param null $search Keresés.
	 * @param null $sort Rendezés.
	 * @return array
	 */
	public static function LoadList($dataSource, $pagination = null, $search = null, $sort = null){
		$dao = Application::GetInstance()->GetSql();

		$sql = "select * from (
    
    	select T1.id as id, T1.warehouseId as warehouseId, T1.warehouseCode as warehouseCode, T1.warehouseName as warehouseName, T1.usedFuelQuantity as usedFuelQuantity from ct_fuel_consumption_item as T1 
   		where T1.documentId = ?
    	
    	
    	union all
   
    
    	select null as id, T1.id as warehouseId, T1.code as warehouseCode, T1.name as warehouseName, '0' as usedFuelQuantity from nubes_warehouse as T1 
    	where T1.code != ? and NOT EXISTS (SELECT id FROM ct_fuel_consumption_item AS T2 WHERE T2.warehouseId = T1.id AND T2.documentId = ? and T1.code != ?)
    
    
    
    	) as T0 WHERE 1=1 ";

		$parameters = array($dataSource["fuelConsumptionId"], "DEF", $dataSource["fuelConsumptionId"], "DEF");

		$columns = $dao->GetRows($sql . " LIMIT 1", $parameters);
		$columns = array_keys($columns[0]);

		if(is_array($search)){
			foreach($search as $indx => $one){
				if($one && strlen($one)){

					if(array_key_exists($indx, $columns)){
						$sql .= " AND {$columns[$indx]} LIKE ?";
					}

					$parameters[] = "%".$one."%";
				}
			}
		}


		if(is_array($sort)) {
			if(count($sort)){

				$sql .= " ORDER BY ";
			}
			foreach ($sort as $indx => $one) {
				if ($one["type"] && strlen($one["type"])) {

					$indx = $one["column"];

					if(in_array($indx, $columns)){
						$sql .= " {$indx} {$one["type"]}";
					}

				}
			}
		}

		if(!is_array($params)){
			$params = array();
		}


		$pageCount = $dao->GetRows($sql, array_merge($params, $parameters));

		$pageCount = count($pageCount);
		$pagination["allRowNum"] = $pageCount;
		$pageCount += ($pagination["recordsPerPage"] - 1);

		$pageCount = floor($pageCount / $pagination["recordsPerPage"]);

		$lim = ($pagination["page"] - 1) * $pagination["recordsPerPage"];
		//Eredmény limitálása
		$sql .= " LIMIT {$lim}, {$pagination["recordsPerPage"]}";


		$dt_creator = new \DI\Model\Data\DataTableCreator($dao);

		$dataTable = $dt_creator->FromQueryResult($dao->GetRows($sql, array_merge($params, $parameters)));

		$grid = new \UI\View\Html\Grid();
		$grid->id="QueryDataTableTest";
		$grid->SetModel($dataTable);

		$columns = $dataSource["Columns"];


		//oszlopok beállítása
		foreach($columns as $indx => $one){

			if(isset($one) && !empty($one)){
				$one = array_filter($one);
			}

			//a select-eknek külön kell az option-jeik Deserialize metódusát hívni
			if($one["objectType"] == "\\UI\\Html\\Select") {
				//először létrehozzuk a selectet
				$select = $one["objectType"]::Deserialize($one);

				//optgroup-ok
				if(isset($one["optGroupArray"]) && $one["optGroupArray"] != null){
					$optGroups = array();
					$options = array();
					foreach ($one["optGroupArray"] as $k => $optgroup){
						$optGroups[$k] = $optgroup["objectType"]::Deserialize($optgroup);

						foreach ($optgroup["options"] as $opt){
							$options[] = $opt["objectType"]::Deserialize($opt);
						}
						$optGroups[$k]->options = $options;

						//kiürítjük az options tömböt
						$options = array();
					}

					$select->optGroupArray = $optGroups;

					//sima option-ök
				}else{
					$options = array();
					foreach ($one["optionsArray"] as $option){
						$options[] = $option["objectType"]::Deserialize($option);
					}

					$select->optionsArray = $options;
				}

				$grid->GetColumns()[$indx] = $select;
			}else{
				if($one["objectType"] != null) {
					$grid->GetColumns()[$indx] = $one["objectType"]::Deserialize($one);
				}
			}
		}

		// $number = new \UI\Html\InputNumber();
		// $number->name = "price";
		// $number->precision = 8;
		// $number->max = 9999999999999;
		// $number->step = 1;

		// $grid->SetColumn("price", $number);

		$view = $grid->RenderRows();



		$ret = array(
			"view" => $view,
			"pagination" => array(
				"page" => $pagination["page"],
				"recordsPerPage" => $pagination["recordsPerPage"],
				"pageCount" => $pageCount,
				"allRowNum" => $pagination["allRowNum"]
			),
			"loadFrom" => "special"
		);

		return $ret;

	}

}