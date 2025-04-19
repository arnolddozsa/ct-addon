<?php

namespace UI\View\Pub\Pages\CityMedia;

use Control\Administration\Definitions\Localization\Translate\Translate;
use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use UI\Html\Button;

/**
 * Alkatrész felhasználás report lap.
 */
class FixtureUsageReport extends \UI\View\Pub\Pages\Page{

	/**
	 *
	 * Létrehozza a weblap tartalmát.
	 *
	 */

	public function __construct(){
		parent::__construct();
		$this->SetTitle(Trans::Get("fixture_usage"));
	}


	public function CreateContent(){
		//url = $this->namespace;

		$printPageParams = array("namespace" => "CityMedia\\FixtureUsageReport\\");


		ob_start();

		?>
		<script>
            $(function() {
                var form = $("#form");


                function UpdateButtons() {
                    var data = Application.UI.Form.Serialize(form);


                    $.each(data, function(key, value) {
                        if (value === "" || value === 0) {
                            delete data[key];
                        }
                    });


                    //Application.DI.Ajax.post("api/Control/Reports/ReportPage/GetContent", {printPageParams: <?php echo json_encode($printPageParams); ?>, templateParams: data}, "json")
                    Application.DI.Ajax.post("api/Control/Reports/ReportPage/ReportButtonControl", {
                        printPageParams: <?php echo json_encode($printPageParams); ?>,
                        templateParams: data
                    }, "json")
                        .done(function(data) {
                            Application.UI.Dialog("Műveletek", "", 0, {
                                innerHTML: data,
                                width: Math.floor($(window).width() / 6)
                            });
                        });
                }


                //enterre is hozza
                $(form).on("keydown", function(e) {

                    if (e.which == 13 || e.keyCode == 13) {
                        e.preventDefault();
                        UpdateButtons();
                    }
                });


                $("#search").on("click", function(e) {
                    e.preventDefault();

                    UpdateButtons();
                });


                $(`${window.elementID}_isAllWarehouse`).on("change", function (e){
					if($(this).is(":checked")){
                        $("#warehouseInput").hide();
                        $(`${window.elementID}_warehouseId`).removeAttr("ng-required");
                        $(`${window.elementID}_warehouseId`).val(null);
                        $(`${window.elementID}_warehouseId`).data("nubes-cfl2").setTitles();
	                }else{
                        $("#warehouseInput").show();
                        $(`${window.elementID}_warehouseId`).attr("ng-required", "displayCondition");
					}
                });


            });
		</script>

		<h1 class="center-text"><?php echo Translate::Get("fixture_usage");?></h1>
		<div class="container-fluid">
			<div class="row justify-content-center">
				<div class="col-4">
					<form id="form">


						<div class="input-group">
							<?php
								$checkbox = new \UI\Html\InputCheckBox();
								$checkbox->name="isAllWarehouse";
								$checkbox->id="". (new \ReflectionClass($this))->getShortName() . "_isAllWarehouse";

								echo $checkbox->Render();

							?>
							&nbsp;<label for="<?php echo (new \ReflectionClass($this))->getShortName(); ?>_isAllWarehouse"><?php echo Translate::Get("all_warehouse");?></label>
						</div>

						<div id="warehouseInput">
							<label for="<?php echo (new \ReflectionClass($this))->getShortName(); ?>_warehouseId"><?php echo Translate::Get("warehouse");?></label>
							<div class="input-group">
								<?php
									$warehouseId = new \UI\Html\InputCfl2();
									$warehouseId->name = "warehouseId";
									$warehouseId->id = (new \ReflectionClass($this))->getShortName()."_warehouseId";
									$warehouseId->data["class"] = "\DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse";
									$warehouseId->data["cfl_column"] = "id";
									$warehouseId->required = true;
									echo $warehouseId->Render();
								?>
							</div>
						</div>




						<label for="<?php echo (new \ReflectionClass($this))->getShortName(); ?>_dateFrom"><?php echo Translate::Get("date_from");?></label>
						<div class="input-group">
							<?php
								$dateFrom = new \UI\Html\InputDate();
								$dateFrom->name="dateFrom";
								$dateFrom->id="". (new \ReflectionClass($this))->getShortName() . "_dateFrom";
								$dateFrom->required = true;
								$dateFrom->SetValue(date("Y-m-d"));
								echo $dateFrom->Render();

							?>
						</div>
						<label for="<?php echo (new \ReflectionClass($this))->getShortName(); ?>_dateTo"><?php echo Translate::Get("date_to");?></label>
						<div class="input-group">
							<?php
								$dateTo = new \UI\Html\InputDate();
								$dateTo->name="dateTo";
								$dateTo->id="". (new \ReflectionClass($this))->getShortName() . "_dateTo";
								$dateTo->required = true;
								$dateTo->SetValue(date("Y-m-d"));

								echo $dateTo->Render();

							?>
						</div>


						<div class="input-group">
							<?php
								$button = new Button();
								$button->type = "button";
								$button->id = "search";
								$button->innerHtml = Translate::Get("search");
								echo $button->Render();
							?>
						</div>

					</form>
				</div>
			</div>
		</div>


		<?php



		$this->content = ob_get_contents();
		ob_end_clean();

	}

}