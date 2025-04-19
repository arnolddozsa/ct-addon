<?php

namespace UI\View\Pub\Pages\CityMedia;


use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use Control\Application;
use DI\Model\Entity\FormMode;

/**
 * Raktár lap.
 */
class Warehouse extends \UI\View\Pub\Pages\Page{

	/**
	 *
	 * Raktár konstruktora.
	 *
	 */

	public function __construct(){
		parent::__construct();
		$this->SetTitle(Trans::Get("warehouse"));
		$this->htmlFooter->AddScript("<script src=\"/vendor/mhzq-com/citymedia-addon/src/UI/js/socket.io.min.js\"></script>");
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
				//$data["warehouse"] = $__DAO->GetObjects("SELECT * FROM " . $_GET["table_schema"] .".nubes_warehouse WHERE id = :id", ["id" => $id], "\\DI\\Model\\Entity\\Administration\\Definitions\\StockManagement\\Warehouse\\Warehouse")[0];
				$data["warehouse"] = \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse::Get(array("id" => $id));

				//ha nem sikerül neki, akkor hozzáadás módba teszi a form-ot és egy új üres objektumot hoz létre
			} catch (\Exception $ex) {
				$data["warehouse"] = new \DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse();
			}



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
		$view->SetModel(array($data));
		echo $view->Render();

		$this->content = ob_get_contents();
		ob_end_clean();
	}

}