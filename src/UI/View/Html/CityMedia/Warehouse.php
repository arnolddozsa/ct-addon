<?php

namespace UI\View\Html\CityMedia;


use Control\Administration\Definitions\Localization\Translate\Translate;
use Control\Application;
use UI\Html\Button;
use UI\Html\Chart;
use UI\Html\InputDate;
use UI\Html\InputTypes;

use DI\Model\Data\DataTableCreator;
use DI\Model\Entity\Administration\SystemSettings\TableDefinitions\Field_Valid_Values;
use DI\Model\Entity\CityMedia\Partner\Partner_Contract;
use DI\Model\Entity\CityMedia\Proceeding\Proceeding;
use DI\Model\Sql\Query as QE;
use UI\Html\Button\SubmitButton;
use UI\Html\Signature;

/**
 *
 * Raktár nézet osztály.
 *
 */
class Warehouse implements \UI\View\IView
{

    private $model;

    /**
     * @inheritDoc
     */
    public function CreateContent()
    {

        $warehouse = $this->GetModel()[0]["warehouse"];

        if ($warehouse->id < 1) {
            header("Location: /");
            exit();
        }

        ob_start();

?>

        <script>
            var socket;
            $(function() {

                <?php
                $root_web = \Control\Application::GetInstance()->config->root_web;
                $root_web = rtrim($root_web, "/");
                $root_web = \explode(":", $root_web);
                $root_web = $root_web[0] . ":" . $root_web[1];
                ?>
                socket = io("<?php echo $root_web ?>:40005");
                var room = <?php echo !empty($warehouse->U_serialNumber) ? '"' . $warehouse->U_serialNumber . '"' : "null"; ?> || null;
                socket.on("connect", () => {
                    
                    socket.emit("joinRoom", {room: room, isMachine: false});
                    $("#socketServerInfo").addClass("d-none");
                });

                socket.on("disconnect", () => {
                    $("#socketServerInfo").removeClass("d-none");
                });

                socket.on("piconnected", ({room, data}) => {
                    
                    if (data.connected) {
                        $(".coinMachineInfo").removeClass("bg-danger").addClass("bg-success");
                        $(".coinMachineInfo").find(".statusText").text("online");
                        $(".coinMachineInfo").find("i").removeClass("fa-times-circle").addClass("fa-check-circle");

                        socket.emit("getInfo", {room: room});
                        socket.emit("getErrors", {room: room}, (data, err) => {
                            if (err) {
                                console.log(err);
                                return;
                            }

                            if (data.data.length) {
                                $("#errors").removeClass("d-none");
                                $("#errorList").html(data.data.map((e) => {
                                    return `<li class="list-group-item"><div class="row"><div class="col"><i class="fa-exclamation-triangle fas text-warning"></i> <span>${e.errorType}</span> <span>${e.description}</span></div></div></li>`
                                }).join());
                            }

                        });

                    } else {
                        $(".coinMachineInfo").removeClass("bg-success").addClass("bg-danger");
                        $(".coinMachineInfo").find(".statusText").text("offline");
                        $(".coinMachineInfo").find("i").removeClass("fa-check-circle").addClass("fa-times-circle");
                    }
                });

                socket.on("infoChange", ({room, data}) => {
                    console.log(data);
                    $(".coinCount").text(data.salesInfo.coinCount);
                    $(".salesCount").text(data.salesInfo.salesCount);
                    $(".salesCountAfterLastFillUp").text(data.salesInfo.salesCountAfterLastFillUp);
                });

                socket.on("coinCount", ({room, data}) => {
                    data = data.data;
                    UIkit.notification({
                        message: `Kidobott érmék darabszáma: ${data}`,
                        status: 'success',
                        timeout: 0
                    });

                });

                $("#coinDropTest").on("click", function() {
                    socket.emit("tossACoinToYourWitcher", {room: room}, (response, error) => {
                        if (error) {
                            alert(error.message);
                            return;
                        }
                    });
                });


                $("#emptyHopper").on("click", function(e) {
                    e.preventDefault();

                    if (!confirm("Biztosan üríted?")) {
                        return;
                    }

                    $(this).addClass("loading");

                    socket.emit("emptyHopper", {room: room}, (response, error) => {
                        $(this).removeClass("loading");
                        if (error) {
                            alert(error.message);
                            return;
                        }
                        UIkit.notification({
                            message: `A gép most megszámolja az érméket`,
                            status: 'success'
                        });
                    });

                });

                $("#fillUp").on("click", function(e) {
                    e.preventDefault();

                    var coinCount = parseInt($(this).closest(".card").find("input[type=number]").val())

                    if (isNaN(coinCount)) {
                        alert($(this).closest(".card").find("input[type=number]").val() + " nem egy szám");
                        return;
                    }

                    if (!confirm(`Biztosan feltölti? ${coinCount} darabszámmal?`)) {
                        return;
                    }

                    $(this).showLoading();

                    socket.emit("fillUpHopper", {room: room, data: {
                            coinCount: coinCount
                        }},
                        async (response, error) => {
                            $(this).hideLoading();
                            if (error) {
                                alert(error.message);
                                return;
                            }

                            var res = await fetch("api/Control/CityMedia/Proceeding/Proceeding/CreateByHopperFillUp", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({
                                    warehouseId: <?php echo $warehouse->id ?>,
                                    coinCount: coinCount
                                })
                            });
                            if (res.ok) {

                                res = await res.json();


                                UIkit.notification({
                                    message: `Bizonylat létrehozva ${res.documentNumber} bizonylatszámon`,
                                    status: 'success'
                                });

                            } else {
                                res = await res.json();
                                UIkit.notification({
                                    message: `Bizonylat létrehozás sikertelen! Hozza létre manuálisan!`,
                                    status: 'danger'
                                });
                            }

                        });

                });

                $("#deleteErrors").on("click", function() {

                    socket.emit("deleteErrors", {room: room}, (data) => {
                        if (data.status == "OK") {
                            $("#errors").addClass("d-none");
                            UIkit.notification("Sikeres törlés", {
                                status: "success"
                            });
                        } else {
                            UIkit.notification("Sikertelen hibatörlés" + data.message, {
                                status: "danger"
                            });
                        }
                    });
                });

                setInterval(() => {
                    $("#currentDateTime").text(new Date().toLocaleString());
                }, 1000);

            });
        </script>

        <div class="center-text">

            <h1><?php echo Translate::Get("machines"); ?></h1>
            <div class="position-relative w-100">
                <div class="coinMachineInfo bg-danger container-fluid text-white w-100">
                    Az (<?php echo $warehouse->code; ?> <?php echo $warehouse->name; ?>) automata <span class="statusText">offline</span> <i class="fas fa-times-circle"></i>
                </div>
                <div id="socketServerInfo" class="bg-danger container-fluid position-absolute text-white" style="top: 0;">
                    Szerver <span>offline</span> <i class="fas fa-times-circle"></i>
                </div>
            </div>

            <!-- <a class="button"
        href="/Administration/Definitions/StockManagement/Warehouse/Warehouse/?id=<?php echo $warehouse->id; ?>"
        target="_blank">
        <button class="button btn btn-primary" name="link" type="submit" title="" value="1" style="" data-cfl_column=""
            data-query_object="" data-query_params="" data-class="">
            <?php echo Translate::Get("view_machine"); ?>
        </button>
    </a>



    <a class="button"
        href="/StockManagement/StoreTransactions/Inventory_Transfer/Inventory_Transfer/?sourceWarehouseId=<?php echo $warehouse->id; ?>"
        target="_blank">
        <button class="button btn btn-primary" name="link" type="submit" title="" value="1" style="" data-cfl_column=""
            data-query_object="" data-query_params="" data-class="">
            <?php echo Translate::Get("inventory_transfer"); ?>
        </button>
    </a> -->



        </div>

        <div class="container-fluid">
            <div class="justify-content-center row">
                <div class="card m-3" style="width: 18rem;">

                    <div class="card-body">
                        <h5 class="card-title">Infó</h5>
                        <p class="card-text">Név: <?php echo $warehouse->code; ?> <?php echo $warehouse->name; ?></p>
                        <p class="card-text">Sorozatszám: <?php echo $warehouse->U_serialNumber; ?></p>
                        <p class="card-text">Érmék darabszáma: <span class="coinCount"></span></p>
                        <p class="card-text">Eladások: <span class="salesCount"></span></p>
                        <p class="card-text">Eladások az utolsó feltöltés óta: <span class="salesCountAfterLastFillUp"></span></p>
                        <p class="card-text">Utolsó feltöltés bejegyzése: <span><?php
                                                                                $dao = Application::GetInstance()->GetSql();
                                                                                $lastFill = $dao->GetObjects("SELECT * FROM ct_telemetry WHERE warehouseId = :warehouseId AND type = 'fillUp' ORDER BY piLogCreateDate DESC LIMIT 1;", array(":warehouseId" => $warehouse->id));
                                                                                if (count($lastFill)) {
                                                                                ?>
                                    <br />
                                    <?php
                                                                                    echo $lastFill[0]->piLogCreateDate;
                                    ?>
                                    <br />
                                <?php
                                                                                    echo str_replace("óta", "előtt", $lastFill[0]->description);
                                                                                }
                                ?></span></p>
                        <p class="card-text">Aktuális dátum és idő:
                        <div id="currentDateTime"></div>
                        </p>
                        <div id="errors" class="d-none card-text">
                            <div><i class="fa-2x fa-exclamation-triangle fas text-warning"></i> <span>A gép hibákat jelez</span></div>
                            <ul id="errorList" class="list-group"></ul>
                        </div>
                        <div class="coinMachineInfo bg-danger container-fluid text-white w-100">
                            Status: <span class="statusText">offline</span> <i class="fas fa-times-circle"></i>
                        </div>

                    </div>
                </div>
                <div class="card m-3" style="width: 18rem;">

                    <div class="card-body">
                        <h5 class="card-title">Érme kidobás teszt</h5>
                        <p class="card-text">Az érme kidobás teszttel fizetés nélkül ellenőrizhető, hogy az érme kidobódik
                        </p>
                        <button id="coinDropTest" class="btn btn-primary">Érme kidobás teszt</button>
                    </div>
                </div>
                <div class="card m-3" style="width: 18rem;">

                    <div class="card-body">
                        <h5 class="card-title">Ürítés</h5>
                        <p class="card-text">Ürítéssel a teljes tartalmat kidobja a gép, valamint megszámolja hány darab
                            volt benne.</p>
                        <button id="emptyHopper" class="btn btn-primary">Ürítés</button>
                    </div>
                </div>
                <div class="card m-3" style="width: 18rem;">

                    <div class="card-body">
                        <h5 class="card-title">Hibák törlése</h5>
                        <p class="card-text">Törölheted a gépben lévő hibákat</p>
                        <button id="deleteErrors" class="btn btn-primary">Törlés</button>
                    </div>
                </div>
                <div class="card m-3" style="width: 18rem;">

                    <div class="card-body">
                        <h5 class="card-title">Feltöltés</h5>
                        <p class="card-text">Feltöltheted érmékkel az eszközt</p>
                        <div class="form-group row">
                            <div class="input-group">
                                <input class="form-control" type="number" value="1" min="1" max="1000" step="1" />
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        db
                                    </div>
                                </div>
                            </div>

                        </div>
                        <button id="fillUp" class="btn btn-primary">Feltöltés</button>
                    </div>
                </div>
            </div>
        </div>

        <?php


        $qe = new QE\Query();
        $t1 = $qe->AddFromTable("ct_telemetry", "T1");


        $field = $qe->AddField("id");
        $field->SetReference($t1);
        $field = $qe->AddField("type");
        $field->SetReference($t1);
        $field = $qe->AddField("description");
        $field->SetReference($t1);
        $field = $qe->AddField("piLogId");
        $field->SetReference($t1);
        $field = $qe->AddField("piLogCreateDate");
        $field->SetReference($t1);
        $field = $qe->AddField("createDate");
        $field->SetReference($t1);

        $where = $qe->AddWhere();
        $field = new QE\Field("warehouseId");
        $field->SetReference($t1);
        $where->SetLeftField($field);
        $where->SetRightValue($warehouse->id);


        $dao = \Control\Application::GetInstance()->GetSql();
        $dt_creator = new DataTableCreator($dao);
        $data = $dt_creator->FromDataSource($qe);

        foreach ($data->GetColumns() as $column) {
            $column->SetEditable(false);
        }


        $model = $data;

        $datatable = new \UI\View\Html\Grid();
        $datatable->id = $_GET["page"] . "_history";
        $model->SetRows(null);
        $datatable->SetModel($model);



        ?>

        <script>
            $(function() {
                var table = $(`${window.elementID}_history`).datatable({
                    ajax: "api/Control/DataTable/Load",
                    dataSource: {
                        Query: <?php echo json_encode($qe); ?>,
                        Columns: <?php echo json_encode($datatable->GetColumns()); ?>
                    },
                    pagination: {
                        recordsPerPage: 50,
                        page: 1
                    },
                    enableContextMenu: false,
                    enableAdd: false,
                    enableDelete: false,
                    sort: [{
                        column: "id",
                        type: "DESC"
                    }]
                });

                var warehouseCode = "<?php echo $warehouse->code; ?>";
                var warehouseName = "<?php echo $warehouse->name; ?>";


                $("#Warehouse_getStatButton").on("click", function(e) {
                    e.preventDefault();

                    var href = new URL(window.location.href);
                    var id = href.searchParams.get("id");

                    if ($("#Warehouse_startDate").val().length == 0 || $("#Warehouse_endDate").val().length == 0) {
                        Application.UI.Dialog("Figyelem!", "Nincs megadva a kezdő és/vagy végdátum.", 1, {});
                        return;
                    }

                    $.post("api/Control/CityMedia/Warehouse/GetChartData", {
                        warehouseId: id,
                        startDate: $("#Warehouse_startDate").val(),
                        endDate: $("#Warehouse_endDate").val()
                    }, function(data) {
                        console.log(data);

                        if (window.chart != null) {
                            window.chart.destroy();
                        }
                        //$("#canvas-wrapper").html("").html('<canvas id="barchart" style="position: relative; height:40vh; width:80vw"></canvas>');
                        var context = $("#barchart").get(0).getContext("2d");
                        window.chart = new Chart(context, {
                            type: 'bar',
                            data: data,
                            options: {
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                },
                                parsing: {
                                    xAxisKey: 'x',
                                },
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: `Érme értékesítés havi bontásban: ${warehouseCode} - ${warehouseName}`,
                                        padding: {
                                            top: 10,
                                            bottom: 20
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            title: function(context) {
                                                console.log(context);
                                                let label = context[0].label + ": " || "";

                                                if (context[0].parsed.y !== null) {
                                                    label += context[0].parsed.y.toString() + " db";
                                                }
                                                return "";
                                            },
                                            label: function(tooltipItem, data) {
                                                // return data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                                return tooltipItem.label + ": " + tooltipItem.formattedValue + " db";
                                            }
                                        }
                                    }
                                },
                                onClick: (e) => {
                                    const canvasPosition = Chart.helpers.getRelativePosition(e, chart);

                                    // Substitute the appropriate scale IDs
                                    const dataX = chart.scales.x.getValueForPixel(canvasPosition.x);
                                    const dataY = chart.scales.y.getValueForPixel(canvasPosition.y);

                                    console.log(dataX, dataY);
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: 'Hónap'
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: 'Eladott darabszám'
                                        }
                                    }
                                }

                            }
                        });


                    });

                });

                //ha kitölti a kezdő dátumot, alapértelmezetten beszúrunk a végdátumhoz +1 évvel növelve
                $("#Warehouse_startDate").on("change", function(e) {

                    //ha illeszkedik a regexre az érték, megtörtént a dateChange operáció
                    var regEx = /^\d{4}-\d{2}-\d{2}$/;
                    if (this.value.match(regEx)) {
                        var d = new Date(this.value),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear() + 1;

                        if (month.length < 2)
                            month = '0' + month;
                        if (day.length < 2)
                            day = '0' + day;

                        var endDate = [year, month, day].join('-');

                        //$("#Warehouse_endDate").val(endDate);
                    }

                });

            });
        </script>

        <div class="container-fluid history">
            <div class="accordion" id="accordionExample">
                <div class="card">
                    <div class="card-header" id="headingProceeding">
                        <a class="d-block p-2" data-toggle="collapse" data-target="#proceeding" aria-expanded="false" aria-controls="collapseOne">
                            Jegyzőkönyv létrehozás
                        </a>
                    </div>

                    <div id="proceeding" class="collapse" aria-labelledby="headingProceeding" data-parent="#accordionExample">
                        <div class="card-body container-fluid">
                            <div class="row justify-content-center">
                                <div class="col-md-6 col-12">
                                    <script>
                                        $(function() {
                                            $(document).on("change", "#Proceeding_type", function() {
                                                switch ($(this).val()) {
                                                    case "2":
                                                        $("#createProceedingForm .finance").removeClass("d-none");
                                                        break;
                                                    default:
                                                        $("#createProceedingForm .finance").addClass("d-none");

                                                        break;
                                                }
                                            });

                                            $("#Proceeding_type").trigger("change");


                                            $(document).on("submit", "#createProceedingForm", async function(e) {
                                                e.preventDefault();

                                                let data = $(this).serializeObject();


                                                var d = new FormData();
                                                d.append("proceeding[type]", data.proceeding.type);
                                                d.append("proceeding[incoming]", data.proceeding.incoming);
                                                d.append("proceeding[outgoing]", data.proceeding.outgoing);
                                                d.append("proceeding[recipient]", data.proceeding.recipient);
                                                d.append("proceeding[comment]", data.proceeding.comment);
                                                d.append("proceeding[createDate]", data.proceeding.createDate);
                                                d.append("proceedingItem[insertedRows][0][warehouseId]", data.proceedingItem.insertedRows[0].warehouseId);
                                                d.append("proceedingItem[insertedRows][0][incoming]", data.proceeding.incoming);
                                                d.append("proceedingItem[insertedRows][0][outgoing]", data.proceeding.outgoing);

                                                var canvas = $("#Proceeding_sign")[0];
                                                if (!canvas.isBlank) {
                                                    var img = canvas.toDataURL("image/png");
                                                    data.proceeding.sign = img;

                                                    d.append("proceeding[sign]", data.proceeding.sign);
                                                }

                                                var canvas2 = $("#Proceeding_sign2")[0];
                                                console.log(canvas2.isBlank);
                                                if (!canvas2.isBlank) {
                                                    var img2 = canvas2.toDataURL("image/png");

                                                    data.proceeding.sign2 = img2;
                                                    d.append("proceeding[sign2]", data.proceeding.sign2);
                                                }

                                                for (i in $("#formFile")[0].files) {

                                                    d.append("proceedingFiles[]", $("#formFile")[0].files[i]);
                                                }

                                                var res = await fetch("/api/Control/CityMedia/Proceeding/Proceeding/AddFromMaintenance", {
                                                    method: "POST",

                                                    body: d
                                                });

                                                if (res.ok) {
                                                    res = await res.json();

                                                    alert("Jegyzőkönyv létrehozva " + res.documentNumber);

                                                    //let href = `printPageParams${encodeURIComponent('[namespace]')}=${encodeURIComponent('CityMedia\\Proceeding\\Proceeding\\')}&printPageParams${encodeURIComponent('[typeCode]')}=DEFAULT&templateParams${encodeURIComponent('[id]')}=${res.id}&options${encodeURIComponent('[inline]')}=1`;



                                                    //$("#PrintPageHeader_download").prop("href", "/api/Control/Prints/PrintPage/CreatePDF?" + (href)).removeClass("d-none");

                                                    let searchParams = new URLSearchParams();
                                                    searchParams.append(
                                                        'printPageParams[namespace]',
                                                        'CityMedia\\Proceeding\\Proceeding\\'
                                                    );

                                                    searchParams.append('printPageParams[typeCode]', 'DEFAULT');
                                                    searchParams.append('templateParams[id]', res.id);
                                                    searchParams.append('options[inline]', 1);
                                                    searchParams.append('options[preferCSSPageSize]', true);
                                                    window.open(
                                                        `/api/Control/Prints/PrintPage/CreatePDF?${searchParams.toString()}`
                                                    );




                                                } else {
                                                    res = await res.json();
                                                    UIkit.notification(res.error_description, {
                                                        status: "danger"
                                                    });
                                                }

                                            });

                                        });
                                    </script>
                                    <form id="createProceedingForm">

                                        <div class="form-group text-center">

                                            <label for="Proceeding_type">Jegyzőkönyv típusa</label>
                                            <?php

                                            $proceedingItem = new \UI\Html\InputHidden();
                                            $proceedingItem->name = "proceedingItem[insertedRows][][warehouseId]";
                                            $proceedingItem->SetModel($warehouse->id);

                                            echo $proceedingItem->Render();


                                            $type = new \UI\Html\Select();
                                            $type->name = "proceeding[type]";
                                            $type->id = "Proceeding_type";
                                            $type->SetValidValues(array_filter(Field_Valid_Values::GetValidValuesFromDataBaseToSelect("citymedia", "ct_proceeding", "type"), function ($o) {
                                                return $o["description"] != 'Panasz';
                                            }));
                                            $type->data["defaultvalue"] = "2";
                                            $type->class[] = "w-auto";
                                            $type->SetValue("2");
                                            $type->required = true;

                                            echo $type->Render();
                                            ?>
                                        </div>

                                        <div class="form-group text-center finance">

                                            <label for="Proceeding_incoming"><?php echo Translate::Get("income"); ?></label>


                                            <?php
                                            $incoming = new \UI\Html\InputNumber();
                                            $incoming->name = "proceeding[incoming]";
                                            $incoming->max = 9999999999999;
                                            $incoming->step = 0.1;
                                            $incoming->precision = 6;
                                            $incoming->class[] = "w-auto";
                                            $incoming->id = "Proceeding_incoming";
                                            $incoming->SetValue(0);
                                            echo $incoming->Render();
                                            ?>
                                            <?php
                                            try {

                                                $agreementItem = \DI\Model\Entity\CityMedia\Partner\Partner_Contract_Item::Get(["warehouseId" => $warehouse->id]);
                                                $agreement = Partner_Contract::Get(["id" => $agreementItem->documentId]);

                                                if ($agreementItem->settlementByDifference) {
                                            ?>
                                                    <label for="Proceeding_outgoing"><?php echo Translate::Get("expenditure"); ?></label>

                                                    <?php
                                                    $outgoing = new \UI\Html\InputNumber();
                                                    $outgoing->name = "proceeding[outgoing]";
                                                    $outgoing->max = 9999999999999;
                                                    $outgoing->step = 0.1;
                                                    $outgoing->precision = 2;
                                                    $outgoing->class[] = "w-auto";
                                                    $outgoing->id = "Proceeding_outgoing";
                                                    $outgoing->SetValue(0);
                                                    echo $outgoing->Render();
                                                    ?>
                                                <?php
                                                }
                                            } catch (\Throwable $th) {
                                                //throw new \Exception("Nincs szerződés!");
                                                ?><div class="uk-margin"><i class="fas fa-exclamation-triangle uk-text-danger fa-2x"></i>Nincs szerződés<br/></div><?php
                                                                                                                                }


                                                                                                                                    ?>


                                                <label for="Proceeding_outgoing">Feltöltött darabszám</label>
                                                <?php

                                                $proceedingItem = new \UI\Html\InputNumber();
                                                $proceedingItem->name = "proceedingItem[insertedRows][][uploadedQuantity]";
                                                $proceedingItem->precision = 0;
                                                $proceedingItem->SetModel(0);

                                                echo $proceedingItem->Render();

                                                ?>

                                        </div>
                                        <div class="form-group text-center">

                                            <label for="Proceeding_recipient">Átvevő</label>
                                            <?php
                                            $recipient = new \UI\Html\InputText();
                                            $recipient->name = "proceeding[recipient]";
                                            $recipient->id = "Proceeding_recipient";
                                            $recipient->maxlength = 100;
                                            echo $recipient->Render();
                                            ?>
                                        </div>
                                        <div class="form-group text-center">

                                            <label for="Proceeding_comment">Megjegyzés</label>
                                            <?php
                                            $comment = new \UI\Html\InputText();
                                            $comment->name = "proceeding[comment]";
                                            $comment->id = "Proceeding_comment";
                                            $comment->maxlength = 250;
                                            echo $comment->Render();
                                            ?>
                                        </div>
                                        <div class="form-group text-center">
                                            <label for="Proceeding_createDate">Létrehozás dátuma</label>

                                            <?php
                                            $createDate = new \UI\Html\InputDate();
                                            $createDate->name = "proceeding[createDate]";
                                            $createDate->id = "Proceeding_createDate";
                                            $createDate->SetModel(date("Y-m-d"));
                                            echo $createDate->Render();
                                            ?>
                                        </div>

                                        <div class="form-group text-center">

                                            <label for="Proceeding_sign">Aláírás (kiállító)</label>
                                            <?php
                                            $sign = new Signature();
                                            $sign->id = "Proceeding_sign";
                                            echo $sign->Render();
                                            ?>
                                        </div>

                                        <div class="form-group text-center">

                                            <label for="Proceeding_sign2">Aláírás (átvevő)</label>
                                            <?php
                                            $sign = new Signature();
                                            $sign->id = "Proceeding_sign2";
                                            echo $sign->Render();
                                            ?>
                                        </div>

                                        <div class="form-group text-center">

                                            <label for="formFile" class="form-label">Egyéb fájl, kép kiválasztás</label>
                                            <input class="form-control" type="file" accept="image/jpeg,image/png,application/pdf" multiple name="proceeding[file][]" id="formFile" />

                                        </div>

                                        <div class="form-group text-center">


                                            <?php
                                            $submit = new SubmitButton();
                                            $submit->id = "Proceeding_submit";
                                            $submit->innerHtml = "Létrehozás";
                                            echo $submit->Render();
                                            ?>



                                        </div>

                                        <!-- <div class="download text-center"> -->

                                        <!-- <a id="PrintPageHeader_download" class="link d-none" href="" name="" target="_blank" download title="Letöltés" style="">Letöltés <i class="icon-download-alt"></i></a> -->
                                        <!-- </div> -->

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingHistory">
                        <a class="d-block p-2" data-toggle="collapse" data-target="#history" aria-expanded="false" aria-controls="collapseOne">
                            Historikus adatok
                        </a>
                    </div>

                    <div id="history" class="collapse" aria-labelledby="headingHistory" data-parent="#accordionExample">
                        <div class="card-body">

                            <?php
                            echo $datatable->Render();
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header" id="headingStat">
                        <a class="d-block p-2" data-toggle="collapse" data-target="#stat" aria-expanded="false" aria-controls="collapseOne">
                            Egyéb statisztika
                        </a>
                    </div>

                    <div id="stat" class="collapse" aria-labelledby="headingStat" data-parent="#accordionExample">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-3">
                                    <dt><?php echo Translate::Get("start_date"); ?></dt>
                                    <dd>
                                        <?php
                                        $startDate = new InputDate();
                                        $startDate->name = "startDate";
                                        $startDate->id = "Warehouse_startDate";

                                        echo $startDate->Render();
                                        ?>
                                    </dd>
                                </div>
                                <div class="col-3">
                                    <dt><?php echo Translate::Get("end_date"); ?></dt>
                                    <dd>
                                        <?php
                                        $endDate = new InputDate();
                                        $endDate->name = "endDate";
                                        $endDate->id = "Warehouse_endDate";

                                        echo $endDate->Render();
                                        ?>
                                    </dd>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-2">
                                    <?php
                                    $button = new \UI\Html\Button();
                                    $button->id = "Warehouse_getStatButton";
                                    $button->type = "button";
                                    $button->innerHtml = "<i class='icon-bar-chart' aria-hidden='true'></i> Diagram létrehozása";


                                    echo $button->Render();
                                    ?>
                                </div>
                            </div>

                            <div class="row canvas-wrapper">
                                <canvas id="barchart" style="position: relative; height:40vh; width:80vw">

                                </canvas>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

        </div>


<?php

        $this->content = ob_get_contents();
        ob_end_clean();
    }

    /**
     * @inheritDoc
     */
    public function Render()
    {
        $this->CreateContent();
        return $this->content;
    }

    /**
     * @inheritDoc
     */
    public function SetModel($obj)
    {
        $this->model = $obj;
    }

    /**
     * @inheritDoc
     */
    public function &GetModel()
    {
        return $this->model;
    }
}
