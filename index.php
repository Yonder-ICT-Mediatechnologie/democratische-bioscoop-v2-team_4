<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title></title>
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

    <div class="text"></div>

    <script src="js/rest.js"></script>
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
            data.forEach(film => print(film.title + ' - ' + film.description + '<br>'));
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