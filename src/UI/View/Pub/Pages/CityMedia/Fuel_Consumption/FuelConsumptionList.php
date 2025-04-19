<?php

namespace UI\View\Pub\Pages\CityMedia\Fuel_Consumption;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use Control\Application;
use DI\Model\Data\DataTableCreator;
use DI\Model\Sql\Query as QE;

/**
 * Üzemanyag felhasználás lista lap.
 */
class FuelConsumptionList extends \UI\View\Pub\Pages\PageView{

	/**
	 * Üzemanyag felhasználás lista konstruktor.
	 */

	public function __construct(){
		parent::__construct();
		$this->SetTitle(Trans::Get("list_of_fuel_consumptions"));
	}

	/**
	 *
	 * Létrehozza a weblap tartalmát,
	 *
	 */

	public function CreateContent(){
		$__DAO = Application::GetInstance()->GetSql();
		$UI = $this->GetUI();

		ob_start();


		try{

			$qe = new QE\Query();
			$t1 = $qe->AddFromTable("ct_fuel_consumption", "T1");


			$field = $qe->AddField("documentNumber");
			$field->SetReference($t1);
			$field = $qe->AddField("docDate");
			$field->SetReference($t1);
			$field = $qe->AddField("createDate");
			$field->SetReference($t1);
			$field = $qe->AddField("id", "link");
			$field->SetReference($t1);


			$dt_creator = new DataTableCreator($__DAO);
			$data = $dt_creator->FromDataSource($qe);

			foreach ($data->GetColumns() as $column){
				$column->SetEditable(false);
			}

		}catch (\Exception $ex){
			(new \DI\Model\Exception\CommonException($ex))->Store();

			header("Location: ".$_SERVER["REQUEST_URI"]);
			exit();
		}


		$view = new $UI();
		$view->SetModel(array($data, $qe));
		echo $view->Render();

		$this->content = ob_get_contents();
		ob_end_clean();
	}

}