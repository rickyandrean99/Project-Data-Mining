<?php
    // extract($_POST);

    if (isset($_POST["submit"])) {
        $storagename = "uploaded_file.csv";
        move_uploaded_file($_FILES["file"]["tmp_name"], "csv/".$storagename);

        if (($open = fopen("csv/".$storagename, "r")) !== FALSE){
            while (($csv = fgetcsv($open, 1000, ",")) !== FALSE) {        
                $data[] = $csv; 
            }
        
            fclose($open);
        }
        
        $attribut = []; // index ke 0 jangan dipakai
        $baris = [];
        $nilai = [];

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

                $nilai_matriks = array_chunk($nilai, count($attribut)-1);
            }
        }
        
        // Start: readable data
        $attribut_list = '';
        $baris_list = '';
        $nilai_list = '';

        foreach ($attribut as $key => $value) {
            if ($key == 0) continue;
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

        echo "<div style='font-size: 16px'>";
            echo "<p><b>Attribut list:</b><br>".$attribut_list."</p>";
            echo "<p><b>Baris list:</b><br>".$baris_list."</p>";
            echo "<p><b>Nilai list:</b><br>".$nilai_list."</p>";
        echo "</div>";
        // End: readable data

        echo "<div style='display: flex; flex-direction: row;'>";
        
        echo "<div style='width: 33%'>";
        // Manhattan Distance
        foreach ($nilai_matriks as $key1 => $value1) {
            foreach ($nilai_matriks as $key2 => $value2) {
                if ($key1 == $key2) continue;

                $manhattan = 0;
                foreach ($value2 as $key3 => $value3) {
                    $manhattan += abs($value1[$key3] - $value3);
                }

                echo "Manhattan(".$baris[$key1].", ".$baris[$key2].") = ".$manhattan."<br>";
            }
            echo "<br>";
        }
        echo "</div>";

        echo "<div style='width: 33%'>";
        // Euclidean Distance
        foreach ($nilai_matriks as $key1 => $value1) {
            foreach ($nilai_matriks as $key2 => $value2) {
                if ($key1 == $key2) continue;

                $euclidean = 0;
                foreach ($value2 as $key3 => $value3) {
                    $euclidean += pow(($value1[$key3] - $value3), 2);
                }

                echo "Euclidean(".$baris[$key1].", ".$baris[$key2].") = ".round(sqrt($euclidean), 2)."<br>";
            }
            echo "<br>";
        }
        echo "</div>";

        echo "<div style='width: 33%'>";
        // Supremum Distance
        foreach ($nilai_matriks as $key1 => $value1) {
            foreach ($nilai_matriks as $key2 => $value2) {
                if ($key1 == $key2) continue;

                $supremum = 0;
                foreach ($value2 as $key3 => $value3) {
                    if (abs($value1[$key3] - $value3) > $supremum) {
                        $supremum = abs(($value1[$key3] - $value3));
                    }
                }

                echo "Supremum(".$baris[$key1].", ".$baris[$key2].") = ".$supremum."<br>";
            }
            echo "<br>";
        }
        echo "</div>";

        echo "</div>";
    }
?>