<?php
namespace UI\View\Html\CityMedia\Proceeding;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use DI\Model\Entity\Administration\SystemSettings\TableDefinitions\Field_Valid_Values;

/**
 * CityMedia szerződés lista nézet osztály.
 */
class ProceedingList implements \UI\View\IView{

	private $model;

	/**
	 * @inheritDoc
	 */
	public function CreateContent(){
		$obj = $this->GetModel();
		$qe = $obj[1];
		$model = $obj[0];
		ob_start();

		$datatable = new \UI\View\Html\Grid();
		$datatable->id = $_GET["page"]."_datatable_list";
		$model->SetRows(null);
		$datatable->SetModel($model); 

		$input = new \UI\Html\Select();
		$input->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("citymedia", "ct_proceeding", "status"));
		$input->visible = true;
		$input->disabled = true;
		$datatable->SetColumn("status", $input);


		$input = new \UI\Html\Select();
		$input->SetValidValues(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("citymedia", "ct_proceeding", "type"));
		$input->visible = true;
		$input->disabled = true;
		$datatable->SetColumn("type", $input);
       

		//megtekintés gomb
		$input = new \UI\Html\Button();
		$input->innerHtml = Trans::Get("view");
		$input->type = "submit";
		$input->visible = true;

		$datatable->SetColumn("link", $input);


		?>

		<script>
            $(function () {
                var table = $(`${window.elementID}_datatable_list`).datatable({
                    ajax:"api/Control/DataTable/Load",
                    dataSource: { Query: <?php echo json_encode($qe);?>, Columns: <?php echo json_encode($datatable->GetColumns()); ?>},
                    pagination:{recordsPerPage: 50, page:1},
                    enableContextMenu: false,
                    sort: [{column: "id", type: "DESC"}]
                });

                $(`${window.elementID}_datatable_list`).on("pageLoad", function(){
                    //ha csak egy üres sort tartalmaz a táblázat, töröljük
                    Application.UI.Grid.HandleListView(table);


                    $(this).find("tbody tr td:last-child() button").wrap(`<a class="button"></a>`);
                    $(this).find("tbody tr td:last-child() a").each(function(){
                        $(this).prop("href", `/CityMedia/Proceeding/Proceeding/?id=${($(this).find("button").val())}`);
                        $(this).prop("target", '_blank');
                    });

                });


                $(`${window.elementID}_addNew_button`).on("click", function () {
                    window.location = window.location.origin + "/CityMedia/Proceeding/Proceeding/";
                });


                $(`${window.elementID}_addNew_button2`).on("click", function () {
                    window.location = window.location.origin + "/CityMedia/Proceeding/Proceeding/";
                });
            });
		</script>

		<h1 class="center-text"><?php echo Trans::Get("list_of_proceeding"); ?></h1>

        <div class="row">
            <div class="col float-right">
				<?php
					$button = new \UI\Html\AddObjectButton();
					$button->class[] = "float-right";
					$button->id = $_GET["page"]."_addNew_button2";
					$button->SetTitle(Trans::Get("add"));

					echo $button->Render();
				?>
            </div>
        </div>
		<div class="row center-text">
			<div class="col-12">
				<?php
					echo $datatable->Render();
				?>
			</div>

		</div>
		<div class="row">
			<div class="col float-right">
				<?php
					$button = new \UI\Html\AddObjectButton();
					$button->class[] = "float-right";
					$button->id = $_GET["page"]."_addNew_button";
					$button->SetTitle(Trans::Get("add"));

					echo $button->Render();
				?>
			</div>
		</div>

		<?php

		$this->content = ob_get_contents();
		ob_end_clean();
	}

	/**
	 * @inheritDoc
	 */
	public function Render(){
		$this->CreateContent();
		return $this->content;
	}

	/**
	 * @inheritDoc
	 */
	public function SetModel($obj){
		$this->model = $obj;
	}

	/**
	 * @inheritDoc
	 */
	public function &GetModel(){
		return $this->model;
	}
}