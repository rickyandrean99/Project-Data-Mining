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
        
        $attribut = [];
        $baris = [];
        $nilai = [];
        $manhattan_list = [];
        $euclidean_list = [];
        $supremum_list = [];

        foreach ($data as $key => $value) {
            if ($key == 0) {
                // Mendapatkan attribut dari dataset
                $modify = explode(";", $value[0]);
                foreach ($modify as $value) {
                    array_push($attribut, $value);
                }
                array_shift($attribut);
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

                $nilai_matriks = array_chunk($nilai, count($attribut));
            }
        }
        
        // Manhattan Distance
        foreach ($nilai_matriks as $key1 => $value1) {
            $manhattan_row = [];
            foreach ($nilai_matriks as $key2 => $value2) {
                $manhattan = 0;
                foreach ($value2 as $key3 => $value3) {
                    $manhattan += abs($value1[$key3] - $value3);
                }
                array_push($manhattan_row, $manhattan); 
            }
            array_push($manhattan_list, $manhattan_row);
        }

        // Euclidean Distance
        foreach ($nilai_matriks as $key1 => $value1) {
            $euclidean_row = [];
            foreach ($nilai_matriks as $key2 => $value2) {
                $euclidean = 0;
                foreach ($value2 as $key3 => $value3) {
                    $euclidean += pow(($value1[$key3] - $value3), 2);
                }
                array_push($euclidean_row, round(sqrt($euclidean), 2));
            }
            array_push($euclidean_list, $euclidean_row); 
        }

        // Supremum Distance
        foreach ($nilai_matriks as $key1 => $value1) {
            $supremum_row = [];
            foreach ($nilai_matriks as $key2 => $value2) {
                $supremum = 0;
                foreach ($value2 as $key3 => $value3) {
                    if (abs($value1[$key3] - $value3) > $supremum) {
                        $supremum = abs(($value1[$key3] - $value3));
                    }
                }
                array_push($supremum_row, $supremum);
            }
            array_push($supremum_list, $supremum_row);
        }

        $distances = [];
        $distances["Manhattan"] = $manhattan_list;
        $distances["Euclidean"] = $euclidean_list;
        $distances["Supremum"] = $supremum_list;
    } else {
        header("location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Project Data Mining</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="d-flex flex-column justify-content-center" style="height: 80vh">
            <div class="h2 text-center my-4 fw-bold">Proximity Matrix</div>
            <div class="d-flex flex-row flex-wrap mt-4 justify-content-center">
                <?php foreach ($distances as $index => $val) { ?>
                    <div class="mx-3">
                        <table style='border: 1px solid black; border-collapse: collapse'>
                            <thead>
                                <tr>
                                    <th colspan="<?php echo (count($baris)+1); ?>" style='padding: 5px 25px; border: 1px solid black; text-align: center'><?php echo $index; ?> Distance</th>
                                </tr>
                                <tr>
                                    <th style='padding: 5px 25px; border: 1px solid black;'></th>
                                    <?php foreach ($baris as $key => $value) { ?>
                                        <th style='padding: 5px 25px; border: 1px solid black;' class="text-center"><?php echo $value; ?></th>
                                    <?php } ?>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($val as $key => $value) { ?>
                                    <tr>
                                        <td class='fw-bold' style='padding: 5px 25px; border: 1px solid black;' class="text-center"><?php echo $baris[$key]; ?></td>
                                        <?php foreach ($value as $key2 => $value2) { ?>
                                            <td style='padding: 5px 25px; border: 1px solid black;' class="text-center"><?php echo $value2; ?></td>
                                        <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </body>
</html>