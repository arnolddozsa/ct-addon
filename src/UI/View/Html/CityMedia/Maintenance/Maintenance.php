<?php
namespace UI\View\Html\CityMedia\Maintenance;

use Control\Administration\Definitions\Localization\Translate\Translate as Trans;
use DI\Model\Entity\FormMode;
use UI\Html\Button\CancelButton;
use UI\Html\Button\SubmitButton;
use UI\Html\InputDate;
use UI\Html\InputText;

/**
 * Karbantartás nézet osztály.
 */
class Maintenance implements \UI\View\IView{

	private $model;

	/**
	 * @inheritDoc
	 */
	public function CreateContent(){
		$maintenance = $this->GetModel()[0]["maintenance"];
		$maintenanceItem = $this->GetModel()[0]["maintenanceItem"];

		$qe = $this->GetModel()["query"];
		$params = $this->GetModel()["params"];

		//control elérési útvonala
		$control = str_replace("\\", "/", $this->GetModel()["control"]);

		$formmode = $this->GetModel()["formmode"];
		FormMode::Check($formmode);

		ob_start();

		//grid
		$grid = new \UI\View\Html\Grid();
		$grid->id = $_GET["page"]."_maintenanceItem";
		$grid->SetModel($maintenanceItem);

		?>

		<script>
		    $(function () {

                var controlPath = "<?php echo $control; ?>";
                var formmode = "<?php echo $formmode; ?>";

                var form = $(window.elementID + "_form1");


                //tábla létrehozás
                var table1 = $(window.elementID + "_maintenanceItem").datatable({
                    ajax: "api/Control/DataTable/Load",
                    dataSource: {
                        Query: <?php echo json_encode($qe); ?> ,
                        Columns: <?php echo json_encode($grid->GetColumns()); ?>,
                        Parameters: <?php echo json_encode($params); ?>},
                    pagination: {recordsPerPage: 15, page: 1},
                    sort: [{column: "name", type: "ASC"}]
                });


                table1.on("pageLoad", function () {

                    //szerkesztjük a táblát
                    //végigmegyünk a sorokon
                    $(table1).find("tbody tr").each(function(i, one){
                        //végigmegyünk a mezőkön
                        $(one).find("td input, td select").each(function (j, input) {

                            //kötelező mezők beállítása
                            if(input.name == "name"){
                                $(input).attr("ng-required", "displayCondition");

                            }
                        });
                    });

                });


                $("#tabs").tabs({
                    create: function (event, ui) {
                        Application.UI.Grid.Refresh();
                    },
                    active: 0
                });


                $(`${window.elementID}_warehouseId`).on("cflSelect", function (e, row){
                    $(`${window.elementID}_warehouseName`).val(row.name);
                    $(`${window.elementID}_warehouseCode`).val(row.code);
                });

                $(`${window.elementID}_warehouseId`).on("cflNotSelect", function (e, row){
                    $(`${window.elementID}_warehouseName`).val(null);
                    $(`${window.elementID}_warehouseCode`).val(null);
                });

                function localSetFormModeToAdd() {
                    formmode = "add";
                    Application.UI.Form.SetMode(formmode, form.formWidget());

                    //a táblának átadjuk az eredeti query-t
                    table1.data("nubes-datatable").option("dataSource", {
                        Query: <?php echo json_encode($qe); ?> ,
                        Columns: <?php echo json_encode($grid->GetColumns()); ?>,
                        Parameters: <?php echo json_encode($params); ?>,
                        Array: [<?php echo \json_encode(new \DI\Model\Entity\CityMedia\Maintenance\Maintenance_Item()); ?>]
                    });
                    //ürítjük a tábla betöltött lapjait
                    table1.data("nubes-datatable").loadedPages = [];
                    //generáljuk a táblát
                    table1.data("nubes-datatable").requestForView();


                    var tabs = $(`#tabs`).find("li");
                    $(tabs[1]).hide();

                }

                if (formmode == "add") {
                    localSetFormModeToAdd();
                }

                //add mode button
                $(document).on("click", window.elementID + "_setAddFormModeButton", localSetFormModeToAdd);

                //felvesszük a contextmenu-t
                $(window.elementID + "_headDiv").addContextMenu('[{"id": "add", "name":"<?php echo Trans::Get("add_new"); ?>"}, {"id": "delete", "name": "<?php echo Trans::Get("delete"); ?>"}]');

                //definiáljuk a contextmenu klikk eseményét
                Application.UI.ContextMenu.addContextMenuEvent(form, function () {
                    switch (this.id) {
                        case "add":
                            localSetFormModeToAdd();
                            break;

                        case "delete":
                            Application.UI.DeleteDialog(controlPath, {"id": "<?php echo $_GET["id"]; ?>"});
                            break;
                    }
                });


                form.submit(function (e) {
                    e.preventDefault();

                    //üres sorokat töröljük
                    table1.find("tbody tr input[name=name]").filter((j, one) => {
                        return $(one).val() < 1;
                    }).closest("tr").remove();


                    if ($(form).checkRequiredFieldValues()) {

                        var controller = {};
                        controller.maintenance = Application.UI.Form.Serialize($(this));

                        controller.maintenance.id = <?php echo isset($maintenance->id) ? $maintenance->id : "undefined";?>;
                        controller.maintenance.id = (formmode == "add") ? "undefined" : controller.maintenance.id;

                        controller.maintenanceItem = table1.data("nubes-datatable").serialize();

                        var mode = form.data("nubes-formWidget").getMethod();
                        if(mode == undefined){
                            return;
                        }




                        $(form).find("button[type=submit]").attr("disabled", "disabled");
                        Application.DI.Ajax.post("api" + controlPath + "/" + mode, {controller}, "json")
                            .done(function (data) {
                                switch (mode) {
                                    case "Add":
                                        Application.Tools.BrowserInfo.History.replaceState({}, document.title, window.location.origin + window.location.pathname + "?id=" + data.id);
                                        window.location.reload();

                                        break;
                                    case "Update":
                                        $(form).data("nubes-formWidget").setMode("ok");
                                        break;
                                }
                            })
                            .always(() => {
                                $(form).find("button[type=submit]").removeAttr("disabled");
                            });



                    }
                });

            });

		</script>


		<h1 class="center-text"><?php echo Trans::Get("maintenance"); ?></h1>


		<form id="<?php echo $_GET["page"]; ?>_form1" class='formwidget'>
			<div id="<?php echo $_GET["page"]; ?>_headDiv" class="row">
				<div class="col-md-6">
					<dt><?php echo Trans::Get("document_number"); ?></dt>
					<dd>
						<?php
							$documentNumber = new \UI\Html\InputText();
							$documentNumber->name = "documentNumber";
							$documentNumber->id = $_GET["page"]."_documentNumber";
							$documentNumber->data["defaultvalue-ajax"] = "Control\Administration\Definitions\DocumentNumber\Document_Number\Generate";
							$documentNumber->data["defaultvalue-ajax-data"] = json_encode(array("namespace" => get_class($maintenance)));
							$documentNumber->required = true;
							$documentNumber->readonly = true;
							$documentNumber->maxlength = 100;
							$documentNumber->SetValue($maintenance->documentNumber);

							echo $documentNumber->Render();
						?>
					</dd>

                    <dt><?php echo Trans::Get("maintenance_date"); ?></dt>
                    <dd>
						<?php
							$maintenanceDate = new InputDate();
							$maintenanceDate->name = "maintenanceDate";
							$maintenanceDate->id = $_GET["page"]."_maintenanceDate";
							$maintenanceDate->data["defaultvalue"] = date("Y-m-d");
							$maintenanceDate->required = true;
							$maintenanceDate->SetValue($maintenance->maintenanceDate);

							echo $maintenanceDate->Render();
						?>
                    </dd>
				</div>

                <div class="col-md-6">
                    <dt><?php echo Trans::Get("warehouse"); ?></dt>
                    <dd>
                        <?php
	                        $warehouseId = new \UI\Html\InputCfl2();
	                        $warehouseId->name = "warehouseId";
	                        $warehouseId->SetValue($maintenance->warehouseId);
	                        $warehouseId->id = $_GET["page"]."_warehouseId";
	                        $warehouseId->data["class"] = "\DI\Model\Entity\Administration\Definitions\StockManagement\Warehouse\Warehouse";
	                        $warehouseId->data["cfl_column"] = "id";
	                        $warehouseId->required = true;

	                        echo $warehouseId->Render();
                        ?>
                    </dd>

                    <dt><?php echo Trans::Get("warehouse_name"); ?></dt>
                    <dd>
                        <?php
                            $warehouseName = new InputText();
                            $warehouseName->name = "warehouseName";
                            $warehouseName->id = $_GET["page"]."_warehouseName";
                            $warehouseName->SetValue($maintenance->warehouseName);
                            $warehouseName->required = true;
                            $warehouseName->readonly = true;

                            echo $warehouseName->Render();

                        ?>
                    </dd>

                    <dt><?php echo Trans::Get("warehouse_code"); ?></dt>
                    <dd>
		                <?php
			                $warehouseCode = new InputText();
			                $warehouseCode->name = "warehouseCode";
			                $warehouseCode->id = $_GET["page"]."_warehouseCode";
			                $warehouseCode->SetValue($maintenance->warehouseCode);
			                $warehouseCode->required = true;
			                $warehouseCode->readonly = true;

			                echo $warehouseCode->Render();

		                ?>
                    </dd>
                </div>
			</div>

			<div class="col-12 form-group" id="tabs" style="overflow: hidden">
				<ul>
					<li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-1"><?php echo Trans::Get("content"); ?></a></li>
					<li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>#tabs-2"><?php echo Trans::Get("signature"); ?></a></li>
				</ul>


                <div id="tabs-1" class="center-text">
                    <div class="col-12">
                        <?php
                            echo $grid->Render();
                        ?>
                    </div>
                </div>


                <div id="tabs-2">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <?php
                                echo \Control\EntityController::RenderSignatureUI($maintenance);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                                echo \Control\EntityController::RenderFileManager($maintenance);
                            ?>
                        </div>
                    </div>
                </div>



            </div>

			<div class="row">
				<div class="col-12">
					<?php
						$button = new SubmitButton();
						echo $button->Render();


						$cancelButton = new CancelButton();
						echo $cancelButton->Render();
					?>
				</div>
			</div>
		</form>


		<div class="row">
			<?php
				if($formmode != FormMode::$add) {
					$button = new \UI\Html\SetAddFormModeButton();
					$button->SetTitle(Trans::Get("add_new"));

					echo $button->Render();
				}
			?>
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