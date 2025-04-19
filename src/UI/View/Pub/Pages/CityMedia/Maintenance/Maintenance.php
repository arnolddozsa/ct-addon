<?php
namespace UI\View\Pub\Pages\CityMedia\Maintenance;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use Control\Application;
use DI\Model\Data\DataTableCreator;
use DI\Model\Entity\FormMode;
use DI\Model\Sql\Query as QE;

/**
 * Karbantartás nézet osztály.
 */
class Maintenance extends \UI\View\Pub\Pages\PageView{

	/**
	 *
	 * Karbantartás konstruktora.
	 *
	 */

	public function __construct(){
		parent::__construct();
		$this->SetTitle(Trans::Get("maintenance"));
	}

	/**
	 *
	 * Létrehozza a megjelenítendő tartalmat.
	 *
	 * @throws \Exception
	 *
	 */

	public function CreateContent(){
		$__DAO = Application::GetInstance()->GetSql();
		$DI = $this->GetDI();
		$UI = $this->GetUI();

		ob_start();

		try {

			//alapértelmezett form mód
			$formmode = FormMode::$ok;

			//ha nincs érvényes id paraméter megadva az url-ben, akkor hibát dob
			if(isset($_GET["id"]) && $_GET["id"] < 1){
				throw new \Exception("Érvénytelen azonosító");
			}

			$id = isset($_GET['id']) ? $_GET['id'] : '';

			$data = array();


			//megpróbál az url-ben lévő id paraméter alapján az adatbázisból lekérni adatot és létrehozni egy objektumot belőle
			try {
				$data["maintenance"] = $DI::Get(array("id" => $id));

				//ha nem sikerül neki, akkor hozzáadás módba teszi a form-ot és egy új üres objektumot hoz létre
			} catch (\Exception $ex) {
				$data["maintenance"] = new $DI();



				$formmode = FormMode::$add;
			}

			$dt_creator = new DataTableCreator($__DAO);

			$qe = new QE\Query();
			$t1 = $qe->AddFromTable("ct_maintenance_item", "T1");


			$field = $qe->AddField("id");
			$field->SetReference($t1);
			$field = $qe->AddField("name");
			$field->SetReference($t1);

			$cond = $qe->AddWhere();
			$field = new QE\Field("documentId");
			$field->SetReference($t1);
			$cond->SetLeftField($field);

			$params = array($id);

			$data["maintenanceItem"] = $dt_creator->FromDataSource($qe, $params);
			$data["maintenanceItem"]->SetRows(null);


			$data["maintenanceItem"]->GetColumn("id")->SetEditable(false);
			$data["maintenanceItem"]->GetColumn("id")->SetVisible(false);



		}catch (\Exception $ex){
			(new \DI\Model\Exception\CommonException($ex))->Store();

			if(!isset($_GET["id"]) || (isset($_GET["id"]) && $_GET["id"] < 1) || $formmode == FormMode::$add){
				header("Location: " . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
			}else{
				header("Location: ".$_SERVER["REQUEST_URI"]."?".http_build_query(array("id" => $_GET["id"])));
			}
			exit();
		}


		$view = new $UI();
		//átadjuk model-ként a view-nak az objektumot és a form módját
		$view->SetModel(array($data, "query" => $qe, "params" => $params, "formmode" => $formmode, "control" => $this->GetControl()));
		echo $view->Render();

		$this->content = ob_get_contents();
		ob_end_clean();
	}


}