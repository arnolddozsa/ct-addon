<?php
namespace Control\CityMedia\Telemetry;

use Control\Administration\Definitions\DocumentNumber\Document_Number as Document_Number;
use Control\Application;

/**
 * Telemetria vezérlő osztály.
 */
class Telemetry extends \Control\EntityController{

	/**
	 *
	 * Telemetria felvétel.
	 *
	 * @param array $data Telemetria adatok.
	 * @throws \Exception
	 */
	public static function AddTelemetryData(array $data){
		$dao = Application::GetInstance()->GetSql();

		try{
			$dao->StartTransaction();

			$request = \Control\Application::GetInstance()->GetApplicationController()->GetRequest();
			$serialNumber = explode("_", $request->headers["AUTHORIZATION"])[1];
			
			if(empty($serialNumber)){
				throw new \Exception("Telemetry couldn't find serialnumber in the authorization string");
			}

			$warehouse = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::Get(array("U_serialNumber" => $serialNumber));

			$telemetryEntries = [];
            foreach ($data as $key => $value) {
				$telemetry = new \DI\Model\Entity\CityMedia\Telemetry\Telemetry;

				
				$telemetry->warehouseId = $warehouse->id;

				$telemetry->type = $value["logType"];
				$telemetry->description = $value["description"];
				$telemetry->value = isset($value["value"])?$value["value"]:null;
				$telemetry->piLogId = $value["id"];
				$telemetry->piLogCreateDate = date("Y-m-d H:i:s", strtotime($value["createDate"]));
				
				

				$telemetryEntries[] = $telemetry->ToArray();
            }

			foreach ($telemetryEntries as $key => $value) {
				self::Add($value);
			}


            
			$dao->Commit();

			//@TODO
			//on type "hopperEmpty" and "hopperLowLevel" send mail or notification to someone!
			//Temporary solution (in this case the pi wont set the data to isSent)
			//in production this must be removed
			//throw new \Exception();

			return true;

		}catch(\Exception $ex){
			$dao->Rollback();
			throw $ex;
		}
	}

	public static function PiDisconnect($serialNumber){

		$warehouse = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::Get(array("U_serialNumber" => $serialNumber));

		$telemetry = new \DI\Model\Entity\CityMedia\Telemetry\Telemetry;

		$telemetry->warehouseId = $warehouse->id;

		$telemetry->type = "disconnect";
		$telemetry->description = "A gép lekapcsolódott a szerverről";
		$telemetry->value = null;
		$telemetry->piLogId = null;
		$telemetry->piLogCreateDate = null;

		self::Add($telemetry->ToArray());

		return $warehouse;

	}

	public static function PiConnect($serialNumber, $ipAddress){

		$warehouse = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::Get(array("U_serialNumber" => $serialNumber));

		$telemetry = new \DI\Model\Entity\CityMedia\Telemetry\Telemetry;

		$telemetry->warehouseId = $warehouse->id;

		$telemetry->type = "connect";
		$telemetry->description = "A gép kapcsolódott a szerverhez " . $ipAddress;
		$telemetry->value = null;
		$telemetry->piLogId = null;
		$telemetry->piLogCreateDate = null;

		self::Add($telemetry->ToArray());

		return $warehouse;

	}

}
