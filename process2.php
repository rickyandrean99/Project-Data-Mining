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
        $attribut_list = '';
        $baris_list = '';
        $nilai_list = '';

        $class = [];

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

    }
?>