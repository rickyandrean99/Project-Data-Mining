<?php
    // extract($_POST);

    if (isset($_POST["submit"])) {
        $storagename = "uploaded_file.csv";
        move_uploaded_file($_FILES["file"]["tmp_name"], "csv/" . $storagename);

        if(($open = fopen("csv/".$storagename, "r")) !== FALSE){
            while (($csv = fgetcsv($open, 1000, ",")) !== FALSE) {        
                $data[] = $csv; 
            }
        
            fclose($open);
        }
        
        $attribut = []; // index ke 0 jangan dipakai
        $baris = [];
        $nilai = [];

        foreach ($data as $index => $value) {
            if ($index == 0) {
                $modify = explode(";", $value[0]);
                
                foreach ($modify as $value) {
                    array_push($attribut, $value);
                }
            } else {
                $modify = explode(";", $value[0]);

                foreach ($modify as $index => $value) {
                    if ($index == 0) {
                        array_push($baris, $value);
                        continue;
                    }

                    array_push($nilai, $value);
                }

                $nilai_matriks = array_chunk($nilai, count($attribut)-1);
            }
        }

        var_dump($attribut);
        echo "<br><br>";
        var_dump($baris);
        echo "<br><br>";
        var_dump($nilai_matriks);
    }
?>