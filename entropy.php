<?php
    if (isset($_POST["submit"])) {
        $storagename = "uploaded_file_entropy.csv";
        move_uploaded_file($_FILES["file"]["tmp_name"], "csv/".$storagename);

        if (($open = fopen("csv/".$storagename, "r")) !== FALSE) {
            while (($csv = fgetcsv($open, 1000, ",")) !== FALSE) {        
                $data[] = $csv; 
            }
        
            fclose($open);
        }

        $attribut = [];
        $baris = [];
        $nilai = [];
        $class = [];

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

        // Mendapatkan class
        foreach ($nilai_matriks as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($key2 == count($value) - 1){
                    array_push($class, $value2);
                    array_pop($nilai_matriks[$key]);
                }
            }
        }

        // Kalkulasi nilai parent
        $parent = (array_count_values($class));
        $total_parent = array_sum($parent);
        $probability_parent = [];
        $entropy_parent = 0;

        foreach ($parent as $value) {
            array_push($probability_parent, ($value/$total_parent));
        }

        foreach ($probability_parent as $value) {
            $entropy_parent += $value * log($value, 2);
        }

        $entropy_parent = -$entropy_parent;
        
        // Pemetaan tabel per feature
        $value_group_list = [];
        for ($i = 0; $i < count($nilai_matriks[0]); $i++) {
            $column_group = array_column($nilai_matriks, $i);
            array_push($value_group_list, array_count_values($column_group));
        }

        $feat_data = [];
        foreach ($attribut as $key => $value) {
            // ambil key sebagai patokan 0, 1, 2, ... unuk menentukan feat mana yang diproses

            $feat_row = [];
            foreach ($parent as $key2 => $value2) {
                // ambil key 2 sebagai patokan seperti C0 atau C1 yang merupakan row dari tabel yang akan dibuat

                $feat_column = [];
                foreach ($value_group_list[$key] as $key3 => $value3) {
                    // ambil key 3 sebagai daftar patokan seperti Ya, Tidak, dll yang merupakan column dari tabel yang akan dibuat

                    $amount = 0;
                    foreach (array_column($nilai_matriks, $key) as $key4 => $value4) {
                        // Looping array nilai matriks pada kolom ke $key, lakukan pengecekan sebagai berikut dimana $value4(nilai pada cell dataset) harus sesuai dengan nilai $key3(daftar data categorical seperti ya, tidak) dan juga class pada row tersebut ($class[$key4]) harus sama dengan row pada tabel yang dibuat sekarang yaitu $key2
                        if ($value4 == $key3 && $class[$key4] == $key2) {
                            $amount++;
                        }
                    }
                    
                    $feat_column[$key3] = $amount;
                }
                
                $feat_row[$key2] = $feat_column;
            }

            array_push($feat_data, $feat_row);
        }
        
        foreach ($attribut as $key => $value) {
            echo "<table style='border: 1px solid black; border-collapse: collapse'>";
                echo "<thead>";
                    echo "<tr>";
                        echo "<th style='padding: 5px 25px; border: 1px solid black;'></th>";

                        foreach ($value_group_list[$key] as $key2 => $value2) {
                            echo "<th style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$key2."</th>";
                        }
                        
                        echo "<th style='padding: 5px 25px; background: black'></th>";
                    echo "</tr>";
                echo "</thead>";

                echo "<tbody>";
                    foreach ($feat_data[$key] as $key3 => $value3) {
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$key3."</td>";

                            foreach ($value3 as $key4 => $value4) {
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$value4."</td>";
                            }

                            echo "<td style='padding: 5px 25px; background: black'></td>";
                        echo "</tr>";
                    }

                    echo "<tr>";
                        echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center; font-weight: bold'>Total</td>";

                        $total = 0;
                        foreach ($value_group_list[$key] as $key5 => $value5) {
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$value5."</td>";
                            $total += $value5;
                        }
                        
                        echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$total."</td>";
                    echo "</tr>";
                echo "</tbody>";
            echo "</table>";

            echo "<br><br>";
        }

        // entropy per group
        // M atau weight gabungan dari entropy per group
        // Gain
        // print_r($feat_data);
        $prob_feat = [];
        foreach ($attribut as $key => $value) {
            $prob_row = [];

            foreach ($feat_data[$key] as $key2 => $value2) {
                $prob_column = [];

                foreach ($value2 as $key3 => $value3) {
                    $prob_column[$key3] = $value3 / $value_group_list[$key][$key3];
                }

                $prob_row[$key2] = $prob_column;
            }

            $prob_feat[$key] = $prob_row;
        }
        
        // foreach ($prob_feat as $key => $value) {
        //     foreach ($value as $key2 => $value2) {
        //         print_r($value2);
        //         echo "<br>";
        //     }
        //     echo "<br>";
        // }
        // die();

        
        // die();

        $entropy_feat = [];
        foreach ($prob_feat as $key => $value) {
            if ($key == 0) continue; // ini hapus
            $entropy_list = [];
            foreach ($value_group_list[$key] as $key2 => $value2) {
                $entropy = 0;

                // if ($key2 == 0) continue; // ini hapus
                print_r(array_column($value, $key2));
                // die();

                // foreach (array_column($value, $key2) as $key3 => $value3) {
                //     $entropy += $value3 * log($value3, 2);
                // }
                // echo -$entropy."<br>";
                // $entropy_list[$key2] = -$entropy;
            }

            die(); // ini hapus

            echo "<br>";
            $entropy_feat[$key] = $entropy_list;
        }
        // print_r($entropy_feat);
        die();
        
        // foreach ($value_group_list as $key => $value) {
        //     print_r($value);
        //     echo "<br>";
        // }
        // die();












        // Start: readable data
        $attribut_list = '';
        $baris_list = '';
        $nilai_list = '';
        $class_list = '';

        foreach ($attribut as $key => $value) {
            $attribut_list .= $value;
            if ($key != count($attribut) - 1) $attribut_list .= ", ";
        }

        foreach ($baris as $key => $value) {
            $baris_list .= $value;
            if ($key != count($baris) - 1) $baris_list .= ", ";
        }
        
        foreach ($nilai_matriks as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $nilai_list .= $value2;
                if ($key2 != count($value) - 1) $nilai_list .= ", ";
            }
            $nilai_list .= "<br>";
        }

        foreach ($class as $key => $value) {
            $class_list .= $value;
            if ($key != count($class) - 1) $class_list .= ", ";
        }

        echo "<div style='font-size: 16px'>";
            echo "<p><b>Attribut list:</b><br>".$attribut_list."</p>";
            echo "<p><b>Baris list:</b><br>".$baris_list."</p>";
            echo "<p><b>Nilai list:</b><br>".$nilai_list."</p>";
            echo "<p><b>Class List:</b><br>".$class_list."</p>";
        echo "</div>";
        // End: readable data
    }
?>