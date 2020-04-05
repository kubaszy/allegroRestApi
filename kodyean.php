<!doctype html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="style.css">
        <title>...CoMpArE...Kubasz</title>
    </head>
    <body>
        <center>
            <nav>
               <?php include 'menu.php';?>
            </nav>
            <div id="tables">
                <header>PROMOCJA ALLEGRO 0.5%</header>
                
                <a href="https://allegro.pl/auth/oauth/authorize?response_type=code&client_id=xxx0&redirect_uri=http://localhost/kodyean.php"><button id="login">UZYSKAJ TOKEN DOSTĘPOWY DLA RAFMAR</button></a>

                <a href="https://allegro.pl/auth/oauth/authorize?response_type=code&client_id=xxx&redirect_uri=http://localhost/kodyean.php"><button id="login">UZYSKAJ TOKEN DOSTĘPOWY DLA WINTEL_PL</button></a>
                <?php include 'kodyeanphp.php';?>

                <form method="post" id="formularz">
                    <b> Wybierz konto na które się zalogowałeś: </b>
                    <select name="who_login">
                        <option value='rafmar'>rafmar</option>
                        <option value='wintel_pl'>wintel_pl</option>
                    </select>
                    <button type="submit">Szukaj EAN</button>
                </form>
                
            </div>
    </center>
    </body>
</html>