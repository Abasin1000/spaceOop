<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Fleet Generator met Stats</title>
    <style>
        /* Algemene stijl voor de pagina */
        body {
            font-family: 'Press Start 2P', cursive; /* Pixel-art lettertype */
            background: #1d1f21;
            color: #e1e1e1;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3 {
            color: #f9f9f9;
            text-align: center;
        }

        h1 {
            font-size: 3em;
            margin-top: 30px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }

        .button {
            background-color: #ff5a5f;
            color: #fff;
            border: 3px solid #c44647;
            padding: 12px 40px;
            font-size: 1.2em;
            cursor: pointer;
            text-transform: uppercase;
            margin: 10px;
            border-radius: 5px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.3);
        }

        .button:hover {
            background-color: #c44647;
            box-shadow: 0 10px 15px rgba(0,0,0,0.5);
        }

        .leaderboard, .battlelog {
            margin-top: 30px;
            padding: 20px;
            background: #333;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: left;
            font-size: 1.1em;
        }

        .battlelog ul, .leaderboard ul {
            list-style: none;
            padding: 0;
        }

        .battlelog li, .leaderboard li {
            padding: 8px;
            margin: 5px 0;
            background-color: #444;
            border-radius: 5px;
        }

        .team {
            background-color: #282a36;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.2);
        }

        .team h2 {
            font-size: 2em;
            margin-bottom: 15px;
        }

        .team ul {
            list-style: none;
            padding: 0;
        }

        .team li {
            font-size: 1.2em;
            padding: 8px;
            background-color: #333;
            margin: 6px 0;
            border-radius: 5px;
            color: #f9f9f9;
        }

        .team li span {
            color: #ff5a5f; /* Damage color */
        }

        /* Achtergrond animatie voor retro gevoel */
        @keyframes blink {
            0% { opacity: 0.5; }
            50% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        .blink {
            animation: blink 1s infinite;
        }

        /* Retro screen effect */
        .screen-effect {
            background: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);
            color: #e1e1e1;
        }

        /* Pixel font en buttons */
        @font-face {
            font-family: 'Press Start 2P';
            src: url('https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap');
        }

    </style>
</head>
<body>
    <div class="container">
        <h1 class="blink">Team Fleet Battle</h1>

        <form method="post">
            <button type="submit" name="generate" class="button">Genereer Teams & Battle</button>
            <button type="submit" name="reset" class="button">Reset Leaderboard & Battlelog</button>
        </form>

        <div class="screen-effect">
            <?php
            session_start();

            // Initialiseer leaderboard en battlelog
            if (!isset($_SESSION['leaderboard'])) {
                $_SESSION['leaderboard'] = ['Team Rood' => 0, 'Team Blauw' => 0];
            }
            if (!isset($_SESSION['battlelog'])) {
                $_SESSION['battlelog'] = [];
            }

            // Functie om willekeurige stats te genereren
            function genereerStat($type) {
                return $type === 'HP' ? rand(50, 100) : rand(10, 20);
            }

            // Functie om willekeurige namen te genereren
            function genereerNaam($type) {
                $namen = $type === 'leider' ? ['Abasin', 'Dylan', 'Melvin', 'Nys', 'Rick'] : ['Vloot A', 'Vloot B', 'Vloot C', 'Vloot D'];
                return $namen[array_rand($namen)];
            }

            // Functie om een team te genereren
            function genereerTeam($teamNaam) {
                $leider = genereerNaam('leider');
                $vloten = [];
                for ($i = 0; $i < 3; $i++) {  // Genereer 3 vloten per team
                    $vloten[] = [
                        'naam' => genereerNaam('vloot'),
                        'HP' => genereerStat('HP'),
                        'damage' => genereerStat('damage')
                    ];
                }
                return [
                    'naam' => $teamNaam,
                    'leider' => $leider,
                    'vloten' => $vloten
                ];
            }

            // Battle simulatie functie
            function simuleerBattle($team1, $team2) {
                $team1HP = 0;
                $team2HP = 0;

                // Bereken totale HP na battle
                foreach ($team1['vloten'] as $i => $vloot1) {
                    $vloot2 = $team2['vloten'][$i];
                    $vloot1['HP'] -= $vloot2['damage'];
                    $vloot2['HP'] -= $vloot1['damage'];
                    $team1HP += max(0, $vloot1['HP']);
                    $team2HP += max(0, $vloot2['HP']);
                }

                // Bepaal winnaar
                if ($team1HP > $team2HP) {
                    return $team1['naam'];
                } elseif ($team2HP > $team1HP) {
                    return $team2['naam'];
                } else {
                    return 'Gelijkspel';
                }
            }

            // Als de knop wordt ingedrukt, genereer teams en simuleer een battle
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['generate'])) {
                    $team1 = genereerTeam("Team Rood");
                    $team2 = genereerTeam("Team Blauw");

                    // Simuleer de battle
                    $winnaar = simuleerBattle($team1, $team2);

                    // Update leaderboard en battlelog
                    if ($winnaar !== 'Gelijkspel') {
                        $_SESSION['leaderboard'][$winnaar] += 1;
                        $_SESSION['battlelog'][] = "$winnaar versloeg " . ($winnaar === $team1['naam'] ? $team2['naam'] : $team1['naam']);
                    } else {
                        $_SESSION['battlelog'][] = "De battle eindigde in een gelijkspel!";
                    }

                    // Toon de teams met stats
                    foreach ([$team1, $team2] as $team) {
                        echo "<div class='team'>";
                        echo "<h2>{$team['naam']}</h2>";
                        echo "<p>Leider: {$team['leider']}</p>";
                        echo "<ul>";
                        foreach ($team['vloten'] as $vloot) {
                            echo "<li>{$vloot['naam']} - HP: {$vloot['HP']}, Damage: <span>{$vloot['damage']}</span></li>";
                        }
                        echo "</ul>";
                        echo "</div>";
                    }

                    echo "<h3>Winnaar van de battle: <span style='color: green;'>$winnaar</span></h3>";
                }

                // Reset leaderboard en battlelog
                if (isset($_POST['reset'])) {
                    $_SESSION['leaderboard'] = ['Team Rood' => 0, 'Team Blauw' => 0];
                    $_SESSION['battlelog'] = [];
                }
            }

            // Leaderboard tonen
            echo "<div class='leaderboard'>";
            echo "<h2>Leaderboard</h2>";
            foreach ($_SESSION['leaderboard'] as $team => $punten) {
                echo "<p>$team: $punten punten</p>";
            }
            echo "</div>";

            // Battlelog tonen
            echo "<div class='battlelog'>";
            echo "<h2>Battlelog</h2>";
            if (empty($_SESSION['battlelog'])) {
                echo "<p>Geen battles gelogd.</p>";
            } else {
                echo "<ul>";
                foreach ($_SESSION['battlelog'] as $log) {
                    echo "<li>$log</li>";
                }
                echo "</ul>";
            }
            echo "</div>";
            ?>
        </div>
    </body>
</html>
