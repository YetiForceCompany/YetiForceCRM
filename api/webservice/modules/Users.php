<?php

class API_Users
{

	public function authentication($email, $password)
	{
		return ['auth' => false, 'userID' => 2343, 'fullName' => 'TTTTTT FFFFFFF', 'email' => $email, 'error' => 'Błedne dane dostępowe'];
	}
}
