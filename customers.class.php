<?php

/*
------------------------------------------------------------
-                File Name : customers.class.php           - 
-                Part of Project: prog02 				   -
------------------------------------------------------------
-                Written By: George Corser				   -
-		  Additional Documentation: Matthew Hozeska		   -
------------------------------------------------------------
- File Purpose:                                            -
- This file contains the main php code that the program    -
- will call. It contains all of the functions for 		   -
- generating html forms and records, all of the validation -
- functions, and the sql statements for handling database. - 
------------------------------------------------------------
- Program Purpose:                                         -
-                                                          -
- This program is an object-orientated crud application.   -
- It demonstrates a simple create, read, update, and delete-
- database application. 
------------------------------------------------------------
- Global Variable Dictionary:             				   -
- id - The id tag for all records.						   -
- name – The name tag for all records.					   -
- email - The email tag for all records.				   -
- mobile - The phone number tag for all records.           -
- noerrors - A flag for determining if all fields were     -
- entered correctly.									   -
- nameError - Checks if the name field was left empty.	   -
- emailError - Checks if the name field was left empty.	   -
- mobileError - Checks if the name field was left empty.   -
- title - Displays the html title for the application.     -
- tableName - Field for the database table name. 		   -
------------------------------------------------------------
*/
header('Access-Control-Allow-Origin: *');
class Customer { 
    public $id;
    public $name;
    public $email;
    public $mobile;
	public $password; // text from HTML form
	public $password_hashed; // hashed password
    private $noerrors = true;
    private $nameError = null;
    private $emailError = null;
    private $mobileError = null;
	private $passwordError = null;
    private $title = "Customer";
    private $tableName = "customers";
    
	/*
     * This method generates the html columns, title, and fields for
	 * the create page.
     * - Input: User presses create button
     * - Processing: php generating html functions
     * - Output: html for the create page
     * - Precondition: Public variables set (name, email, mobile, password)
     *   and database connection variables are set in database.php.
     * - Postcondition: User is redirected to the create page.
	 */
    function create_record() { // display "create" form
        $this->generate_html_top (1);
        $this->generate_form_group("name", $this->nameError, $this->name, "autofocus");
        $this->generate_form_group("email", $this->emailError, $this->email);
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile);
		$this->generate_form_group("password", $this->passwordError, $this->password, "", "password");
		//echo <a href='upload02.html' class='btn btn-success'>Upload02</a>	
		//$this->generate_form_group("upload", "", "", "", "upload");
        $this->generate_html_bottom (1);
    } // end function create_record()

	/*
     * This method generates the html columns, title, and fields for
	 * the read page.
     * - Input: User presses read button, function accepts the id of record selected.
     * - Processing: php generating html functions
     * - Output: html for the read page, with fields already set in read-only
     * - Precondition: Public variables set (id, name, email, mobile)
     * - and database connection variables are set in database.php.
     * - Postcondition: User is redirected to the read page.
	 */    
    function read_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(2);
        $this->generate_form_group("name", $this->nameError, $this->name, "disabled");
        $this->generate_form_group("email", $this->emailError, $this->email, "disabled");
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile, "disabled");
        $this->generate_html_bottom(2);
    } // end function read_record()
 
	/*
     * This method updates the record selected in the database with the updated
	 * data that the user entered.
     * - Input: User can change the field data and presses update button.
     * - Processing: php generating html functions, SELECT into database using sql.
     * - Output: html for the update page, with fields already set from selected record.
     * - Precondition: Public variables set (id, name, email, mobile)
     *   and database connection variables are set in database.php.
     * - Postcondition: User is redirected to the update page and record is updated in database.
	 */ 
    function update_record($id) { // display "update" form
        if($this->noerrors) $this->select_db_record($id);
        $this->generate_html_top(3, $id);
        $this->generate_form_group("name", $this->nameError, $this->name, "autofocus onfocus='this.select()'");
        $this->generate_form_group("email", $this->emailError, $this->email);
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile);
        $this->generate_html_bottom(3);
    } // end function update_record()
 
	/*
     * This method deletes the record selected in the database.
     * - Input: User selects the delete button on specific record.
     * - Processing: php generating html functions, SELECT into database using sql.
     * - Output: html for the delete page, with fields already set from selected record in read-only.
     * - Precondition: Public variables set (id, name, email, mobile)
     *   and database connection variables are set in database.php.
     * - Postcondition: User is redirected to the delete page and record is then deleted in database.
	 */
    function delete_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(4, $id);
        $this->generate_form_group("name", $this->nameError, $this->name, "disabled");
        $this->generate_form_group("email", $this->emailError, $this->email, "disabled");
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile, "disabled");
        $this->generate_html_bottom(4);
    } // end function delete_record()
    
    /*
     * This method inserts one record into the table, 
     * and redirects user to List, IF user input is valid, 
     * OTHERWISE it redirects user back to Create form, with errors
     * - Input: user data from Create form
     * - Processing: INSERT (SQL)
     * - Output: None (This method does not generate HTML code,
     *   it only changes the content of the database)
     * - Precondition: Public variables set (name, email, mobile)
     *   and database connection variables are set in datase.php.
     *   Note that $id will NOT be set because the record 
     *   will be a new record so the SQL database will "auto-number"
     * - Postcondition: New record is added to the database table, 
     *   and user is redirected to the List screen (if no errors), 
     *   or Create form (if errors)
     */
    function insert_db_record () {
        if ($this->fieldsAllValid()) { // validate user input
            // if valid data, insert record into table
            $pdo = Database::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->password_hashed = MD5($this->password);
			// safe code
            $sql = "INSERT INTO $this->tableName (name,email,mobile, password_hash) values(?, ?, ?, ?)";
			// dangerous code
			//$sql = "INSERT INTO $this->tableName (name,email,mobile) values('$this->name', '$this->email', '$this->mobile')";
            $q = $pdo->prepare($sql);
			// safe code
            $q->execute(array($this->name, $this->email, $this->mobile, $this->password_hashed));
			// dangerous code
			//$q->execute(array());
            Database::disconnect();
            header("Location: $this->tableName.php"); // go back to "list"
        }
        else {
            // if not valid data, go back to "create" form, with errors
            // Note: error fields are set in fieldsAllValid ()method
            $this->create_record(); 
        }
    } // end function insert_db_record
    
	/*
     * This method selects the record from the database using SQL.
     * - Input: User selects a button that requires this sub function.
     * - Processing: SQL SELECT * FROM query.
     * - Output: (none) 
     * - Precondition: Public variables set (id, name, email, mobile)
     *   and database connection variables are set in database.php.
     * - Postcondition: name, email, and mobile variables are set to what the user entered
	 * - and matches from the database.
	 */
    private function select_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM $this->tableName where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->mobile = $data['mobile'];
    } // function select_db_record()
 
	/*
     * This method updates the selected record from the database using SQL.
     * - Input: User selects the update button that requires this sub function.
     * - Processing: SQL SELECT * FROM query.
     * - Output: (none) 
     * - Precondition: Public variables set (id, name, email, mobile)
     *   and database connection variables are set in database.php.
     * - Postcondition: name, email, and mobile variables are set to what the user entered
	 * - and matches from the database.
	 */ 
    function update_db_record ($id) {
        $this->id = $id;
        if ($this->fieldsAllValid()) {
            $this->noerrors = true;
            $pdo = Database::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "UPDATE $this->tableName  set name = ?, email = ?, mobile = ? WHERE id = ?";
            $q = $pdo->prepare($sql);
            $q->execute(array($this->name,$this->email,$this->mobile,$this->id));
            Database::disconnect();
            header("Location: $this->tableName.php");
        }
        else {
            $this->noerrors = false;
            $this->update_record($id);  // go back to "update" form
        }
    } // end function update_db_record 
 
	/*
     * This method deletes the record from the database using SQL.
     * - Input: User selects the delete button that requires this sub function.
     * - Processing: SQL SELECT * FROM query.
     * - Output: (none) 
     * - Precondition: Public variables set (id, name, email, mobile)
     *   and database connection variables are set in database.php.
     * - Postcondition: Record is deleted in the database.
	 */ 
    function delete_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM $this->tableName WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        Database::disconnect();
        header("Location: $this->tableName.php");
    } // end function delete_db_record()
	
 	/*
     * This method generates the HTML title relating to what button the user pushed.
     * - Input: User selects a button that requires this sub function.
     * - Processing: php generating HTML code.
     * - Output: HTML with the title being either create, read, update, or delete.
     * - Precondition: Public variables set (id, name, email, mobile)
     * - Postcondition: Title is updated with the related header value.
	 */   
    private function generate_html_top ($fun, $id=null) {
        switch ($fun) {
            case 1: // create
                $funWord = "Create"; $funNext = "insert_db_record"; 
                break;
            case 2: // read
                $funWord = "Read"; $funNext = "none"; 
                break;
            case 3: // update
                $funWord = "Update"; $funNext = "update_db_record&id=" . $id; 
                break;
            case 4: // delete
                $funWord = "Delete"; $funNext = "delete_db_record&id=" . $id; 
                break;
            default: 
                echo "Error: Invalid function: generate_html_top()"; 
                exit();
                break;
        } // end function generate_html_top()
		
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$funWord a $this->title</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                <style>label {width: 5em;}</style>
                    "; 
        echo "
            </head>";
        echo "
            <body>
                <div class='container'>
                    <div class='span10 offset1'>
                        <p class='row'>
                            <h3>$funWord a $this->title</h3>
                        </p>
                        <form class='form-horizontal' action='$this->tableName.php?fun=$funNext' method='post'>                        
                    ";
    } // end function generate_html_top()

	/*
     * This method generates the HTML button underneath the fields relating to what button the user pushed.
     * - Input: User selects a button that requires this sub function.
     * - Processing: php generating HTML code.
     * - Output: HTML with the title being either create, read, update, or delete.
     * - Precondition: Public variables set (id, name, email, mobile)
     * - Postcondition: Bottom buttons are updated with the related header value.
	 */    
    private function generate_html_bottom ($fun) {
        switch ($fun) {
            case 1: // create
                $funButton = "<button type='submit' class='btn btn-success'>Create</button>"; 
                break;
            case 2: // read
                $funButton = "";
                break;
            case 3: // update
                $funButton = "<button type='submit' class='btn btn-warning'>Update</button>";
                break;
            case 4: // delete
                $funButton = "<button type='submit' class='btn btn-danger'>Delete</button>"; 
                break;
            default: 
                echo "Error: Invalid function: generate_html_bottom()"; 
                exit();
                break;
        }
		
        echo " 
                            <div class='form-actions'>
                                $funButton
                                <a class='btn btn-secondary' href='$this->tableName.php'>Back</a>
								<a href='upload02.html' class='btn btn-success'>Upload02</a>
                            </div>
                        </form>
                    </div>

                </div> <!-- /container -->
            </body>
        </html>
                    ";
    } // end function generate_html_bottom()
    
	/*
     * This method generates the HTML for all the fields that the user can interact with.
     * - Input: Fields are generated with appropriate label headers and modifiers.
     * - Processing: php generating HTML code.
     * - Output: HTML fields for the user to interact with.
     * - Precondition: Public variables set (id, name, email, mobile)
     * - Postcondition: Fields are created.
	 */
	 private function generate_form_group ($label, $labelError, $val, $modifier="", $fieldType="text") {
        echo "<div class='form-group";
        echo !empty($labelError) ? ' alert alert-danger ' : '';
        echo "'>";
        echo "<label class='control-label'>$label &nbsp;</label>";
        //echo "<div class='controls'>";
        echo "<input "
            . "name='$label' "
            . "type='$fieldType' "
            . "$modifier "
            . "placeholder='$label' "
            . "value='";
        echo !empty($val) ? $val : '';
        echo "'>";
        if (!empty($labelError)) {
            echo "<span class='help-inline'>";
            echo "&nbsp;&nbsp;" . $labelError;
            echo "</span>";
        }
        //echo "</div>"; // end div: class='controls'
        echo "</div>"; // end div: class='form-group'
    } // end function generate_form_group()

 	/*
     * This method validates all the field entries if they are empty or match a proper email address.
     * - Input: User presses a bottom button that attempts to either create, or update the record.
     * - Processing: php if statements, checking each appropriate field for bad data.
     * - Output: (none)
     * - Precondition: Public variables set (id, name, email, mobile)
     * - Postcondition: Fields are validated and can then be created or updated into the database.
	 */   
    private function fieldsAllValid () {
        $valid = true;
        if (empty($this->name)) {
            $this->nameError = 'Please enter Name';
            $valid = false;
        }
        if (empty($this->email)) {
            $this->emailError = 'Please enter Email Address';
            $valid = false;
        } 
        else if ( !filter_var($this->email,FILTER_VALIDATE_EMAIL) ) {
            $this->emailError = 'Please enter a valid email address: me@mydomain.com';
            $valid = false;
        }
        if (empty($this->mobile)) {
            $this->mobileError = 'Please enter Mobile phone number';
            $valid = false;
        }
        return $valid;
		
    } // end function fieldsAllValid() 

	/*
     * This method acts as the index.php page which initially displays all records with the crud buttons.
     * - Input: Database is established and user clicks on customers.php file.
     * - Processing: Displaying html for the buttons and displays all records from sql selecting into database.
     * - Output: Main page with records and appropriate buttons.
     * - Precondition: Database is valid and connection is established.
     * - Postcondition: Page is generated with all created records.
	 */    
    function list_records() {
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$this->title" . "s" . "</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                    ";  
        echo "
            </head>
            <body>
                <a href='https://github.com/mthozesk/crud_oo_complete' target='_blank'>Github</a><br />
				<a href='http://csis.svsu.edu/~mthozesk/cis355wi19/customers/prog02_Diagram.jpg' target='_blank'>Diagram1</a><br />
				<a href='http://csis.svsu.edu/~mthozesk/cis355wi19/customers/prog03_Diagram.jpg' target='_blank'>Diagram2</a><br />
				<a href='http://mthozesk.000webhostapp.com/Prog04/' target='_blank'>Prog04-000webhost</a><br />
                <div class='container'>
                    <p class='row'>
                        <h3>$this->title" . "s" . "</h3>
                    </p>
                    <p>
                        <a href='$this->tableName.php?fun=display_create_form' class='btn btn-success'>Create</a>
						<a href='logout.php' class='btn btn-warning'>Logout</a> 
						<a href='upload01.html' class='btn btn-success'>Upload01</a>
						
					</p>
                    <div class='row'>
                        <table class='table table-striped table-bordered'>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                    ";
		//		<a href='upload02.html' class='btn btn-success'>Upload02</a>	
        $pdo = Database::connect();
        $sql = "SELECT * FROM $this->tableName ORDER BY id DESC";
        foreach ($pdo->query($sql) as $row) {
            echo "<tr>";
            echo "<td>". $row["name"] . "</td>";
            echo "<td>". $row["email"] . "</td>";
            echo "<td>". $row["mobile"] . "</td>";
            echo "<td width=250>";
            echo "<a class='btn btn-info' href='$this->tableName.php?fun=display_read_form&id=".$row["id"]."'>Read</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-warning' href='$this->tableName.php?fun=display_update_form&id=".$row["id"]."'>Update</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-danger' href='$this->tableName.php?fun=display_delete_form&id=".$row["id"]."'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        Database::disconnect();        
        echo "
                            </tbody>
                        </table>
                    </div>
                </div>

            </body>

        </html>
                    ";  
    } // end function list_records()
    
} // end class Customer
