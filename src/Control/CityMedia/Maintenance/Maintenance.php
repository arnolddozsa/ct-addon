<?php
namespace Control\CityMedia\Maintenance;

use Control\Administration\Definitions\DocumentNumber\Document_Number as Document_Number;
use Control\Application;

/**
 * Karbantartás vezérlő osztály.
 */
class Maintenance extends \Control\EntityController{

	/**
	 *
	 * Karbantartás felvétel.
	 *
	 * @param array $data Karbantartás adatok.
	 * @return \DI\Model\Entity\CityMedia\Maintenance\Maintenance Karbantartás objektum.
	 * @throws \Exception
	 */
	public static function Add(array $data): \DI\Model\Entity\CityMedia\Maintenance\Maintenance{
		$dao = Application::GetInstance()->GetSql();

		try{
			$dao->StartTransaction();

			$data["maintenance"]["documentNumber"] = Document_Number::GenerateSave(self::CreateObject());


			$maintenance = parent::Add($data["maintenance"]);


			if(isset($data["maintenanceItem"])){
				$maintenanceItem = self::HandleMaintenanceItem($data["maintenanceItem"], $maintenance);
			}


			$dao->Commit();


			return $maintenance;

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
	 * @return \DI\Model\Entity\CityMedia\Maintenance\Maintenance Karbantartás objektum.
	 * @throws \Exception
	 */
	public static function Update(array $data): \DI\Model\Entity\CityMedia\Maintenance\Maintenance{
		$dao = Application::GetInstance()->GetSql();

		try{
			$dao->StartTransaction();

			unset($data["maintenance"]["documentNumber"]);


			$maintenance = parent::Update($data["maintenance"]);


			if(isset($data["maintenanceItem"])){
				$maintenanceItem = self::HandleMaintenanceItem($data["maintenanceItem"], $maintenance);
			}


			$dao->Commit();


			return $maintenance;

		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}


	/**
	 *
	 * Karbantartás tétel adatok kezelése.
	 *
	 * @param array $data Karbantartás tétel adatok.
	 * @param \DI\Model\Entity\CityMedia\Maintenance\Maintenance $maintenance Karbantartás objektum.
	 * @return array
	 * @throws \Exception
	 */
	private static function HandleMaintenanceItem(array $data, \DI\Model\Entity\CityMedia\Maintenance\Maintenance $maintenance): array{
		$localMaintenanceItem = array();

		if(isset($data["insertedRows"])){
			$data["insertedRows"] = array_filter($data["insertedRows"], function($a){
				return !empty($a["name"]);
			});


			foreach ($data["insertedRows"] as $one){
				$one["documentId"] = $maintenance->id;

				$localMaintenanceItem[] = Maintenance_Item::Add($one);
			}
		}

		if(isset($data["modifiedRows"])){
			$data["modifiedRows"] = array_filter($data["modifiedRows"], function($a){
				return !empty($a["name"]);
			});


			foreach ($data["modifiedRows"] as $one){
				$one["documentId"] = $maintenance->id;

				Maintenance_Item::Update($one);
				$localMaintenanceItem[] = $one["id"];
			}
		}

		if(isset($data["deletedRows"])){
			$data["deletedRows"] = array_filter($data["deletedRows"], function($a){
				return !empty($a["name"]);
			});

			foreach ($data["deletedRows"] as $one){
				Maintenance_Item::Delete($one);
			}
		}

		return $localMaintenanceItem;
	}

}