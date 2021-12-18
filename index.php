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
        <div class="d-flex" style="height: 80vh">
            <div class="row container p-0 pt-5" style="margin: auto">
                <div class="h2 fw-bold text-center mb-5">Project Data Mining</div>

                <div class="col-3 m-3 p-3 border">
                    <div class="h5 fw-bold mb-4 text-center">Proximity Matrix</div>
                    <form action="proximity.php" method="POST" enctype="multipart/form-data">
                        <input class="form-control" type="file" name="file" id="formFile">
                        <button type="submit" name="submit" class="btn btn-success w-100 mt-3">Submit</button>
                    </form>
                    <small class="d-block mt-2 fst-italic">
                        *Proximity matrix yang digunakan adalah Manhattan, Euclidean dan Supremum. Dataset <b>wajib</b> berupa angka
                    </small>
                </div>

                <div class="col-4 m-3 p-3 border">
                    <div class="h5 fw-bold mb-4 text-center">Gini Best Split</div>
                    <form action="gini.php" method="POST" enctype="multipart/form-data">
                        <input class="form-control" type="file" name="file" id="formFile">
                        <button type="submit" name="submit" class="btn btn-success w-100 mt-3">Submit</button>
                    </form>
                    <small class="d-block mt-2 fst-italic">
                        *Gini Best Split menerima dataset berupa data categorical maupun data kontinu
                    </small>
                </div>

                <div class="col-4 m-3 p-3 border">
                    <div class="h5 fw-bold mb-4 text-center">Entropy Best Split</div>
                    <form action="entropy.php" method="POST" enctype="multipart/form-data">
                        <input class="form-control" type="file" name="file" id="formFile">
                        <button type="submit" name="submit" class="btn btn-success w-100 mt-3">Submit</button>
                    </form>
                    <small class="d-block mt-2 fst-italic">
                        *Entropy Best Split menerima dataset berupa data categorical maupun data kontinu
                    </small>
                </div>
            </div>
        </div>
    </body>
</html>