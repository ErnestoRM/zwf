<?php ?>
<!DOCTYPE>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>


        <title>zerfrex.com</title>
        <style type="text/css">

            .zwf {
                font-family: Raleway;
                background-color: white;
                color: #808080;
            }

            .message-container {
                height: 40vh;
            }

            .message {
                position: relative;
                font-size: 3vw;
                font-weight: 400;
            }

            h1 {
                position: relative;
                font-size: 3vw;
                font-weight: 400;
            }

            .message::after {
                position: absolute;
                right: -5px;
                bottom: 5px;
                width: 4px;
                height: 3vw;
                content: '';
                opacity: 1;
                display: block;
                background: #808080;
            }

            .message.animate-cursor::after {
                animation: cursor .6s infinite ease;
            }

            @keyframes cursor {
                0% {
                    opacity: 1;
                }
                100% {
                    opacity: 0;
                }
            </style>
        </head>
        <body>
            <div class="jumbotron text-center zwf">

                <img src="res/zerfrexLogo.png" alt="Zerfrex framework">
                <h3>The Framework for code writers</h3>
            </div>

            <section class="main-section text-center zwf">
                <div class="message-container">
                    <span class="message" id="message"></span>
                </div>
            </section>
            <div>
                <div class="jumbotron text-center zwf">

                    <span>
                        <?php
                        if (isset($name)) {
                            echo $name;
                        }

                        if (isset($all)) {

                            ?>
                            <pre>
                                <?php print_r($all); ?>
                            </pre>
                        <?php } ?>
                    </span>
                </div>

            </div>
            <script type="text/javascript">
                (function (w, d) {
                    var message = document.getElementById('message');
                    // var message = message.parentNode;
                    var index = 0;
                    var interval = null;
                    var messages = ['Easy {Coding}', 'Funny {Coding}', 'Happy {Coding}']; // Cada posicion del array es un mensaje
                    var countMessage = messages.length - 1;
                    var sleepTimeout = 1000;
                    var sleepInterval = 150;

                    function update(func)
                    {
                        message.classList.toggle('animate-cursor');
                        setTimeout(function () {
                            message.classList.toggle('animate-cursor');
                            interval = setInterval(func, sleepInterval);
                        }, sleepTimeout);
                    }

                    function clear()
                    {
                        var count = message.innerHTML.length;
                        if (count === 0)
                        {
                            clearInterval(interval);
                            message.innerHTML = '';
                            index = (index >= countMessage) ? 0 : index + 1;
                            update(write);
                        } else
                        {
                            message.innerHTML = message.innerHTML.toString().substr(0, count - 1);
                        }
                    }

                    function write()
                    {
                        var count = message.innerHTML.length;
                        var countCharacter = messages[index].length - 1;
                        message.innerHTML += messages[index][(count > 0) ? count : 0];

                        if (countCharacter === count)
                        {
                            clearInterval(interval);
                            update(clear);
                        }
                    }

                    update(write);
                })(window, document);
            </script>
        </body>
    </html>