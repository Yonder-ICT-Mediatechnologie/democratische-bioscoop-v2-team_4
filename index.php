<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="css/popup.css">
</head>

<body>

    <?php
    session_start();
    //check if user is logged in. if not redirect to login page
    if (!isset($_SESSION["user_id"])) {
        header("Location: auth.php");
        exit();
    }
    ?>

    <div class="nav-bar">
        <nav>
            <a href="index.php">home</a>

        </nav>
        <div class="account-button">
            <a href="account.php">account</a>
        </div>
    </div>
    <div class="container">
        <div class="vote-button">
            <button class="btn btn-primary">Stem op de film van de week!</button>
        </div>

        <div class="popup-overlay" id="popupOverlay">
            <div class="popup-film">
                <button class="popup-close" id="popupClose">&times;</button>
                <div class="film1">
                    <button class="vote-btn-one">Stem op film a</button>
                </div>
                <div class="film2">
                    <button class="vote-btn-two">Stem op film b</button>
                </div>
            </div>
        </div>
        <div class="text"></div>
    </div>




    <script src="js/rest.js"></script>
    <script>
        document.querySelector('.btn.btn-primary').addEventListener('click', function() {
            document.getElementById('popupOverlay').classList.add('active');
        });
        document.getElementById('popupClose').addEventListener('click', function() {
            document.getElementById('popupOverlay').classList.remove('active');
        });
        document.getElementById('popupOverlay').addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('active');
        });
    </script>
    <script>
        // const restService = 'http://localhost:3000';
        const restService = 'https://project-bioscoop-restservice.azurewebsites.net';
        const apiKey = '29~LIlSjDYFr5OrhU3f';

        // // Test: films toevoegen
        // postFilmData(restService, apiKey, {
        //     "title": "Spiderman",
        //     "description": "Over een man die zich spiderman noemt",
        //     "url_trailer": "https://www.youtube.com/watch?v=JfVOs4VSpmA",
        //     "timestamp": Date.now(),
        //     "category": "action"
        // });
        // postFilmData(restService, apiKey, {
        //     "title": "Superman",
        //     "description": "Echt een held",
        //     "url_trailer": "https://www.youtube.com/watch?v=T6DJcgm3wNY",
        //     "timestamp": Date.now(),
        //     "category": "action"
        // });

        const print = (text) => {
            document.querySelector('.text').innerHTML += text + '<br>';
        };

        // Test: alle films ophalen
        getFilms(restService, apiKey)
            .then((data) => {
                data.forEach(film => print(film.title + ' <br> ' + film.description + '<br>'));
            });

        // Test: details van 1 film ophalen
        getFilms(restService, apiKey)
            .then((data) => {
                getFilmDetails(restService, apiKey, data[0]._id)
                    .then((details) => console.log(details));
            });

        // Test: vote up film
        getFilms(restService, apiKey)
            .then((data) => {
                voteUp(restService, apiKey, data[0]._id)
                    .then((result) => console.log(result));
            });

        // // Test: detele a film
        // getFilms(restService, apiKey)
        //     .then((data) => {
        //         deleteFilm(restService, apiKey, data[0]._id)
        //             .then((result)=>console.log(result));
        //     });
    </script>
</body>

</html>