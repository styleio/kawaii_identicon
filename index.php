<?php

require_once 'src/Kawaii_identicon.php';

function generateIcon($size = 100)
{
    $userId = md5(uniqid(rand(), true)); // ランダムなID生成
    $imgBase64 = KawaiiIdenticon::get($userId, $size);
    return '<img src="data:image/png;base64,' . $imgBase64 . '" alt="Kawaii Icon" />';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kawaii Identicons</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            grid-gap: 10px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        img {
            width: 100px;
            height: 100px;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php
        for ($i = 0; $i < 15; $i++) {
            echo generateIcon(100);
        }
        ?>
    </div>
</body>

</html>