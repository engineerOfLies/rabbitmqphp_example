<?php
//generates a session key 
function keyGenerator($user_id){
	$key = bin2hex(random_bytes(32));

	return $key;
}

?>
