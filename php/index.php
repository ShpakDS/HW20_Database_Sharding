<?php
ob_start();

function connectDatabase($host, $dbname, $user, $password): PDO
{
    $dsn = "pgsql:host=$host;dbname=$dbname";
    return new PDO($dsn, $user, $password);
}

function insertBooks($pdo, $count): float
{
    $start = hrtime(true);

    echo "<script>updateProgressBar(0);</script>";

    $stmt = $pdo->prepare(
        "INSERT INTO books (title, author, price, published_date) VALUES (:title, :author, :price, :published_date)"
    );

    for ($i = 0; $i < $count; $i++) {
        $stmt->execute([
            ':title' => "Book $i",
            ':author' => "Author $i",
            ':price' => rand(10, 100),
            ':published_date' => date('Y-m-d', strtotime("-" . rand(1, 1000) . " days"))
        ]);

        if ($i % 1000 === 0 || $i === $count - 1) {
            $progress = ($i + 1) / $count * 100;
            echo "<script>updateProgressBar($progress);</script>";
            flush();
            ob_flush();
        }
    }

    $end = hrtime(true);
    return ($end - $start) / 1e6; // Return milliseconds
}

function readBooks($pdo, $count): float
{
    $start = hrtime(true);

    $stmt = $pdo->query("SELECT * FROM books LIMIT $count");
    $books = $stmt->fetchAll();

    $end = hrtime(true);
    return ($end - $start) / 1e6;
}

echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Bar Example</title>
    <script>
        function updateProgressBar(progress) {
            const progressBar = document.getElementById('progress-bar');
            progressBar.value = progress;
        }
    </script>
</head>
<body>
    <h1>Processing Inserts</h1>
    <progress id="progress-bar" value="0" max="100" style="width: 100%;"></progress>
    <pre>
HTML;

flush();
ob_flush();

$noSharding = connectDatabase('postgresql_b', 'books', 'user', 'password');
$fdwSharding = connectDatabase('postgresql_b1', 'shard1', 'user', 'password');
$citusSharding = connectDatabase('postgresql_b2', 'shard2', 'user', 'password');

$insertNoSharding = insertBooks($noSharding, 1000000);
$insertFDWSharding = insertBooks($fdwSharding, 1000000);

echo "<script>updateProgressBar(100);</script>";

echo "Performance (ms):\n";
echo "No Sharding - Insert: $insertNoSharding ms\n";
echo "FDW Sharding - Insert: $insertFDWSharding ms\n";

echo <<<HTML
    </pre>
</body>
</html>
HTML;