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

        // Mendapatkan class
        foreach ($nilai_matriks as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($key2 == count($value) - 1){
                    array_push($class, $value2);
                    array_pop($nilai_matriks[$key]);
                }
            }
        }

        $count_class = (array_count_values($class));
        print_r($count_class);
        
        // Start: readable data
        $attribut_list = '';
        $baris_list = '';
        $nilai_list = '';
        $class_list = '';

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