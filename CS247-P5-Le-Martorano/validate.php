<link rel="stylesheet" type="text/css" href="form.css">

<body>
	<div class="phpPage">
		<?php
			// Krystal T Le - Austin Martorano
			// Web Dev 
			// Project 5. Fall 2016


			//Include password.php. Source: https://github.com/ircmaxell/password_compat
			include("password.php");
			$GLOBALS["error"] = array();//Stores errors

			//Get name from form and validate to make sure name includes first name and last name
			function ValidateName($name){
				if (preg_match("/^[a-zA-Z ]{1,30} [a-zA-Z ]{1,30}$/", $name)){
					return true;
				}else{
					array_push($GLOBALS["error"],"Invalid Input: Name Format &ltFirstName&gt &ltLastName&gt");

					return false;
				}
			}

			//Create username based on the name entered
			function CreateUserName($name){
				$fullname=explode(' ', $name);
				$firstname=$fullname[0][0];
				$lastname=substr($fullname[1], 0,6);
				return $firstname.$lastname;
			}

			//Hash password using password_hash() function
			function CreatePassword($password){
				$options = [
				    'cost' => 10,
				    'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
				];
				$hash=password_hash("$password", PASSWORD_BCRYPT, $options);
				return $hash;

			}

			//Make sure address is in the right format
			function ValidateAddress($address){
				if (preg_match("/^\\d+ ([a-zA-Z]{1}.?)+ [a-zA-Z ]+(.,|,)? [a-zA-Z ]+, [a-zA-Z]+(,?) \d{5}$/", $address)){
					return true;
				}else{
					array_push($GLOBALS["error"],"Invalid Input: Address Format &ltStreet Number&gt &ltDirection&gt &ltStreet Name&gt, &ltCity&gt, &ltState&gt, &ltZIP&gt");
					return false;
				}
			}
			//Check if age is greater than or equal to 18
			function ValidateAge($age){
				if ($age>=18){		
					return true;
				}else{
					array_push($GLOBALS["error"],"Invalid Input: Age must be greater than or equal to 18");
					return false;
				}
			}

			//Check whether major exists
			function ValidateField($field){
				$majors = array("computer science", "computer engineering", "mathematics", "data science");
				if (preg_match("/^[a-zA-Z ]{3,30} [a-zA-Z ]{3,30}$/", $field)){
					foreach($majors as $major){
						if ($field==$major){
							return true;
						}
					}
					array_push($GLOBALS["error"], "Invalid Input: Major does not exist");
				}else{
					array_push($GLOBALS["error"]," Invalid Input: Field you entered is not is one of the following: computer science, mathematics, computer engineering, or data science");
					return false;
				}
			}

			//Check if password is at least 8 characters in length
			function ValidatePassword($password){

				if (strlen($password)>=8){

					return true;
				}else{
					array_push($GLOBALS["error"],"Invalid Input: Password must contains greater than or equal to 8 characters");
					return false;
				}
			}


			//This function establish connections to the database
			function database($first, $last, $address, $age, $field, $user_pw, $hash_pw){
				$servername = "localhost";
				$username = "cs247_group1";
				$password = "159267624";
				$dbname = "cs247_group1";
				// Create connection
				$conn = mysqli_connect($servername, $username, $password, $dbname);
				// Check connection
				if (!$conn) {
				    die("Connection failed: " . mysqli_connect_error());
				}
				//Check what radio button is checked using switch statement
				if (isset($_GET['record'])) 
					{
						switch($_GET['record']) {
							//if Add record is checked
					        case "add_record":
					        	AddRecord($conn, $first, $last, $address, $age, $field, $hash_pw);
					        	break;
					        //If Retrieve Record is checked
					        case "retrieve_record":
					        	RetrieveRecord($conn, $first, $last, $user_pw);
					        	break;
				        }
					}
				mysql_close();
			}

			// // This function perform insert query.
			function AddRecord($conn, $first, $last, $address, $age, $field, $hash_pw){
				//Insert query
				$query= "INSERT INTO `Users` (`userid`, `first`, `last`, `address`, `age`, `field`, `password`) VALUES (NULL, '$first', '$last','$address',$age,'$field','$hash_pw');";
				//Attempt to insert data into data base. Output error if any
				//If Success
				if (mysqli_query($conn, $query)) {
				    echo "Welcome $first. Your account was created.";
				} else {
				    echo "Error: " . $query . "<br>" . mysqli_error($conn);
				}
				mysqli_close();
			}

			// //This function will retrieve data from database.
			function RetrieveRecord($connection, $first, $last, $user_pw){
				// Query based on first name and last name
				$query="SELECT * FROM `Users` WHERE `first`='$first' AND `last`='$last';";
				// //Attempt to insert data into data base. Output error if any
				$result = $connection->query($query);
				//If there's data returned
				if ($result->num_rows != 0){
					// // fetch associative array 
					while ($row = $result->fetch_assoc()) {
						//Getting password from Database
						$hash_pw=$row['password'];

				       	echo "First Name: ".$row['first']."<br>";
				       	echo "Last Name: ".$row['last']."<br>";
				       	echo "Address: ".$row['address']."<br>";
				       	echo "Age: ".$row['age']."<br>";
				       	echo "Field: ".$row['field']."<br>";
				       	echo "Password: ".$hash_pw."<br>";

				       	//Compare password entered by user with password stored in database using Password_Verify function
				       	if (password_verify($user_pw, $hash_pw)) 
				       	{ 
						    echo 'Password Matched with Record Found in Database'; 
						} else { 
						    echo 'Password does not match with database record'; 
						} 

				    }
				}else{
					echo "Sorry, no record found for $first, $last";
				}
				mysqli_close();
			}


			//Main
			function ValidateForm(){
				//Spliting name to get first and last name
				$name=$_GET["name"];
				$new_name=explode(' ', $name);
				$first=$new_name[0];
				$last=$new_name[1];

				//Getting other information on forms.
				$address=$_GET["address"];
				$age=$_GET["age"];
				$field=$_GET["field"];
				$password=$_GET["password"];
				$username=CreateUserName($name);
				
				//Hasing password with salt
				$hash_pwd=CreatePassword($password);

				//Validating functions
				$name_value=ValidateName($name);
				$address_value=ValidateAddress($address);
				$age_value=ValidateAge($age);
				$field_value=ValidateField($field);
				$pwd_value=ValidatePassword($password);
				
				//Only output username and password when all the fields are entered correctly.
				if (($name_value ===true) and ($address_value===true) and ($age_value===true) and ($field_value===true) and ($pwd_value===true)){
					//Doing Database query when all fields are validated
					database($first, $last, $address, $age, $field, $password, $hash_pwd);

				}
				else{
				//Or else, print out the errors.
					echo "Sorry, invalid form entry<br/>";
					echo "Problem:<br/>";
					foreach($GLOBALS["error"] as $err){
						echo $err."<br>";
					}

				}
			}
			ValidateForm();
		?>
	</div>
</body>