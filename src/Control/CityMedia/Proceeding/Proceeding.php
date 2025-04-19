<?php
namespace Control\CityMedia\Proceeding;

use Control\Administration\Definitions\DocumentNumber\Document_Number as Document_Number;
use Control\Application;
use Control\DocumentSource\TDocument_Source;
use Control\StockManagement\Price\PriceContext;
use Control\StockManagement\StoreTransactions\Inventory_Exit\Inventory_Exit;
use Control\System\File\FileManager;
use DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group;
use DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse;
use DI\Model\Entity\BusinessPartners\Partner\Partner;
use DI\Model\Entity\CityMedia\Partner\Partner_Contract;
use DI\Model\Entity\CityMedia\Partner\Partner_Contract_Item;
use DI\Model\Entity\CityMedia\Proceeding\Proceeding as ProceedingProceeding;
use DI\Model\Entity\CityMedia\Proceeding\Proceeding_Item as DIProceeding_Item;
use DI\Model\Entity\StockManagement\Item\Item;
use DI\Model\Entity\StockManagement\Item\Item_Seo;
use DI\Model\Entity\StockManagement\Price\Price_List;
use DI\Model\Entity\StockManagement\StoreTransactions\Inventory_Exit\Inventory_Exit as Inventory_ExitInventory_Exit;
use DI\Model\Entity\StockManagement\StoreTransactions\Inventory_Exit\Inventory_Exit_Item;
use DI\Model\Entity\System\File\File;
use DI\System\File\FileManager as FileFileManager;
use Exception;
use UI\View\Html\CityMedia\Proceeding\Proceeding as CityMediaProceedingProceeding;

/**
 * Karbantartás vezérlő osztály.
 */
class Proceeding extends \Control\EntityController{

	/**
	 *
	 * Karbantartás felvétel.
	 *
	 * @param array $data Karbantartás adatok.
	 * @return \DI\Model\Entity\CityMedia\Proceeding\Proceeding Karbantartás objektum.
	 * @throws \Exception
	 */
	public static function Add(array $data): \DI\Model\Entity\CityMedia\Proceeding\Proceeding{
		$dao = Application::GetInstance()->GetSql();

		try{
			$dao->StartTransaction();

			$data["proceeding"]["documentNumber"] = Document_Number::GenerateSave(self::CreateObject());


			switch($data["proceeding"]["type"]){
				//Kihelyezési
				case "1":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}


					if($data["proceeding"]["partnerId"] < 1){
						// throw new \Exception("Hiba! Nincs megadva partner azonosító!");
					}


					if(strlen($data["proceeding"]["issueDate"]) < 1){
						throw new \Exception("Hiba! Nincs megadva kihelyezés dátuma!");
					}

					$data["proceeding"]["incoming"] = null;
					$data["proceeding"]["outgoing"] = null;
					$data["proceeding"]["commission"] = null;

					break;

				//Ürítési
				case "2":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}

					if($data["proceeding"]["partnerId"] < 1){
						// throw new \Exception("Hiba! Nincs megadva partner azonosító!");
					}

					break;
				//Panasz
				case "3":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}

					if($data["proceeding"]["partnerId"] < 1){
						throw new \Exception("Hiba! Nincs megadva partner azonosító!");
					}

					$data["proceeding"]["incoming"] = null;
					$data["proceeding"]["outgoing"] = null;
					$data["proceeding"]["commission"] = null;



					break;

				//Karbantartási
				case "4":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}


					$data["proceeding"]["incoming"] = null;
					$data["proceeding"]["outgoing"] = null;
					$data["proceeding"]["commission"] = null;


					break;
			}




			$proceeding = parent::Add($data["proceeding"]);

            //létrehozás dátuma
            if(strlen($data["proceeding"]["createDate"]) > 0) {
                $proceeding->createDate = $data["proceeding"]["createDate"];
                $proceeding = parent::Update($proceeding->ToArray());
            }

			$items = [];
			if(isset($data["proceedingItem"])){
				$items = self::HandleProceedingItem($data["proceedingItem"], $proceeding);
			}

			$proceeding->incoming = 0;
			$proceeding->outgoing = 0;
			$proceeding->netAmount = 0;
			$proceeding->grossAmount = 0;

			$items = \DI\Model\Entity\CityMedia\Proceeding\Proceeding_Item::GetObjectList(array("proceedingId" =>$proceeding->id));

			foreach($items as $one){
				$proceeding->incoming += $one->incoming;
				$proceeding->outgoing += $one->outgoing;
				$proceeding->netAmount += $one->netAmount;
				$proceeding->grossAmount += $one->grossAmount;
			}

			$proceeding->Update();


			$dao->Commit();


			return $proceeding;

		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}


	/**
	 *
	 * Karbantartás módosítása.
	 *
	 * @param array $data Karbantartás adatok.
	 * @return \DI\Model\Entity\CityMedia\Proceeding\Proceeding Karbantartás objektum.
	 * @throws \Exception
	 */
	public static function Update(array $data): \DI\Model\Entity\CityMedia\Proceeding\Proceeding{
		$dao = Application::GetInstance()->GetSql();

		try{
			$dao->StartTransaction();

			unset($data["proceeding"]["documentNumber"]);


			switch($data["proceeding"]["type"]){
				//Kihelyezési
				case "1":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}


					if($data["proceeding"]["partnerId"] < 1){
						throw new \Exception("Hiba! Nincs megadva partner azonosító!");
					}


					if(strlen($data["proceeding"]["issueDate"]) < 1){
						throw new \Exception("Hiba! Nincs megadva kihelyezés dátuma!");
					}

					$data["proceeding"]["incoming"] = null;
					$data["proceeding"]["outgoing"] = null;
					$data["proceeding"]["commission"] = null;

					break;

				//Ürítési
				case "2":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}

					if($data["proceeding"]["partnerId"] < 1){
						throw new \Exception("Hiba! Nincs megadva partner azonosító!");
					}


					break;
				//Panasz
				case "3":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}

					if($data["proceeding"]["partnerId"] < 1){
						throw new \Exception("Hiba! Nincs megadva partner azonosító!");
					}

					$data["proceeding"]["incoming"] = null;
					$data["proceeding"]["outgoing"] = null;
					$data["proceeding"]["commission"] = null;



					break;

				//Karbantartási
				case "4":
//					if($data["proceeding"]["warehouseId"] < 1){
//						throw new \Exception("Hiba! Nincs megadva raktár azonosító!");
//					}


					$data["proceeding"]["incoming"] = null;
					$data["proceeding"]["outgoing"] = null;
					$data["proceeding"]["commission"] = null;


					break;
			}



			
			$proceeding = parent::Update($data["proceeding"]);

			$items = [];
			if(isset($data["proceedingItem"])){
				$items = self::HandleProceedingItem($data["proceedingItem"], $proceeding);
			}

			$proceeding->incoming = 0;
			$proceeding->outgoing = 0;
			$proceeding->netAmount = 0;
			$proceeding->grossAmount = 0;

			$items = \DI\Model\Entity\CityMedia\Proceeding\Proceeding_Item::GetObjectList(array("proceedingId" =>$proceeding->id));

			foreach($items as $one){
				$proceeding->incoming += $one->incoming;
				$proceeding->outgoing += $one->outgoing;
				$proceeding->netAmount += $one->netAmount;
				$proceeding->grossAmount += $one->grossAmount;
			}

			$proceeding->Update();

			$dao->Commit();


			return $proceeding;

		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}


	/**
	 *
	 * Jegyzőkönyv tétel adatok kezelése.
	 *
	 * @param array $data Jegyzőkönyv tétel adatok.
	 * @param \DI\Model\Entity\CityMedia\Proceeding\Proceeding $proceeding Jegyzőkönyv objektum.
	 * @return array
	 * @throws \Exception
	 */
	private static function HandleProceedingItem(array $data, \DI\Model\Entity\CityMedia\Proceeding\Proceeding $proceeding): array{
		$localProceedingItem = array();
		$dao = Application::GetInstance()->GetSql();

		if(isset($data["insertedRows"])){
			$data["insertedRows"] = array_filter($data["insertedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]);
			});


			foreach ($data["insertedRows"] as $one){
				$one["proceedingId"] = $proceeding->id;
				if(empty($one["outgoing"])){

					$one["outgoing"] = $one["netPrice"] * $one["quantity"] * ((100 + $one["vatRate"]) / 100);
				}
				$one["grossAmount"] = ($one["incoming"] - $one["outgoing"]) * ($one["commission"] / 100);
				$one["netAmount"] = $one["grossAmount"] / ((100 + $one["vatRate"]) / 100);


				$sql = "SELECT T1.*, T2.commission, T2.type FROM ct_partner_contract_item AS T1 
				INNER JOIN ct_partner_contract AS T2 ON T2.id = T1.documentId 
				WHERE T2.partnerId = :partnerId AND T1.warehouseId = :warehouseId";

				$partnerContractItem = $dao->GetObjects($sql, array(":partnerId" => $proceeding->partnerId, ":warehouseId" => $one["warehouseId"]))[0];


				//Fix bérleti díjas szerződés
				if($partnerContractItem->type == 3){
					$one["netAmount"] = $one["netPrice"];
					$one["grossAmount"] = $one["netAmount"] * ((100 + $one["vatRate"]) / 100);
					$one["incoming"] = 0;
					$one["outgoing"] = 0;
					$one["commission"] = 0;

				}





				$localProceedingItem[] = Proceeding_Item::Add($one);
			}
		}

		if(isset($data["modifiedRows"])){
			$data["modifiedRows"] = array_filter($data["modifiedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]);
			});


			foreach ($data["modifiedRows"] as $one){
				$one["proceedingId"] = $proceeding->id;

				
				$one["outgoing"] = $one["netPrice"] * $one["quantity"] * ((100 + $one["vatRate"]) / 100);
				$one["grossAmount"] = ($one["incoming"] - $one["outgoing"]) * ($one["commission"] / 100);
				$one["netAmount"] = $one["grossAmount"] / ((100 + $one["vatRate"]) / 100);

				
				$sql = "SELECT T1.*, T2.commission, T2.type FROM ct_partner_contract_item AS T1 
				INNER JOIN ct_partner_contract AS T2 ON T2.id = T1.documentId 
				WHERE T2.partnerId = :partnerId AND T1.warehouseId = :warehouseId";

				$partnerContractItem = $dao->GetObjects($sql, array(":partnerId" => $proceeding->partnerId, ":warehouseId" => $one["warehouseId"]))[0];


				//Fix bérleti díjas szerződés
				if($partnerContractItem->type == 3){
					$one["netAmount"] = $one["netPrice"];
					$one["grossAmount"] = $one["netAmount"] * ((100 + $one["vatRate"]) / 100);
					$one["incoming"] = 0;
					$one["outgoing"] = 0;
					$one["commission"] = 0;

				}


				
				$localProceedingItem[] = Proceeding_Item::Update($one);
			}
		}

		if(isset($data["deletedRows"])){
			$data["deletedRows"] = array_filter($data["deletedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]);
			});

			foreach ($data["deletedRows"] as $one){
				Proceeding_Item::Delete($one);
			}
		}

		return $localProceedingItem;
	}


	/**
	 * Létrehoz ürítési jegyzőkönyvet a feltöltés alapján
	 * @param int $warehouseId Raktár azonosító
	 * @param int $coinCount Érmék darabszáma
	 */
	public static function CreateByHopperFillUp($warehouseId, $coinCount){
		
		$dao = Application::GetInstance()->GetSql();

		$ctPartnerContractItem = Partner_Contract_Item::Get(array("warehouseId" => $warehouseId));

		$ctPartnerContract = Partner_Contract::Get(array("id" => $ctPartnerContractItem->documentId));

		$warehouse = Warehouse::Get(array("id" => $ctPartnerContractItem->warehouseId));

		$item = \DI\Model\Entity\StockManagement\Item\Item::Get(array("id" => $ctPartnerContractItem->itemId));
		$partner = Partner::Get(array("id" => $ctPartnerContract->partnerId));

		$inventoryExit = new Inventory_ExitInventory_Exit();
		$inventoryExit->taxDate = date("Y-m-d");
		$inventoryExit->orderDate = date("Y-m-d");
		
		// $inventoryExit->objectType = null;
		// $inventoryExit->objectId = null;
		
		$inventoryExitItem = new Inventory_Exit_Item();
		$inventoryExitItem->itemId = $item->id;
		$inventoryExitItem->itemCode = $item->code;
		$inventoryExitItem->itemName = $item->name;
		$inventoryExitItem->quantity = $coinCount;
		$inventoryExitItem->quantityUnit = $item->inventoryUnit;
		$inventoryExitItem->warehouseId = Warehouse::Get(array("code" => "DEF"))->id;
		// $inventoryExitItem->objectType = null;
		// $inventoryExitItem->objectId = null;

		//@TODO most csak anyagkiadás (később lehet jegyzőkönyv bizonylatból akár)
		$inventory_exit = Inventory_Exit::Add(array("inventoryExit" => $inventoryExit->ToArray(), "inventoryExitItem" => [$inventoryExitItem->ToArray()]));

		return $inventory_exit;


		//@TODO Jegyzőkönyv létrehozás egyenlőre nem kell most  még csak a raktárkészletről leemeljük
		//később ha kell a jegyzőkönyv is lehet érdemes belevinni a cikket és mint bizonylat kezelni

		/*

		$ctx = new PriceContext($item);

		$ctx->SetPriceList(Price_List::Get(array("code" => "DEF")));

		$ctx->SetCustomer($partner);

		$vatGroupDefault = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("isDefault" => 1));
		$vatGroup = $vatGroupDefault;
		try{
			$ctc = \DI\Model\Entity\Administration\Definitions\Finance\Customs\Customs_Tariff::Get(array("code" => $item->customsTariffCode));
			$vatGroup = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("id" => $ctc->vatGroupId));
		}catch(\Throwable $th){
			try {
				$vg = \DI\Model\Entity\Administration\Definitions\Finance\Tax\Vat\Vat_Group\Vat_Group::Get(array("id" => $item->vatGroupId));
				$vatGroup = $vg;
			} catch (\Throwable $th) {
				//throw $th;
			}
		}

		$price = $ctx->GetPrice();

		$grossPrice = $price->price * ((100 + $vatGroup->rate) / 100);

		$salesAfterLastFillUp = $dao->GetRows("SELECT COUNT(id) AS qty FROM ct_telemetry WHERE piLogCreateDate > IFNULL((SELECT piLogCreateDate FROM ct_telemetry WHERE type = 'fillUp' AND warehouseId = :warehouseId ORDER BY piLogId DESC LIMIT 1), '2020-01-01') AND type = 'sales' AND warehouseId = :warehouseId;", array(":warehouseId" => $warehouse->id))[0]["qty"];


		$proceeding = null;
		//Jutalékos
		if($ctPartnerContract->type == "2"){

			$proceeding = new \DI\Model\Entity\CityMedia\Proceeding\Proceeding();
			$proceeding->type = 2;
			$proceeding->partnerId = $ctPartnerContract->partnerId;
			// $proceeding->warehouseId = $warehouseId;
			$proceeding->status = "O";
			$proceeding->issueDate = date("Y-m-d");
			$proceeding->createDate = date("Y-m-d H:i:s");
			$proceeding->comment = "Gép feltöltés";
			$proceeding->incoming = $grossPrice * $salesAfterLastFillUp;
			//@TODO honnan jön a beszerzési ár
			$proceeding->outgoing = 0;
			$proceeding->commission = $ctPartnerContract->commission;

			$proceedingItem = new DIProceeding_Item();
			$proceedingItem->warehouseId = $warehouse->id;
			$proceedingItem->warehouseCode = $warehouse->code;
			$proceedingItem->warehouseName = $warehouse->name;

			$proceedingItems = array();
			$proceedingItems[] = $proceedingItem->ToArray();

			$proceeding = self::Add(array("proceeding" => $proceeding->ToArray(), "proceedingItem" => array("insertedRows" => $proceedingItems)));
		}

		return $proceeding;
		*/


	}


	/**
	 * Gyors jegyzőkönyv létrehozás a karbantartás lapról
	 */
	public static function AddFromMaintenance($proceeding, $proceedingItem, $proceedingFiles){
		$dao = Application::GetInstance()->GetSql();
		$data = array(
			"proceeding" => $proceeding,
			"proceedingItem" => $proceedingItem

		);
		$data["proceeding"]["issueDate"] = (strlen($proceeding["createDate"]) > 0) ? $proceeding["createDate"] : date("Y-m-d");
		$data["proceeding"]["status"] = "O";

		foreach($data["proceedingItem"]["insertedRows"] as &$one){

			$one["incoming"] = floatval($one["incoming"]);


			

			$warehouse = Warehouse::Get(array("id" => $one["warehouseId"]));

			$one["warehouseCode"] = $warehouse->code;
			$one["warehouseName"] = $warehouse->name;


			//csak ha ürítési a jegyzőkönyv akkor nézzük a szerződést hozzá
			if($data["proceeding"]["type"] != 2){
				continue;
			}

			$vatGroup = null;
			try {

				$agreementItem = \DI\Model\Entity\CityMedia\Partner\Partner_Contract_Item::Get(["warehouseId" => $warehouse->id]);
				$agreement = Partner_Contract::Get(["id" => $agreementItem->documentId]);
				

			} catch (\Throwable $th) {
				throw new \Exception("Nincs szerződés!");
			}

			$data["proceeding"]["partnerId"] = $agreement->partnerId;
			// $data["proceeding"]["partnerName"] = $agreement->partnerName;
			// $data["proceeding"]["partnerCode"] = $agreement->partnerCode;

			

			$vatGroup = $dao->GetObjects("SELECT * FROM nubes_vat_group WHERE id = (SELECT itemVatGroup(?))", [$agreementItem->itemId], Vat_Group::class)[0];
			if(!$vatGroup){
				throw new \Exception("Nincs adócsoport");
			}
			
			$one["vatGroupId"] = $vatGroup->id;
			$one["vatRate"] = $vatGroup->rate;


			//Ha különbözet szerinti az elszámolás
			$one["outgoing"] = floatval($one["outgoing"]);
			$income = $one["incoming"];
			if($agreementItem->settlementByDifference){
				$income -= $one["outgoing"];
			}
			

			if($agreement->type == 2){
					
				$one["commission"] = $agreement->commission;
				$one["grossAmount"] = $income * ($one["commission"] / 100);
				$one["netAmount"] = $one["grossAmount"] / ((100 + $one["vatRate"]) / 100);
			}

		}
		
		$sign = $data["proceeding"]["sign"];
		unset($data["proceeding"]["sign"]);

        $sign2 = null;
        if(isset($data["proceeding"]["sign2"])){
            $sign2 = $data["proceeding"]["sign2"];
            unset($data["proceeding"]["sign2"]);
        }
		
		if(!empty($proceedingFiles["tmp_name"])){

			$files = FileManager::UploadTemp($proceedingFiles);
			
			$data["proceeding"]["files"] = [];
			foreach($files as $o){

				$data["proceeding"]["files"]["insertedRows"][] = array(
					"name" => $o->name,
					"mimeType" => $o->mimeType,
					"extension" => $o->extension, 
					"size" => $o->size
				);
			}
		}

		$ret = static::Add($data);
		$className = get_class($ret);
	    $namespace = preg_replace("/^DI\\\\Model\\\\Entity/", "", $className);

		if(!empty($sign)){
			FileManager::UploadBase64EncodedFile($sign, "sign.png", "image/png", $namespace, $ret->id);
		}

        if(!empty($sign2)){
            FileManager::UploadBase64EncodedFile($sign2, "sign2.png", "image/png", $namespace, $ret->id);
        }


		return $ret;

	}

    /**
     *
     * Eltárolja az átváltott bizonylat cél objektumot.
     *
     * @param int $objectId Forrás objektum azonosító.
     * @param string $destObjectNS Cél objektum névtér.
     * @return \DI\Model\Entity\Tree|mixed
     * @throws \Exception
     */
    public static function StoreTDocumentSource(int $objectId, string $destObjectNS){

        $__DAO = Application::GetInstance()->GetSql();

        try {

            $__DAO->StartTransaction();

            $preparedObjects = self::PrepareObject($objectId, $destObjectNS, null, "proceedingId");

            $tDocumentSource = TDocument_Source::Add($preparedObjects);


            $__DAO->Commit();

            return $tDocumentSource;

        }catch (\Exception $ex){
            $__DAO->Rollback();
            throw $ex;
        }
    }


}