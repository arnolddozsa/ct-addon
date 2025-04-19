<?php

namespace Control\CityMedia;


use Control\Application;
use DI\Model\Data\ChartDataSet;
use UI\Html\Chart;
use function PHPUnit\TestFixture\func;

/**
 * CityMedia raktár vezérlő osztály.
 */
class Warehouse{

    public static function GetChartData(int $warehouseId, $startDate, $endDate){
        $dao = Application::GetInstance()->GetSql();
        
        $months = array("Január", "Február", "Március", "Április", "Május", "Június", "Július", "Augusztus", "Szeptember", "Október", "November", "December");
        // $daily = (strtotime($endDate) - strtotime($startDate)) / 3600 / 24 <= date("t", strtotime($startDate)) - 1 ;
        $daily = date("Y-m", strtotime($startDate)) == date("Y-m", strtotime($endDate));
        if($daily){
            $query = "SELECT COUNT(T1.type) AS num, MONTH(T1.piLogCreateDate) AS gr, DAY(T1.piLogCreateDate) AS subGroup FROM ct_telemetry AS T1 
                    WHERE 
                    T1.warehouseId = :warehouseId AND 
                    T1.`type` = 'sales' AND 
                    (DATE(T1.piLogCreateDate) >= :startDate AND 
                    DATE(T1.piLogCreateDate) <= :endDate)
                    GROUP BY DAY(T1.piLogCreateDate)
                    ORDER BY T1.piLogCreateDate asc";

                    $labels = [];
                    $startNum = date("j", strtotime($startDate));
                    $endNum = date("j", strtotime($endDate));
                    // for($i=$startNum;$i<=$endNum;$i++){
                    $i = 0;
                    while(date('Y-m-d', strtotime($startDate . " +$i day")) < date("Y-m-d", strtotime($endDate . " +1 day"))){
                        $day = date('j', strtotime($startDate . " +$i day"));
                        $labels[] = $day;
                        $i++;
                    }
        } else {
            $query = "SELECT COUNT(T1.type) AS num, YEAR(T1.piLogCreateDate) AS gr, MONTH(T1.piLogCreateDate) AS subGroup FROM ct_telemetry AS T1 
            WHERE 
            T1.warehouseId = :warehouseId AND 
            T1.`type` = 'sales' AND 
            (DATE(T1.piLogCreateDate) >= :startDate AND 
            DATE(T1.piLogCreateDate) <= :endDate)
            GROUP BY MONTH(T1.piLogCreateDate)
            ORDER BY T1.piLogCreateDate asc";
            $labels = $months;
        }

      

        $params = array(":warehouseId" => $warehouseId, ":startDate"=> $startDate, ":endDate" => $endDate);
        $res = $dao->GetRows($query, $params);

        //hónapok címkéi, ezek kerülnek az x tengelyre
        

        $groupedData = array();
        $mlabels = [];

        if(count($res) > 0){
            //évenként csoportosítva hozunk létre adatszerkezeteket
            foreach ($res as $one){

                $group = $one["gr"];
                if($daily){
                    $group = $months[$one["gr"] - 1];

                } else {
                    // $group = $months[$one["subGroup"] - 1];
                }

                

                if(!in_array($group, array_keys($groupedData))){
                    $groupedData[$group] = array();
                    
                }
                
                $groupedData[$group][] = array("label" => $labels[$one["subGroup"] - 1], "value" => $one["num"]);

            }
        }

        $chartDataSetArray = array();

        if(count($groupedData) > 0){
            $i = 0;
            foreach ($groupedData as $key => $yearData){
                $chartDataSet = new ChartDataSet();
                $chartDataSet->label = $key;
                $chartDataSet->backgroundColor = self::DynamicColors();
                $chartDataSet->borderWidth = 1;

                $lData = array();
                $lLabels = array();

                if(count($yearData) > 0){
                    foreach ($yearData as $one){
                        $lLabels[] = $key . " " . $one["label"] . ".";
                        if($daily){
                            $lData[] = ["x" => intval($one["label"])-1, "y" => $one["value"]];
                        } else {
                            // $lLabels = $mlabels;
                            $lData[] = ["x" => $key, "y" => $one["value"],  "backgroundColor" => self::DynamicColors()];
                        }
                    }
                }

                $chartDataSet->data = $lData;
                $chartDataSet->labels = $lLabels;

                $chartDataSetArray[] = $chartDataSet;
                $i++;
            }
        }

        $data = array();

        $data["labels"] = array();
        $data["datasets"] = array();
        if(is_a($chartDataSetArray[0], "DI\Model\Data\ChartDataSet")) {

            foreach ($chartDataSetArray as $one) {
                $data["datasets"][] = $one->ToArray();

                foreach ($one->labels as $label) {
                    if (!in_array($label, $data["labels"])) {
                        $data["labels"][] = $label;
                    }
                }
                // $data["labels"][] = $one->label;

            }
        }


        return $data;
    }

    private static function DynamicColors(){
        $r = rand(0,255);
        $g = rand(0,255);
        $b = rand(0,255);
        return "rgb(" . $r . "," . $g . "," . $b . ")";
    }

}