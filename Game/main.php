<?php

/* Fare un gioco a livelli di difficoltà in cui (in base alla difficoltà) vengono estratti dei numeri primi
(es.: facile 3 numeri, medio 7 numeri e difficile 21 numeri)  e poi si generano 50 valori combinando i numeri primi selezionati e
poi moltiplicati per un numero a caso tra 7 e 21
// CON COMBINANDO NOI INTENDIAMO QUESTO: Prendi un numero primo tra quelli estratti, moltiplicalo per un numero random tra 7 e 21. Ripeti 50 volte.
L’utente ogni volta deve indicare un divisore e premere il tasto continua. Il programma toglie i numeri divisibili per
il numero inserito e mostra i restanti e conta anche i tentativi rimasti.
Si vince quando si riescono ad eliminarli tutti i numeri. Ed il sistema mostra il numero di mosse effettuate.
Prima di procedere alla creazione progettare un mock-up grafico del sistema
*/

// RACCOGLIMENTO DEI DATI -- SESSIONE DA IMPLEMENTARE
session_start();

// Funzione che verifica se un numero è primo
function isPrime($number) {
    if($number <= 1) return false;

    for($i=2; $i <=sqrt($number); $i++) {
        if ($number % $i == 0) {
            return false;
        }
    }
    return true;
}

// Funzione crea lista di numeri primi in base a difficoltà
function generatePrimeList ($difficulty) {
    $primeList = [];
    for ($i=0; $i < $difficulty; $i++) {
        $prime = 0;
        do {
            $prime = rand(2, 10000);
        }while (!isPrime($prime) || in_array($prime, $primeList)); // in_array($prime, $primeList)) garantisce che ogni numero primo nella lista sia univoco
        $primeList[$i] = $prime;
    }
    return $primeList;
}

// Funzione che crea la lista di gioco (Crea un numero random, moltiplica per primoRandom, moltiplica per valore [7, 21])
function generateGameList ($difficulty) {
    $primeNumbers = generatePrimeList($difficulty);
    $gameList = [];
    for ($i = 0; $i < 50; $i++) {
        // Prendi numero primo random e moltiplicalo per valore tra [7, 21]
        $gameList[$i] = $primeNumbers[rand(0, count($primeNumbers) - 1)] * rand(7, 21);
    }
    return $gameList;
}

// Funzione che elimina i numeri divisibili per number dalla lista
function removeNumbers ($list,  $number) {
    $listanuova=[];

    for($i=0; $i<count($list); $i++) {
        if($list[$i] % $number !== 0){
            $listanuova[] = $list[$i];
        }
    }
    return $listanuova;
}

// Funzione che stampa la lista

function printList($gameList) {
    if (empty($gameList)) {
        echo "<p>Nessun numero presente.</p>";
        return;
    }

    echo '<div class="container mt-3">';
    echo '<div class="row row-cols-5 g-2">'; // 5 numeri per riga

    foreach ($gameList as $value) {
        echo '
        <div class="col">
            <div class="p-2 border rounded text-center bg-light">
                ' . $value . '
            </div>
        </div>';
    }

    echo '</div></div>';
}

if (isset($_POST['Reset'])){
	session_unset(); 
	session_destroy();
	$gameList = [];
	$tries = 0;
	echo "Inserisci la difficoltà !!!";
}else{

// DETERMINIAMO LA DIFFICOLTA'
$difficultyMap = [
    "facile" => 3,
    "medio" => 7,
    "difficile" => 21
];

$diffKey = isset($_POST['difficulty']) ? $_POST['difficulty'] : null;
$difficulty = isset($difficultyMap[$diffKey]) ? $difficultyMap[$diffKey] : null;

if ($difficulty !== null) {
    $_SESSION['gameList'] = generateGameList($difficulty);
    $_SESSION['tries'] = 0;
}

// 3. Se invece ESISTE → ricarico
$gameList = isset($_SESSION['gameList']) ? $_SESSION['gameList'] : [];
$tries = isset($_SESSION['tries']) ? $_SESSION['tries'] : 0;

// 4. Verifico se il number esiste
$number=isset($_POST['number']) ? $_POST['number'] : 0;

// FLUSSO PRINCIPALE
if($number !== "" && $number >1) {

    $gameList = removeNumbers($gameList, $number); //Rigeneriamo la lista con i numeri tolti
    $tries++;

    $_SESSION['tries'] = $tries;
    $_SESSION['gameList'] = $gameList;
    if(count($gameList) == 0) {
        echo "HAI VINTO !!!". "<br>";
       echo "hai vinto in $tries tentativi". "<br>";
        
        echo"GIOCA DI NUOVO !!!". "<br>";
		// Aggiunta: distruggiamo la sessione per far ricominciare
        unset($_SESSION['gameList']); 
        unset($_SESSION['tries']);
    }else{
        echo "sei a $tries tentativi" . "<br>";
    }
}else if (isset($_POST['number'])&& !isset($_POST['Reset'])){
echo "Il numero 1 o inferiori ad esso non sono validi, inseriscine un altro" . "<br>";
}

if(isset($_SESSION['gameList']) && count($gameList) > 0) {
    echo '<fieldset>LISTA:</fieldset> ';
    printList($gameList);
}else{
	 echo"Inserisci la difficoltà !!!";
}
}
?>

<html>
<head>
    <title>SMINATORE MATEMATICO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        h1 {
            text-align: center;
        }

        h3 {
            text-align: center;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;   /* Centra orizzontalmente */
            min-height: 100vh;
            text-align: center; /* Centra tutto il testo */
        }

        fieldset {
            padding: 20px;
            border: 2px solid #007bff;
            border-radius: 10px;
            background-color: #f8f9fa;
            margin-top: 20px;
        }
		
        .bottonerosso { 
            background-color: #f44336;
            color: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<h1>SMINATORE MATEMATICO</h1>
<h3>Created By Cavallini & Pratesi</h3>

<fieldset>
    <form method="post" action="main.php">
        <?php
		// Se la lista del gioco è VUOTA (partita non iniziata o appena finita)
		// usiamo empty() che controlla sia se non esiste sia se è un array vuoto
        if (empty($gameList)) {
            echo '
        <select name="difficulty">
            <option></option>
            <option value="facile">Facile</option>
            <option value="medio">Medio</option>
            <option value="difficile">Difficile</option>
        </select> <br>

        ';
        }
        ?>
        <?php if (!empty($gameList))
        {
            echo '<input type="number" placeholder="inserisci un numero" name="number"> <br>';
        }
        ?>
        <input type="submit" name= "Submit" value="Invia"> <br>


        <input type="submit" name="Reset" value="Reset" class="bottonerosso">

    </form>
</fieldset>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>