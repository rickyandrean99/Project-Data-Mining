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
        
        // ========================================================================= START CATEGORICAL =====================================================================
        $value_group_list = [];
        for ($i = 0; $i < count($nilai_matriks[0]); $i++) {
            $column_group = array_column($nilai_matriks, $i);
            array_push($value_group_list, array_count_values($column_group));
        }

        // Menghitung feat di tiap feature
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
                        
                        // jika datanya numerik tidak perlu diproses karena prosesnya akan beda 
                        if (is_numeric($value4)) break 3;
                        
                        if ($value4 == $key3 && $class[$key4] == $key2) {
                            $amount++;
                        }
                    }
                    
                    $feat_column[$key3] = $amount;
                }
                
                $feat_row[$key2] = $feat_column;
            }
            
            if (count($feat_row) > 0) $feat_data[$key] = $feat_row;
        }
        
        // Menentukan probability di tiap feature
        $prob_feat = [];
        foreach ($attribut as $key => $value) {
            if (!isset($feat_data[$key])) continue;
            
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

        // Menentukan entropy di tiap feature
        $entropy_feat = [];
        foreach ($prob_feat as $key => $value) {
            $entropy_list = [];

            foreach ($value_group_list[$key] as $key2 => $value2) {
                $entropy = 0;

                foreach (array_column($value, $key2) as $key3 => $value3) {
                    if ($value3 > 0) {
                        $entropy += $value3 * log($value3, 2);
                    }
                }

                $entropy_list[$key2] = -$entropy;
            }

            $entropy_feat[$key] = $entropy_list;
        }

        // Menentukan weight di tiap feature
        $weight_list = [];
        foreach ($entropy_feat as $key => $value) {
            $weight = 0;
            foreach ($value as $key2 => $value2) {
                $weight += ($value_group_list[$key][$key2] / array_sum($value_group_list[$key])) * $value2;
            }
            
            $weight_list[$key] = $weight;
        }

        // Menentukan gain di tiap feature
        $gain_list = [];
        foreach ($weight_list as $key => $value) {
            $gain_list[$key] = $entropy_parent - $value;
        }
        arsort($gain_list);
        // ========================================================================= END CATEGORICAL =====================================================================

        // ========================================================================= START KONTINU =====================================================================
        // Mendapatkan data kontinu
        $data_kontinu = [];
        foreach ($attribut as $key => $value) {
            if (!(is_numeric($nilai_matriks[0][$key]))) continue;
            $data_kontinu[$key] = array_column($nilai_matriks, $key);
        }

        // Mengatasi duplikasi nilai
        $data_kontinu_unique = [];
        foreach ($data_kontinu as $key => $value) {
            asort($value);
            $data_kontinu_unique[$key] = array_unique($value);
        }

        // Pemetaan angka baru berdasarkan data kontinu yang sudah diurutkan
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

        // Menghitung feat untuk data kontinu di tiap feature
        $kontinu_feat_data = [];
        foreach($new_data_kontinu as $key => $value) {
            foreach ($value as $key4 => $value4) {
                $kontinu_feat_group = [];

                foreach ($parent as $key2 => $value2) {
                    $kontinu_feat_row = [];
                    $less = 0;
                    $more = 0;

                    foreach (array_column($nilai_matriks, $key) as $key3 => $value3) {
                        if ($value3 <= $value4 && $class[$key3] == $key2) $less++;
                        if ($value3 > $value4 && $class[$key3] == $key2) $more++;
                    }

                    $kontinu_feat_row["less"] = $less;
                    $kontinu_feat_row["more"] = $more;
                    $kontinu_feat_group[$key2] = $kontinu_feat_row;
                }

                $kontinu_feat_data[$key][$key4] = $kontinu_feat_group;
            }
        }

        // Menghitung akumulasi less atau more untuk data kontinu di tiap feature
        $total_feat_data = [];
        foreach ($kontinu_feat_data as $key => $value) {
            $total_data = [];

            foreach ($value as $key2 => $value2) {
                $total_data[$key2]["less"] = array_sum(array_column($value2, "less"));
                $total_data[$key2]["more"] = array_sum(array_column($value2, "more"));
            }
            
            $total_feat_data[$key] = $total_data;
        }

        // Menentukan probability untuk data kontinu di tiap feature
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

        // Menentukan entropy untuk data kontinu di tiap feature
        $kontinu_entropy_feat = [];
        foreach ($kontinu_prob_feat as $key => $value) {
            $entropy_list = [];
            
            foreach ($value as $key2 => $value2) {
                $less = array_column($value2, "less");
                $more = array_column($value2, "more");

                $entropy_less = 0;
                $entropy_more = 0;
                
                foreach ($less as $key3 => $value3) {
                    if ($value3 > 0) {
                        $entropy_less += $value3 * log($value3, 2);
                    }
                }

                foreach ($more as $key4 => $value4) {
                    if ($value4 > 0) {
                        $entropy_more += $value4 * log($value4, 2);
                    }
                }

                $entropy_list[$key2]["less"] = (-$entropy_less == -0)? 0 : -$entropy_less;
                $entropy_list[$key2]["more"] = (-$entropy_more == -0)? 0 : -$entropy_more;
            }
            
            $kontinu_entropy_feat[$key] = $entropy_list;
        }

        // Menentukan weight untuk data kontinu di tiap feature
        $kontinu_weight_list = [];
        foreach ($kontinu_entropy_feat as $key => $value) {
            $kontinu_weight_data = [];
            
            foreach ($value as $key2 => $value2) {
                $weight = 0;
                $less_amount = $total_feat_data[$key][$key2]["less"];
                $more_amount = $total_feat_data[$key][$key2]["more"];
                $amount = array_sum($total_feat_data[$key][$key2]);

                $weight += ($value2["less"] * $less_amount / $amount);
                $weight += ($value2["more"] * $more_amount / $amount);

                $kontinu_weight_data[$key2] = $weight;
            }

            $kontinu_weight_list[$key] = $kontinu_weight_data;
        }

        // Menentukan gain untuk data kontinu di sebuah nilai baru pada feature
        $kontinu_gain_list = [];
        foreach ($kontinu_weight_list as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $kontinu_gain_list[$key][$key2] = $entropy_parent - $value2;
            }
        }

        // Menentukan gain untuk data kontinu di sebuah feature
        $max_kontinu_gain_list = [];
        foreach ($kontinu_gain_list as $key => $value) {
            arsort($value);
            $max_kontinu_gain_list[$key] = $value[key($value)];
        }
        // ========================================================================= END KONTINU =====================================================================
        
        // Sorting categorical and kontinu gain
        $categorical_kontinu_gain_list = [];
        foreach ($gain_list as $key => $value) $categorical_kontinu_gain_list[$key] = $value;
        foreach ($max_kontinu_gain_list as $key => $value) $categorical_kontinu_gain_list[$key] = $value;
        arsort($categorical_kontinu_gain_list);
        
        echo "<div style='display: flex; flex-direction: row; width: 100%; flex-wrap: wrap'>";        
        // Tabel untuk data categorical
        foreach ($attribut as $key => $value) {
            if (!isset($feat_data[$key])) continue;

            echo "<div style='margin-right: 40px; margin-bottom: 20px'>";
                echo "<table style='border: 1px solid black; border-collapse: collapse'>";
                    echo "<caption>".$attribut[$key]."</caption>";
                    echo "<thead>";
                        echo "<tr>";
                            echo "<th style='padding: 5px 25px; border: 1px solid black;'></th>";
                            foreach ($value_group_list[$key] as $key2 => $value2) {
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
                            foreach ($value_group_list[$key] as $key5 => $value5) {
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".$value5."</td>";
                            }
                        echo "</tr>";

                        foreach ($prob_feat[$key] as $key3 => $value3) {
                            echo "<tr>";
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>P(".$key3.")</td>";
                                foreach ($value3 as $key4 => $value4) {
                                    echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".round($value4, 3)."</td>";
                                }
                            echo "</tr>";
                        }

                        // Entropy
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>Entropy</td>";
                            foreach ($entropy_feat[$key] as $key5 => $value5) {
                                echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".round($value5, 3)."</td>";
                            }
                        echo "</tr>";

                        // Weight
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>Weight</td>";
                            echo "<td colspan='".count($value_group_list[$key])."'style='padding: 5px 25px; border: 1px solid black; text-align: center;'>".round($weight_list[$key], 3)."</td>";
                        echo "</tr>";

                        // Gain
                        echo "<tr>";
                            echo "<td style='padding: 5px 25px; border: 1px solid black; text-align: center;'>Gain</td>";
                            echo "<td colspan='".count($value_group_list[$key])."'style='padding: 5px 25px; border: 1px solid black; text-align: center;'><b>".round($gain_list[$key], 3)."</b></td>";
                        echo "</tr>";
                    echo "</tbody>";
                echo "</table>";
            echo "</div>";
        }

        // Tabel untuk data kontinu
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

                        foreach ($parent as $key2 => $value4) {
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

                        foreach ($parent as $key2 => $value2) {
                            echo "<tr>";
                                echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>P(".$key2.")</td>";

                                foreach ($kontinu_prob_feat[$key] as $key3 => $value3) {
                                    foreach ($value3[$key2] as $key4 => $value4) {
                                        echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>".round($value4, 3)."</td>";
                                    }
                                }
                            echo "</tr>";
                        }

                        // Entropy
                        echo "<tr>";
                            echo "<td style='padding: 5px 15px; border: 1px solid black; text-align: center;'>Entropy</td>";

                            foreach ($kontinu_entropy_feat[$key] as $key2 => $value2) {
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

        // Hasil
        echo "<div style='font-size: 20px; margin-top: 20px'>Best split adalah attribut <b>".$attribut[key($categorical_kontinu_gain_list)]."</b> karena memiliki gain terbesar yaitu ".round($categorical_kontinu_gain_list[key($categorical_kontinu_gain_list)], 3)."</div>";
    } else {
        header("location: index.php");
    }
?>