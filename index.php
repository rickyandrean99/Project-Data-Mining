<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hello </title>
    </head>
    <body>
        <div style="display: flex; align-items: center; flex-direction: column">
            <h1>Project Data Mining</h1>
            <form action="process.php" method="POST" enctype="multipart/form-data">
                <!-- Rony: Tambahin radio button (Manhattan, Euclidean, Supremum) disini -->
                <div>
                    <input type="radio" name="method" value="Manhattan">Manhattan<br>
                    <input type="radio" name="method" value="Euclidean">Euclidean<br>
                    <input type="radio" name="method" value="Supremum">Supremum<br>                  
                </div>    
                <div style="margin: 5% 0">
                    <input type="file" name="file">
                </div>
                <div>
                    <input type="submit" name="submit">
                </div>
            </form>

            <br><br><br><br><br>

            <form action="gini.php" method="POST" enctype="multipart/form-data">
                <div>Gini</div>
                <div style="margin: 5% 0">
                    <input type="file" name="file">
                </div>
                <div>
                    <input type="submit" name="submit">
                </div>
            </form>

            <br><br><br><br><br>

            <form action="entropy.php" method="POST" enctype="multipart/form-data">
                <div>Entropy</div>
                <div style="margin: 5% 0">
                    <input type="file" name="file">
                </div>
                <div>
                    <input type="submit" name="submit">
                </div>
            </form>
        </div>
    </body>
</html>