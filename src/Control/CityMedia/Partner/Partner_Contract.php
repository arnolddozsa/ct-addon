<?php
namespace Control\CityMedia\Partner;

use Control\Administration\Definitions\DocumentNumber\Document_Number as Document_Number;
use Control\Application;
use Control\StockManagement\Price\PriceContext;
use DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse;
use DI\Model\Entity\BusinessPartners\Partner\Partner;
use DI\Model\Entity\StockManagement\Item\Item;
use Exception;

/**
 * Szerződés vezérlő osztály.
 */
class Partner_Contract extends \Control\EntityController{


	/**
	 *
	 * Szerződés felvétele.
	 *
	 * @param array $data Szerződés adatok.
	 * @return \DI\Model\Entity\CityMedia\Partner\Partner_Contract
	 * @throws \Exception
	 */
	public static function Add(array $data): \DI\Model\Entity\CityMedia\Partner\Partner_Contract{
		$dao = Application::GetInstance()->GetSql();

		try{

			if(strlen($data["partnerContract"]["type"]) < 1){
				throw new \Exception("Figyelem! Nincs kiválaszva a szerződés típusa");
			}

			//nem jutalékos szerződés
			if($data["partnerContract"]["type"] != 2){
				$data["partnerContract"]["commission"] = null;
			}

			$dao->StartTransaction();


			$data["partnerContract"]["documentNumber"] = Document_Number::GenerateSave(self::CreateObject());


			$partnerContract = parent::Add($data["partnerContract"]);


			if(isset($data["partnerContractItem"])){
				$partnerContractItem = self::HandlePartnerContractItem($data["partnerContractItem"], $partnerContract);
			}



			$dao->Commit();


			return $partnerContract;
		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}


	/**
	 *
	 * Szerződés módosítása.
	 *
	 * @param array $data Szerződés adatok.
	 * @return \DI\Model\Entity\CityMedia\Partner\Partner_Contract
	 * @throws \Exception
	 */
	public static function Update(array $data): \DI\Model\Entity\CityMedia\Partner\Partner_Contract{
		$dao = Application::GetInstance()->GetSql();

		try{

			if(strlen($data["partnerContract"]["type"]) < 1){
				throw new \Exception("Figyelem! Nincs kiválaszva a szerződés típusa");
			}


			//nem jutalékos szerződés
			if($data["partnerContract"]["type"] != 2){
				$data["partnerContract"]["commission"] = null;
			}

			$dao->StartTransaction();


			unset($data["partnerContract"]["documentNumber"]);


			$partnerContract = parent::Update($data["partnerContract"]);


			if(isset($data["partnerContractItem"])){
				$partnerContractItem = self::HandlePartnerContractItem($data["partnerContractItem"], $partnerContract);
			}


			$dao->Commit();


			return $partnerContract;
		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}


	/**
	 *
	 * Szerződés tétel adatok kezelése.
	 *
	 * @param array $data Szerződés tétel adatok.
	 * @param \DI\Model\Entity\CityMedia\Partner\Partner_Contract $partnerContract Szerződés objektum.
	 * @return array
	 * @throws \Exception
	 */
	private static function HandlePartnerContractItem(array $data, \DI\Model\Entity\CityMedia\Partner\Partner_Contract $partnerContract): array{
		$localPartnerContractItem = array();

		if(isset($data["insertedRows"])){
			$data["insertedRows"] = array_filter($data["insertedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]);
			});


			foreach ($data["insertedRows"] as $one){
				$one["documentId"] = $partnerContract->id;

				$localPartnerContractItem[] = Partner_Contract_Item::Add($one);
			}
		}

		if(isset($data["modifiedRows"])){
			$data["modifiedRows"] = array_filter($data["modifiedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]);
			});


			foreach ($data["modifiedRows"] as $one){
				$one["documentId"] = $partnerContract->id;

				Partner_Contract_Item::Update($one);
				$localPartnerContractItem[] = $one["id"];
			}
		}

		if(isset($data["deletedRows"])){
			$data["deletedRows"] = array_filter($data["deletedRows"], function($a){
				return !empty($a["warehouseId"]) && !empty($a["warehouseCode"]) && !empty($a["warehouseName"]);
			});

			foreach ($data["deletedRows"] as $one){
				Partner_Contract_Item::Delete($one);
			}
		}

		return $localPartnerContractItem;
	}


	/**
	 *
	 * Visszaadja a partner és a gép azonosítója alapján a hozzájuk tartozó partner szerződést.
	 *
	 * @param int $partnerId Partner azonosító.
	 * @param int $warehouseId Raktár azonosító.
	 * @return mixed|null
	 * @throws \Exception
	 */
	public static function GetPartnerContract(int $partnerId, int $warehouseId){
		//lekérjük a partnerhez tartozó szerződéseket
		$partnerContractList = \DI\Model\Entity\CityMedia\Partner\Partner_Contract::GetObjectList(array("partnerId" => $partnerId));
		$partnerContract = null;


		if(count($partnerContractList) > 0){
			//végigmegyünk a partner szerződéseinek listáján, és ha megtaláljuk a géphez tartozó tételt, visszaadjuk azt a szerződést
			foreach($partnerContractList as $one){
				try{
					$partnerContractItem = \DI\Model\Entity\CityMedia\Partner\Partner_Contract_Item::Get(array("documentId" => $one->id, "warehouseId" => $warehouseId));

					$partnerContract = $one;

					break;
				}catch(\Exception $ex){
					continue;
				}
			}



		}

		return $partnerContract;
	}

	/**
	 *
	 * Visszaadja a partner és a gép azonosítója alapján a hozzájuk tartozó partner szerződést.
	 *
	 * @param int $partnerId Partner azonosító.
	 * @param int $warehouseId Raktár azonosító.
	 * @return mixed|null
	 * @throws \Exception
	 */
	public static function GetPartnerContractInfo(int $partnerId, int $warehouseId){
		
		$partner = Partner::Get(array("id" => $partnerId));
		$warehouse = Warehouse::Get(array("id" => $warehouseId));

		$dao = Application::GetInstance()->GetSql();

		$sql = "SELECT T1.*, T2.commission, T2.type AS contractType
		, 'ct_partner_contract_item' AS objectType
		, T1.id AS objectId
		FROM ct_partner_contract_item AS T1 
		INNER JOIN ct_partner_contract AS T2 ON T2.id = T1.documentId 
		WHERE T2.partnerId = :partnerId AND T1.warehouseId = :warehouseId";

		$partnerContractItem = $dao->GetObjects($sql, array(":partnerId" => $partnerId, ":warehouseId" => $warehouseId));

		if(count($partnerContractItem) == 0){
			throw new Exception("Nem található $partner->name $warehouse->name szerződés tétel");
		}

		$partnerContractItem = $partnerContractItem[0];

		//Fix bérleti díjas szerződés
		if($partnerContractItem->contractType == 3){
			
		} else{
			
			$item = Item::Get(array("id" => $partnerContractItem->itemId));

			//get item price
			$priceContext = new PriceContext($item);
			
			$price = $priceContext->GetPrice();
			$partnerContractItem->netPrice = $price->price;
			
		}


		return $partnerContractItem;
	}

}