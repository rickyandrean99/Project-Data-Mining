<?php 
    if (isset($_POST["submit"])) {
        $storagename = "uploaded_file_gini.csv";
        move_uploaded_file($_FILES["file"]["tmp_name"], "csv/".$storagename);

        if (($open = fopen("csv/".$storagename, "r")) !== FALSE){
            while (($csv = fgetcsv($open, 1000, ",")) !== FALSE) {        
                $data[] = $csv; 
            }
        
            fclose($open);
        }
        
        $attribut = []; // index ke 0 jangan dipakai
        $baris = [];
        $nilai = array();

        foreach ($data as $key => $value) {
            if ($key == 0) {
                // Mendapatkan attribut dari dataset
                $modify = explode(";", $value[0]);
                foreach ($modify as $value) {
                    array_push($attribut, $value);
                }
            } else {
                // Mendapatkan nama baris dan juga datanya dari dataset
                $modify = explode(";", $value[0]);
                foreach ($modify as $key => $value) {
                    if ($key == 0) {
                        array_push($baris, $value);
                        continue;
                    }

                    array_push($nilai, $value);
                }

            }
        }
        $nilai_matriks = array_chunk($nilai, count($attribut)-1);
        array_shift($attribut);
        array_pop($attribut);

        $classList = [];
        $class = [];
        $classValue=[];
        $giniParrent = 1;
        foreach ($nilai_matriks as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if($key2 == count($value)-1){
                    array_push($classList,$value2);
                    array_push($classValue,array_pop($nilai_matriks[$key]));
                }
            }
        }
        $class=(array_count_values($classList));

        foreach ($class as $key => $value){
            $prob = pow($value/array_sum($class),2);
            $giniParrent -= $prob;
        }

        
        $groupList = [];
        for ($i = 0; $i < count($nilai_matriks[0]); $i++) {
            $column_group = array_column($nilai_matriks, $i);
            array_push($groupList, array_count_values($column_group));
        }

        $feat_data = [];
        foreach ($attribut as $key=>$value){
            $feat_row = [];
            foreach($class as $key2 => $value2){
                $feat_column = [];
                foreach($groupList[$key] as $key3 => $value3){
                    $amount = 0;
                    foreach(array_column($nilai_matriks, $key) as $key4 => $value4){
                        if ($value4 == $key3 && $classList[$key4] == $key2) {
                            $amount++;
                        }
                    }
                    $feat_column[$key3] = $amount;
                }
                $feat_row[$key2] = $feat_column;
            }
            array_push($feat_data, $feat_row);
        }

        $prob= [];
       
        foreach ($attribut as $key => $value){
            $row=[];
            foreach ($feat_data[$key] as $key2 => $value2){
                $column=[];
                $temp = 0;
                foreach ($value2 as $key3 =>$value3){
                    $column[$key3] = $value3 / $groupList[$key][$key3];
                    
                }
                $row[$key2] = $column;
               
            }
            $prob[$key] = $row;
        }
        
        $gini = [];
        foreach($prob as $key => $value){
            $giniList = [];
            foreach ($groupList[$key] as $key2 => $value2){
                $temp = 1;
                foreach(array_column($value,$key2) as $key3 =>$value3){
                    $temp-= pow($value3,2);
                }
                $giniList[$key2] = $temp;
            }
            $gini[$key] = $giniList;
        }

        $weight=[];
        foreach($gini as $key => $value){
            $temp=0;
            foreach($value as $key2=>$value2){
                 $temp += ($groupList[$key][$key2]/array_sum($groupList[$key])*$value2);
            }
             $weight[$key]= $temp;
        }
        $gain=[];
        foreach($weight as $key=> $value){
            $gain[$key]= $giniParrent-$value;
        }
        arsort($gain);

        //kontinu
        $data_kontinu = [];
        foreach($attribut as $key => $value){
            if (!(is_numeric($nilai_matriks[0][$key]))) continue;
            $data_kontinu[$key] = array_column($nilai_matriks, $key);
        }

        $data_kontinu_unique = [];
        foreach ($data_kontinu as $key => $value) {
            asort($value);
            $data_kontinu_unique[$key] = array_unique($value);
        }

        $new_data_kontinu = [];

        foreach ($data_kontinu_unique as $key => $value) {
            $new = [];
            array_push($new, reset($value)-1);
            
            foreach ($value as $key2 => $value2) {
                foreach ($value as $key3 => $value3) {
                    if ($value3 > $value2) {
                        array_push($new, (($value2 + $value3)/2));
                        break;
                    }
                }
            }
            
            array_push($new, end($value)+1);
            $new_data_kontinu[$key] = $new;
        }

        $kontinu_feat_data = [];
        foreach($new_data_kontinu as $key => $value) {
            foreach ($value as $key4 => $value4) {
                $kontinu_feat_group = [];

                foreach ($class as $key2 => $value2) {
                    $kontinu_feat_row = [];
                    $less = 0;
                    $more = 0;

                    foreach (array_column($nilai_matriks, $key) as $key3 => $value3) {
                        if ($value3 <= $value4 && $classList[$key3] == $key2) $less++;
                        if ($value3 > $value4 && $classList[$key3] == $key2) $more++;
                    }

                    $kontinu_feat_row["less"] = $less;
                    $kontinu_feat_row["more"] = $more;
                    $kontinu_feat_group[$key2] = $kontinu_feat_row;
                }

                $kontinu_feat_data[$key][$key4] = $kontinu_feat_group;
            }
        }

        $total_feat_data = [];
        foreach ($kontinu_feat_data as $key => $value) {
            $total_data = [];

            foreach ($value as $key2 => $value2) {
                $total_data[$key2]["less"] = array_sum(array_column($value2, "less"));
                $total_data[$key2]["more"] = array_sum(array_column($value2, "more"));
            }
            
            $total_feat_data[$key] = $total_data;
        }

        $kontinu_prob_feat = [];
        foreach ($attribut as $key => $value) {
            if (!isset($kontinu_feat_data[$key])) continue;
            
            $kontinu_data = [];
            foreach ($kontinu_feat_data[$key] as $key2 => $value2) {
                $kontinu_prob_row = [];
                
                foreach ($value2 as $key3 => $value3) {
                    $kontinu_prob_column = [];
                    
                    foreach ($value3 as $key4 => $value4) {
                        $total = $total_feat_data[$key][$key2][$key4];
                        if ($total == 0) {
                            $kontinu_prob_column[$key4] = 0;
                        } else {
                            $kontinu_prob_column[$key4] = $value4 / $total;
                        }
                    }
                    
                    $kontinu_prob_row[$key3] = $kontinu_prob_column;
                }
                
                $kontinu_data[$key2] = $kontinu_prob_row;
            }

            $kontinu_prob_feat[$key] = $kontinu_data;
        }

        $kontinu_gini_feat = [];
        foreach ($kontinu_prob_feat as $key => $value) {
            $entropy_list = [];
            
            foreach ($value as $key2 => $value2) {
                $less = array_column($value2, "less");
                $more = array_column($value2, "more");

                $entropy_less = 1;
                $entropy_more = 1;
                
                foreach ($less as $key3 => $value3) {
                    if ($value3 > 0) {
                        $entropy_less -= pow($value3,2);
                    }
                }

                foreach ($more as $key4 => $value4) {
                    if ($value4 > 0) {
                        $entropy_more -= pow($value4,2);
                    }
                }

                $entropy_list[$key2]["less"] = $entropy_less;
                $entropy_list[$key2]["more"] = $entropy_more;
            }
            
            $kontinu_gini_feat[$key] = $entropy_list;
        }

        $kontinu_weight_list = [];
        foreach ($kontinu_gini_feat as $key => $value) {
            $kontinu_weight_data = [];
            
            foreach ($value as $key2 => $value2) {
                $weightTemp = 0;
                $less_amount = $total_feat_data[$key][$key2]["less"];
                $more_amount = $total_feat_data[$key][$key2]["more"];
                $amount = array_sum($total_feat_data[$key][$key2]);

                $weightTemp += ($value2["less"] * $less_amount / $amount);
                $weightTemp += ($value2["more"] * $more_amount / $amount);

                $kontinu_weight_data[$key2] = $weightTemp;
            }

            $kontinu_weight_list[$key] = $kontinu_weight_data;
        }

        $kontinu_gain_list = [];
        foreach ($kontinu_weight_list as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $kontinu_gain_list[$key][$key2] = $giniParrent - $value2;
            }
        }

        $max_kontinu_gain_list = [];
        foreach ($kontinu_gain_list as $key => $value) {
            arsort($value);
            $max_kontinu_gain_list[$key] = $value[key($value)];
        }

        $categorical_kontinu_gain_list = [];
        foreach ($gain as $key => $value) $categorical_kontinu_gain_list[$key] = $value;
        foreach ($max_kontinu_gain_list as $key => $value) $categorical_kontinu_gain_list[$key] = $value;
        arsort($categorical_kontinu_gain_list);

        echo "<div style='display: flex; flex-direction: row; width: 100%; flex-wrap: wrap'>";

        foreach ($attribut as $key => $value) {
            if (!isset($feat_data[$key])) continue;

            echo "<div style='margin-right: 40px; margin-bottom: 20px'>";
                echo "<table style='border: 1px solid black; border-collapse: collapse'>";
                    echo "<caption>".$attribut[$key]."</caption>";
                    echo "<thead>";
                        echo "<tr>";
                            echo "<th style='padding: 5px 25px; border: 1px solid black;'></th>";
                            foreach ($groupList[$key] as $key2 => $value2) {
                                echo "<th style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$key2."</th>";
                            }
                        echo "</tr>";
                    echo "</thead>";

                    echo "<tbody>";
                        foreach ($feat_data[$key] as $key3 => $value3) {
                            echo "<tr>";
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$key3."</td>";
                                foreach ($value3 as $key4 => $value4) {
                                    echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$value4."</td>";
                                }
                            echo "</tr>";
                        }

                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center; font-weight: bold'>Total</td>";
                            foreach ($groupList[$key] as $key5 => $value5) {
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$value5."</td>";
                            }
                        echo "</tr>";

                        foreach ($prob[$key] as $key3 => $value3) {
                            echo "<tr>";
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>P(".$key3.")</td>";
                                foreach ($value3 as $key4 => $value4) {
                                    echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".round($value4, 3)."</td>";
                                }
                            echo "</tr>";
                        }

                        // Gini
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>Gini</td>";
                            foreach ($gini[$key] as $key5 => $value5) {
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".round($value5, 3)."</td>";
                            }
                        echo "</tr>";

                        // Weight
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>Weight</td>";
                            echo "<td colspan='".count($groupList[$key])."'style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".round($weight[$key], 3)."</td>";
                        echo "</tr>";

                        // Gain
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>Gain</td>";
                            echo "<td colspan='".count($groupList[$key])."'style='padding: 5px 25px; border: 1px solid black; text-align: center;'><b>".round($gain[$key], 3)."</b></td>";
                        echo "</tr>";
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        }

        foreach ($data_kontinu_unique as $key => $value) {
            echo "<div style='margin-right: 40px; margin-bottom: 20px'>";
                echo "<table style='border: 1px solid black; border-collapse: collapse'>";
                    echo "<caption>".$attribut[$key]."</caption>";
                    echo "<thead>";
                        // Data lama
                        echo "<tr>";
                            echo "<th style='padding: 5px 15px; border: 1px solid black; background: black'></th>";
                            echo "<th style='padding: 5px 15px; border: 1px solid black; background: black'></th>";
                            foreach ($value as $key2 => $value2) {
                                echo "<th colspan='2' style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".$value2."</th>";
                            }
                            echo "<th style='padding: 5px 15px; border: 1px solid black; background: black'></th>";
                        echo "</tr>";

                        // Data baru
                        echo "<tr>";
                            echo "<th style='padding: 5px 15px; border: 1px solid black; background: black '></th>";
                            foreach ($new_data_kontinu[$key] as $key2 => $value2) {
                                echo "<th colspan='2' style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".$value2."</th>";
                            }
                        echo "</tr>";
                    echo "</thead>";

                    echo "<tbody>";
                        echo "<tr>";
                            echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center; background: black'></td>";
                            
                            for ($i = 0; $i < count($kontinu_feat_data[$key]); $i++) {
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'><=</td>";
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>></td>";
                            }
                        echo "</tr>";

                        foreach ($class as $key2 => $value4) {
                            echo "<tr>";
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".$key2."</td>";

                                foreach ($kontinu_feat_data[$key] as $key3 => $value3) {
                                    foreach ($value3[$key2] as $key4 => $value4) {
                                        echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".$value4."</td>";
                                    }
                                }
                            echo "</tr>";
                        }

                        echo "<tr>";
                            echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center; font-weight: bold'>Total</td>";

                            foreach ($total_feat_data[$key] as $key2 => $value2) {
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".$value2["less"]."</td>";
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".$value2["more"]."</td>";
                            }
                        echo "</tr>";

                        foreach ($class as $key2 => $value2) {
                            echo "<tr>";
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>P(".$key2.")</td>";

                                foreach ($kontinu_prob_feat[$key] as $key3 => $value3) {
                                    foreach ($value3[$key2] as $key4 => $value4) {
                                        echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".round($value4, 3)."</td>";
                                    }
                                }
                            echo "</tr>";
                        }

                        // Gini
                        echo "<tr>";
                            echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>Gini</td>";

                            foreach ($kontinu_gini_feat[$key] as $key2 => $value2) {
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".round($value2["less"], 3)."</td>";
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".round($value2["more"], 3)."</td>";
                            }
                        echo "</tr>";

                        // Weight
                        echo "<tr>";
                            echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>Weight</td>";

                            foreach ($kontinu_weight_list[$key] as $key2 => $value2) {
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;' colspan='2'>".round($value2, 3)."</td>";
                            }
                        echo "</tr>";
                        
                        // Gain
                        echo "<tr>";
                            echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>Gain</td>";

                            foreach ($kontinu_gain_list[$key] as $key2 => $value2) {
                                if ($max_kontinu_gain_list[$key] == $value2) {
                                    echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center; font-weight: bold' colspan='2'>".round($value2, 3)."</td>";
                                } else {
                                    echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;' colspan='2'>".round($value2, 3)."</td>";
                                }
                            }
                        echo "</tr>";
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        }
        echo "</div>";

        echo "<div style='font-size: 22px; margin-top: 30px; margin-bottom: 50px; text-align: center'>Best split adalah attribut <b>".$attribut[key($categorical_kontinu_gain_list)]."</b> karena memiliki gain terbesar yaitu ".round($categorical_kontinu_gain_list[key($categorical_kontinu_gain_list)], 3)."</div>";
    } else {
        header("location: index.php");
    }

?>