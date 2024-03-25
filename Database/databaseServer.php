#!/usr/bin/php
<?php

require_once __DIR__.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

function insertRating($request)
{
	$authed = doAuth($request['username'], $request['token']);
	if ($authed == "not authed"){
		return array ("destination" => 'frontend', 'authed' => "not authed");
	}
	include "mysqlconnect.php";
	$username = $request['username'];
	$query = "SELECT userID from accounts where username = '$username'";
	$userIDcheck = $mydb->query($query);
	$userIDarray = $userIDcheck ->fetch_assoc();
	$userID = $userIDarray['userID'];
	$recipeID = $request['recipeName'];
	$rating = $request['rating'];
	if ($rating == "like"){
		$rating = 100;
	}else{
		$rating = 0;
	}
	
	$query2 = "INSERT INTO recipe_rating (userID, recipeID, rating) VALUES ('$userID', '$recipeID', '$rating')";
	$result = $mydb->query($query2);

	return array ("destination" => 'frontend', 'message' => "success");
}
function getFridgeRecipe($request)
{
	$authed = doAuth($request['username'], $request['token']);
	if ($authed == "not authed"){
		return array ("destination" => 'frontend', 'authed' => "not authed");
	}
	include "mysqlconnect.php";
	$username = $request['username'];
	$query = "SELECT userID from accounts where username = '$username'";
	$userIDcheck = $mydb->query($query);
	$userIDarray = $userIDcheck ->fetch_assoc();
	$userID = $userIDarray['userID'];
	
	$query2 = "select ingredient_name from ingredients left join fridge on fridge.ingredient_id = ingredients.ingredient_id where fridge.userID = '$userID'";
	
	$result = $mydb->query($query2);
	$fridgelist = [];
	if ($result->num_rows > 0)
	{
		while($rows = $result->fetch_assoc())
		{
			$ingredientName = $rows['ingredient_name'];
			$ingredientName = str_replace(' ', '', $ingredientName) ;
			array_push($fridgelist, $ingredientName);
		}
	}
	return array ("type" => 'getFridgeRecipe', "destination" => 'dmz', 'ingredients' => $fridgelist);
	
	
	
	
}

function getFridge($request) 
{
	$authed = doAuth($request['username'], $request['token']);
	if ($authed == "not authed"){
		return array ("destination" => 'frontend', 'authed' => "not authed");
	}
	include "mysqlconnect.php";
	$username = $request['username'];
	$query = "SELECT userID from accounts where username = '$username'";
	$userIDcheck = $mydb->query($query);
	$userIDarray = $userIDcheck ->fetch_assoc();
	$userID = $userIDarray['userID'];
	
	$query2 = "select ingredient_name, fridge.quantity from ingredients left join fridge on fridge.ingredient_id = ingredients.ingredient_id where fridge.userID = '$userID'";
	
	$result = $mydb->query($query2);
	$fridgelist = [];
	if ($result->num_rows > 0)
	{
		while($rows = $result->fetch_assoc())
		{
			$ingredientName = $rows['ingredient_name'];
			$quantity = $rows['quantity'];
			$array = array($ingredientName, $quantity);
			array_push($fridgelist, $array);
		}
	}
	return array ("destination" => 'frontend', 'message' => $fridgelist);
}

function addFridge($request)
{
	$authed = doAuth($request['username'], $request['token']);
	if ($authed == "not authed"){
		return array ("destination" => 'frontend', 'authed' => "not authed");
	}
	include "mysqlconnect.php";
	$username = $request['username'];
	$ingredient = $request['ingredient'];
	$quantity = $request['quantity'];
	
	$userID = "select userID from accounts where username = '$username'";
	$userIDresult = $mydb->query($userID);
	$userIDresults = $userIDresult->fetch_assoc();
	$ingredientID = "select ingredient_id from ingredients where ingredient_name = '$ingredient'";
	$ingredientIDresult = $mydb->query($ingredientID);
	$ingredientIDresults =  $ingredientIDresult->fetch_assoc();
	$user = $userIDresults['userID'];
	$ingre = $ingredientIDresults['ingredient_id'];
	
	$check = "select * from fridge where userID = '$user' and ingredient_id = '$ingre'";
	$result = $mydb->query($check);
	
	if ($result->num_rows > 0)
	{
		return array ("destination" => 'frontend', 'message' => "failed");
	}else{
		$query = "INSERT INTO fridge (userID, ingredient_id, quantity) 
		VALUES ('$user', '$ingre', '$quantity')";
	
		$result = $mydb->query($query);
		return array ("destination" => 'frontend', 'message' => "success");
	}
}	

function getIngredientsList($request)
{
	$authed = doAuth($request['username'], $request['token']);
	if ($authed == "not authed"){
		return array ("destination" => 'frontend', 'authed' => "not authed");
	}
	include "mysqlconnect.php";
	$query = "SELECT * from ingredients";
	$result = $mydb->query($query);
	$ingredients = [];
	if ($result->num_rows > 0)
	{
		while($rows = $result->fetch_assoc())
		{
			$foodName = $rows['ingredient_name'];
			array_push($ingredients, $foodName);
		}
	}
	return array ("destination" => 'frontend', 'message' => $ingredients);
}

function doLogin($username,$password)
{

	include "mysqlconnect.php";
	$query = "SELECT * from accounts WHERE
		username = '$username'";
	$result = $mydb->query($query);
	if ($result->num_rows == 1){
		$results = $result->fetch_assoc();
		if (password_verify($password, $results['password'])){
			echo "\n\n";
			$token = bin2hex(openssl_random_pseudo_bytes(25));
			$date = date('Y/m/d h:i:s', time()+1800);
			$query = "UPDATE accounts
			 	SET sessionToken = '$token', expire = '$date'
			 	WHERE username = '$username'";
			 
			$result = $mydb->query($query);
			return array ("destination" => 'frontend', 'username' => $username, 'message' => "Account found", "token" => $token);
		}else{
			return array ("destination" => 'frontend', 'username' => NULL, 'message' => "Account not found");
		}
	}else{
		echo "\n\n";
		return array ("destination" => 'frontend', 'username' => NULL, 'message' => "Account not found");
	}	
}
function doRegister($username, $password, $email)
{
	include "mysqlconnect.php";
	$query = "SELECT * from accounts WHERE
		username = '$username'";
		
	$result = $mydb->query($query);
	if ($result->num_rows == 1){
		echo "\n\n";
		return array ("destination" => 'frontend', 'message' => "Failed");
	}else{
		//$token = bin2hex(openssl_random_pseudo_bytes(25));
		//$expire = null;
		$hashedP = password_hash($password, PASSWORD_DEFAULT);
		$query = "INSERT INTO accounts (username, password, email, sessionToken, expire)
			 VALUES ('$username', '$hashedP', '$email', NULL, NULL)";
		$result = $mydb->query($query);
		return array ("destination" => 'frontend', 'message' => "success");
	}	
	
}

function doLogout($username)
{
	include "mysqlconnect.php";
	$null = null;	
	$query = "UPDATE accounts
		 SET sessionToken = NULL, expire = NULL
		 WHERE username = '$username'";
	$result = $mydb->query($query);
	return array ("destination" => 'frontend', 'message' => "success");
	
}

function doAuth($username, $token){
	include "mysqlconnect.php";
	echo "\n";
	echo "AUTHING";
	echo "\n";
	$currentTime = time();
	$query = "SELECT username ,sessionToken, expire from accounts
	WHERE username = '$username' AND sessionToken = '$token' AND expire < NOW() ";
	$result = $mydb->query($query);
	if ($result->num_rows > 0){
		return "authed";
	}else{
		return "not authed";
	}
	
}



$connection = new AMQPStreamConnection('172.23.62.86', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

$channel->queue_declare('dataQueue', false, true, false, false);

echo "\ntestRabbitMQServer BEGIN".PHP_EOL;

$callback = function ($msg) use ($channel) {
    echo "======================================\n";
    echo "recieved request\n";
    echo "--------------------------------------\n\n";
    $request = json_decode($msg->body, true);
    var_dump($request);
    $response = '';

    try {
        switch ($request['type']) {
            case "Login":
                $response = doLogin($request['username'], $request['password']);
                break;
            case "Register":
                $response = doRegister($request['username'], $request['password'], $request['email']);
                break;
            case "Logout":
            	$response = doLogout($request['username']);
            	break;
            case "Auth":
            	$response = doAuth($request['username'], $request['token']);
            	break;
            case "getIngredientsList":
            	$response = getIngredientsList($request);
            	break;
            case "addIngredient":
            	$response = addFridge($request);
            	break;
            case "getFridge":
            	$response = getFridge($request);
            	break;
            case "getFridgeRecipe":
            	$response = getFridgeRecipe($request);
            	break;
            case "rating":
            	$response = insertRating($request);
            	break;
            default:
                $response = ['success' => false, 'message' => "Request type not handled"];
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => $e->getMessage()];
    }
    
    
    $responseMsg = new AMQPMessage(
        json_encode($response),
        array('correlation_id' => $msg->get('correlation_id'))
    );

    $channel->basic_publish($responseMsg, '', $msg->get('reply_to'));
    echo "Sending Response\n";
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('dataQueue', '', false, true, false, false, $callback);

try {
    while (true) {
        $channel->wait();
    }
} catch (Exception $e) {
    echo 'An error occurred: ', $e->getMessage(), "\n";
    $channel->close();
    $connection->close();
}

$channel->close();
$connection->close();
