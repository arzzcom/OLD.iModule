<?php
class DB {
	private $infor = array();
	private $connector = array();
	private $isLog = false;
	public $sql;

	function &instance() {
		if(isset($GLOBALS['_DB_']) == false || !$GLOBALS['_DB_']) $GLOBALS['_DB_'] = new DB();
		return $GLOBALS['_DB_'];
	}

	function __construct() {
		if (isset($_ENV['db']) == true) {
			$this->infor['default'] = $_ENV['db'];
		} else {
			if (isset($_ENV['dbfile']) == true && file_exists($_ENV['dbfile']) == true) {
				$readFile = @file($_ENV['dbfile']) or die('Not Found db.conf.php File!!<br>Please Install Solution Again!!');
			} else {
				$readFile = @file($_ENV['path'].'/config/db.conf.php') or die('Not Found db.conf.php File!!<br>Please Install Solution Again!!');
			}
			$this->infor['default']['type'] = trim($readFile[1]);
	
			$DBinfor = unserialize(ArzzDecoder($readFile[2]));
			$this->infor['default']['host'] = $DBinfor[0];
			$this->infor['default']['id'] = $DBinfor[1];
			$this->infor['default']['password'] = $DBinfor[2];
			$this->infor['default']['dbname'] = $DBinfor[3];
		}
		$this->DBconnect('default');
	}

	function DBconnect($db) {
		if (isset($this->connector[$db]) == false) {
			switch ($this->infor[$db]['type'] ) {
				case 'mysql' :
					$this->connector[$db] = @mysql_connect($this->infor[$db]['host'],$this->infor[$db]['id'],$this->infor[$db]['password']);
					@mysql_query("set names utf8");
				break;
			}
		}
	}

	function DBCheck() {
		$success = true;
		switch ($this->infor['default']['type'] ) {
			case 'mysql' :
				$this->connector['default'] = @mysql_connect($this->infor['default']['host'],$this->infor['default']['id'],$this->infor['default']['password']) or ($success = false);
			break;
		}

		return $success;
	}

	function DBinfor($db) {
		$dbinfor = $this->DBfetch($_ENV['table']['db'],array('dbtype','dbhost','dbid','dbpassword','dbname'),"WHERE `dbcode`='$db'");
		if ($dbinfor) {
			$this->infor[$db]['type'] = $dbinfor['dbtype'];
			$this->infor[$db]['host'] = $dbinfor['dbhost'];
			$this->infor[$db]['dbname'] = $dbinfor['dbname'];
			$this->infor[$db]['id'] = ArzzDecoder($dbinfor['dbid']);
			$this->infor[$db]['password'] = ArzzDecoder($dbinfor['dbpassword']);

			$this->DBconnect($db);
		}
	}

	function DBlock($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'LOCK TABLES `'.$this->infor[$db]['dbname'].'`.`'.$table.'` READ';
				@mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
			break;
		}
	}

	function DBunlock($db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'UNLOCK TABLES';
				@mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
			break;
		}
	}
	
	function DBFind($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$check = @mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$this->infor[$db]['dbname']."' AND table_name = '$table'"));
				if ($check[0] == 1) return true;
			break;
		}
		
		return false;
	}
	
	function FieldType($field,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$fieldType = array('varchar'=>'varchar','char'=>'char','tinyint'=>'tinyint','int'=>'int','bigint'=>'bigint','text'=>'text','longtext'=>'longtext','html'=>'longtext','date'=>'date','enum'=>'enum');
			break;
		}
		
		$return = array();
		$isArray = isset($field[0]) == true;
		if ($isArray == false) $field = array($field);
		
		for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
			if (in_array($field[$i]['type'],$fieldType) == false) return false;
			if (in_array($fieldType[$field[$i]['type']],array('text','longtext','date')) == false && isset($field[$i]['length']) == false) return false;
			if ($fieldType[$field[$i]['type']] == 'enum') $field[$i]['length'] = '\''.implode('\',\'',explode(',',str_replace('\'','',$field[$i]['length']))).'\'';
			
			if (in_array($fieldType[$field[$i]['type']],array('tinyint','int','bigint')) == true) {
				$field[$i]['default'] = isset($field[$i]['default']) == true && is_numeric($field[$i]['default']) == true ? $field[$i]['default'] : '0';
			} elseif ($fieldType[$field[$i]['type']] == 'date') {
				$field[$i]['default'] = isset($field[$i]['default']) == true && $field[$i]['default'] ? $field[$i]['default'] : '0000-00-00';
			} else {
				$field[$i]['default'] = isset($field[$i]['default']) == true ? $field[$i]['default'] : '';
			}
			
			$field[$i]['comment'] = isset($field[$i]['comment']) == true ? $field[$i]['comment'] : '';
			$return[$i] = array('name'=>$field[$i]['name'],'type'=>$fieldType[$field[$i]['type']],'length'=>$field[$i]['length'],'default'=>$field[$i]['default'],'comment'=>$field[$i]['comment']);
		}
		
		if ($isArray == false) $return = array_shift($return);
		
		return $return;
	}
	
	function IndexType($index,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$indexType = array('auto_increment'=>'auto_increment','primary'=>'primary','index'=>'index','unique'=>'unique','fulltext'=>'fulltext');
			break;
		}
		
		$return = array();
		$isArray = isset($index[0]) == true;
		if ($isArray == false) $index = array($index);
		
		for ($i=0, $loop=sizeof($index);$i<$loop;$i++) {
			if (in_array($index[$i]['type'],$indexType) == false) return false;
			if (isset($index[$i]['name']) == false) return false;
			
			$return[$i] = array('name'=>$index[$i]['name'],'type'=>$indexType[$index[$i]['type']]);
		}
		
		if ($isArray == false) $return = array_shift($return);
		
		return $return;
	}
	
	function DBreset($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` AUTO_INCREMENT=0';
				@mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
			break;
		}
	}

	function DBcreate($table,$field=array(),$index=array(),$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$isSuccess = true;
		
		$field = $this->FieldType($field,$db);
		$index = $this->IndexType($index,$db);
		if ($field == false) return false;
		if ($index == false) return false;
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'CREATE TABLE IF NOT EXISTS `'.$this->infor[$db]['dbname'].'`.`'.$table.'` (';
				
				for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
					if (in_array($field[$i]['type'],array('varchar','char','int','bigint','enum')) == true) {
						$field[$i] = '`'.$field[$i]['name'].'` '.$field[$i]['type'].'('.$field[$i]['length'].') NOT NULL DEFAULT \''.$field[$i]['default'].'\' COMMENT \''.$field[$i]['comment'].'\'';
					} elseif (in_array($field[$i]['type'],array('text','date')) == true) {
						$field[$i] = '`'.$field[$i]['name'].'` '.$field[$i]['type'].' NOT NULL DEFAULT \''.$field[$i]['default'].'\' COMMENT \''.$field[$i]['comment'].'\'';
					} elseif (in_array($field[$i]['type'],array('longtext')) == true) {
						$field[$i] = '`'.$field[$i]['name'].'` '.$field[$i]['type'].' NOT NULL COMMENT \''.$field[$i]['comment'].'\'';
					}
				}
				
				$query.= implode(', ',$field);
				
				$query.= ') ENGINE=MyISAM DEFAULT CHARSET=utf8;';

				@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
				
				$oIndexList = @mysql_query('SHOW INDEX FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'`',$this->connector[$db]);
				while ($oIndex = @mysql_fetch_assoc($oIndexList)) {
					@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` DROP INDEX `'.$oIndex['Key_name'].'`',$this->connector[$db]);
				}
				
				if ($isSuccess == true) {
					$indexType = array('auto_increment'=>'auto_increment','primary'=>'primary','index'=>'index','unique'=>'unique','fulltext'=>'fulltext');
				
					for ($i=0, $loop=sizeof($index);$i<$loop;$i++) {
						$index[$i]['name'] = implode('`,`',explode(',',$index[$i]['name']));
						if ($index[$i]['type'] == 'auto_increment') {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD PRIMARY KEY (`'.$index[$i]['name'].'`)',$this->connector[$db]);
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` CHANGE `'.$index[$i]['name'].'` `'.$index[$i]['name'].'` INT(11) NOT NULL AUTO_INCREMENT COMMENT \'고유값\'',$this->connector[$db]);
						}
						
						if ($index[$i]['type'] == 'primary') {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD PRIMARY KEY (`'.$index[$i]['name'].'`)',$this->connector[$db]);
						}
						
						if ($index[$i]['type'] == 'index') {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD INDEX (`'.$index[$i]['name'].'`)',$this->connector[$db]);
						}
						
						if ($index[$i]['type'] == 'unique') {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD UNIQUE (`'.$index[$i]['name'].'`)',$this->connector[$db]);
						}
						
						if ($index[$i]['type'] == 'fulltext') {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD FULLTEXT (`'.$index[$i]['name'].'`)',$this->connector[$db]);
						}
					}
				}
			break;
		}
		
		return $isSuccess;
	}
	
	function DBname($table,$newname,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$isSuccess = true;
		if ($this->DBfind($newname,$db) == false) {
			switch ($this->infor[$db]['type']) {
				case 'mysql' :
					$query = 'RENAME TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` TO `'.$this->infor[$db]['dbname'].'`.`'.$newname.'`';
					@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
				break;
			}
		} else {
			$isSuccess = false;
		}
		
		return $isSuccess;
	}
	
	function DBcompare($table,$field,$index,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$field = $this->FieldType($field,$db);
		$index = $this->IndexType($index,$db);
		
		if ($field == false) return false;
		if ($index == false) return false;

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$desc = @mysql_query('DESC `'.$this->infor[$db]['dbname'].'`.`'.$table.'`',$this->connector[$db]);
				$i = 0;
				while ($data = @mysql_fetch_assoc($desc)) {
					if ($data['Field'] != $field[$i]['name']) return false;
					if (in_array($data['Type'],array('text','longtext','date')) == true && $data['Type'] != $field[$i]['type']) return false;
					elseif (in_array($data['Type'],array('text','longtext','date')) == false && $data['Type'] != $field[$i]['type'].'('.$field[$i]['length'].')') return false;
					$i++;
				}
				
				if ($i != sizeof($field)) return false;

				$i = 0;
				$oIndexList = @mysql_query('SHOW INDEX FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'`',$this->connector[$db]);
				$keyName = array();
				$beforeKeyName = '';
				while ($oIndex = @mysql_fetch_assoc($oIndexList)) {
					if ($beforeKeyName == $oIndex['Key_name']) {
						$key = array_pop($keyName).','.$oIndex['Column_name'];
						$keyName[] = $key;
					} else {
						$keyName[] = $oIndex['Column_name'];
					}
					$beforeKeyName = $oIndex['Key_name'];
				}
				if (sizeof($keyName) != sizeof($index)) return false;
				for ($i=0, $loop=sizeof($keyName);$i<$loop;$i++) {
					if ($keyName[$i] != $index[$i]['name']) return false;
					$i++;
				}
				

				for ($i=0, $loop=sizeof($index);$i<$loop;$i++) {
					$index[$i]['name'] = implode('`,`',explode(',',$index[$i]['name']));
					if ($index[$i]['type'] == 'auto_increment') {
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` CHANGE `'.$index[$i]['name'].'` `'.$index[$i]['name'].'` INT(11) NOT NULL AUTO_INCREMENT COMMENT \'고유값\'',$this->connector[$db]);
						$auto_increment = $index[$i]['name'];
					}
				}
				
				$i = 0;
				$desc = @mysql_query('DESC `'.$this->infor[$db]['dbname'].'`.`'.$table.'`',$this->connector[$db]);
				while ($data = @mysql_fetch_assoc($desc)) {
					if ($auto_increment != $data['Field']) {
						if ($field[$i]['default']) {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` CHANGE `'.$data['Field'].'` `'.$data['Field'].'` '.$data['Type'].' NOT NULL DEFAULT \''.$field[$i]['default'].'\' COMMENT \''.$field[$i]['comment'].'\'');
						} else {
							@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` CHANGE `'.$data['Field'].'` `'.$data['Field'].'` '.$data['Type'].' NOT NULL COMMENT \''.$field[$i]['comment'].'\'');
						}
					}
					$i++;
				}
			break;
		}
		
		return true;
	}
	/*
	function DBcreate($table,$field,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$isSuccess = true;

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$check = @mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='".$this->infor[$db]['dbname']."' AND table_name = '$table'"));
				if ($check[0] == 1) return;

				$indexType = array('PRIMARY'=>'PRIMARY KEY','BTREE'=>'INDEX','UNIQUE'=>'UNIQUE');
				$fieldType = array('VARCHAR'=>'VARCHAR','INT'=>'INT','FILE'=>'INT','TEXT'=>'TEXT','HTML'=>'LONGTEXT','DATE'=>'DATE');

				$indexes = array();
				$fields = array();
				$query = 'CREATE TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` (';

				for ($i=0, $loop=sizeof($field);$i<$loop;$i++) {
					$fields[$i] = '`'.$field[$i]['name'].'`';
					if ($field[$i]['type'] != 'DATE' && $field[$i]['type'] != 'TEXT' && $field[$i]['type'] != 'HTML' && $field[$i]['type'] != 'SELECT') {
						$fields[$i].= ' '.$fieldType[$field[$i]['type']].'('.($field[$i]['type'] == 'FILE' ? '11' : $field[$i]['length']).')';
					} elseif ($field[$i]['type'] == 'SELECT') {
						$fields[$i].= ' ENUM('.$field[$i]['length'].')';
					} else {
						$fields[$i].= ' '.$fieldType[$field[$i]['type']];
					}
					$fields[$i].= ' NOT NULL';
					if (isset($field[$i]['default']) == true && $field[$i]['default'] != '') {
						$fields[$i].= ' DEFAULT \''.$field[$i]['default'].'\'';
					}
					if (isset($field[$i]['index']) == true && $field[$i]['index'] != '') {
						if ($field[$i]['index'] == 'PRIMARY') {
							$fields[$i].= ' PRIMARY KEY';
						} else {
							$indexes[] = ''.$indexType[$field[$i]['index']].'(`'.$field[$i]['name'].'`)';
						}
					}
					if (isset($field[$i]['option']) == true && $field[$i]['option'] == 'AUTO_INCREMENT') {
						$fields[$i].= ' AUTO_INCREMENT';
					}
					if (isset($field[$i]['info']) == true && $field[$i]['info'] != '') {
						$fields[$i].= ' COMMENT \''.$field[$i]['info'].'\'';
					}
				}

				$query.= implode(',',$fields);
				if (sizeof($indexes) > 0) $query.= ','.implode(',',$indexes);
				$query.= ') ENGINE = MYISAM';

				@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
			break;
		}
		
		$this->DBlog($query);

		return $isSuccess;
	}
	*/

	function DBremove($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$isSuccess = true;
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'DROP TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
				@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
			break;
		}
		
		$this->DBlog($query);

		return $isSuccess;
	}
	
	function FDlist($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$fields = array();
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'DESC `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
				$sql = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				while ($field = @mysql_fetch_assoc($sql)) {
					$fields[] = $field['Field'];
				}
			break;
		}
		
		return $fields;
	}
	
	function FDadd($table,$field,$position='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$isSuccess = true;
		
		$field = $this->FieldType($field,$db);

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD ';
				
				if (in_array($field['type'],array('varchar','char','int','bigint','enum')) == true) {
					$query.= '`'.$field['name'].'` '.$field['type'].'('.$field['length'].') NOT NULL DEFAULT \''.$field['default'].'\' COMMENT \''.$field['comment'].'\'';
				} elseif (in_array($field['type'],array('text','date')) == true) {
					$query.= '`'.$field['name'].'` '.$field['type'].' NOT NULL DEFAULT \''.$field['default'].'\' COMMENT \''.$field['comment'].'\'';
				} elseif (in_array($field['type'],array('longtext')) == true) {
					$query.= '`'.$field['name'].'` '.$field['type'].' NOT NULL COMMENT \''.$field['comment'].'\'';
				}
				
				if ($position == 'first') {
					$query.= ' FIRST';
				} elseif ($position) {
					$query.= ' AFTER '.$position;
				}

				@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
			break;
		}
		
		return $isSuccess;
	}

	function FDchange($table,$field,$change,$position='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$isSuccess = true;
		
		$change = $this->FieldType($change,$db);

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` CHANGE `'.$field.'` ';
				if (in_array($change['type'],array('varchar','char','int','bigint','enum')) == true) {
					$query.= '`'.$change['name'].'` '.$change['type'].'('.$change['length'].') NOT NULL DEFAULT \''.$change['default'].'\' COMMENT \''.$change['comment'].'\'';
				} elseif (in_array($change['type'],array('text','date')) == true) {
					$query.= '`'.$change['name'].'` '.$change['type'].' NOT NULL DEFAULT \''.$change['default'].'\' COMMENT \''.$change['comment'].'\'';
				} elseif (in_array($change['type'],array('longtext')) == true) {
					$query.= '`'.$change['name'].'` '.$change['type'].' NOT NULL COMMENT \''.$change['comment'].'\'';
				}
				
				if ($position == 'first') {
					$query.= ' FIRST';
				} elseif ($position) {
					$query.= ' AFTER '.$position;
				}

				@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
			break;
		}
		
		$this->DBlog($query);

		return $isSuccess;
	}

	function FDdrop($table,$field,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$isSuccess = true;

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` DROP `'.$field.'`';
				@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
			break;
		}
		
		$this->DBlog($query);

		return $isSuccess;
	}
	
	function IDinfo($table,$field,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$index = '';
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SHOW INDEX FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
				$sql = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				while($fetch = @mysql_fetch_assoc($sql)) {
					if ($fetch['Column_name'] == $field) {
						if ($fetch['Key_name'] == 'PRIMARY') $index = 'primary';
						elseif ($fetch['Non_unique'] == '0') $index = 'unique';
						else $index = 'index';
					}
				}
			break;
		}
		
		$this->DBlog($query);

		return $index;
	}
	
	function IDadd($table,$index,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$isSuccess = true;
		
		$index = $this->IndexType($index,$db);
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				if (($index['type'] != 'auto_increment' && $this->IDinfo($table,$index['name'],$db) != $index['type']) || ($index['type'] == 'auto_increment' && $this->IDinfo($table,$index['name'],$db) != 'primary')) {
					if ($this->IDinfo($table,$index['name'],$db) != '') {
						$this->IDdrop($table,$index['name'],$db='');
					}
					
					if ($index['type'] == 'auto_increment') {
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` DROP PRIMARY KEY',$this->connector[$db]);
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD PRIMARY KEY (`'.$index['name'].'`)',$this->connector[$db]);
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` CHANGE `'.$index['name'].'` `'.$index['name'].'` INT(11) NOT NULL AUTO_INCREMENT COMMENT \'고유값\'',$this->connector[$db]);
					} elseif ($index['type'] == 'primary') {
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` DROP PRIMARY KEY',$this->connector[$db]);
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD PRIMARY KEY (`'.$index['name'].'`)',$this->connector[$db]);
					} elseif ($index['type'] == 'index') {
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD INDEX (`'.$index['name'].'`)',$this->connector[$db]);
					} elseif ($index['type'] == 'unique') {
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD UNIQUE (`'.$index['name'].'`)',$this->connector[$db]);
					} elseif ($index[$i]['type'] == 'fulltext') {
						@mysql_query('ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` ADD FULLTEXT (`'.$index['name'].'`)',$this->connector[$db]);
					}
				}
			break;
		}
		
		return $isSuccess;
	}
	
	function IDdrop($table,$field,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);
		
		$isSuccess = true;
		
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				if ($this->IDinfo($table,$field,$db) == 'primary') {
					$query = 'ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` DROP PRIMARY KEY';
				} else if ($this->IDinfo($table,$field,$db) != '') {
					$query = 'ALTER TABLE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` DROP INDEX `'.$field.'`';
				} else {
					$query = '';
				}
				
				if ($query) {
					@mysql_query($query,$this->connector[$db]) or $isSuccess = $this->DBerror($query,mysql_error());
				}
			break;
		}
		
		return $isSuccess;
	}

	function DBindex($table,$column,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$index = '';
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SHOW INDEX FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
				$sql = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				while($fetch = @mysql_fetch_assoc($sql)) {
					if ($fetch['Column_name'] == $column) {
						if ($fetch['Key_name'] == 'PRIMARY') $index = 'PRIMARY KEY';
						elseif ($fetch['Non_unique'] == '0') $index = 'UNIQUE';
						else $index = 'INDEX';
					}
				}
			break;
		}
		
		$this->DBlog($query);

		return $index;
	}

	function DBsize($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$size = 0;
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SHOW TABLE STATUS FROM `'.$this->infor[$db]['dbname'].'` WHERE Name="'.$table.'"';
				$list = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				$status = @mysql_fetch_assoc($list);

				$size = $status['Data_length']+$status['Index_length'];
			break;
		}
		
		$this->DBlog($query);

		return $size;
	}

	function DBcount($table,$find='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				if (preg_match('/GROUP BY/i',$find) == true) {
					$query = 'SELECT * FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'` '.$find;
					$checkCount = mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
					$count = @mysql_num_rows($checkCount);
				} else {
					$query = 'SELECT COUNT(*) FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'` '.$find;
					$checkCount = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
					$check = @mysql_fetch_array($checkCount);
					$count = $check[0];
				}
			break;
		}
		
		$this->DBlog($query);

		return $count;
	}

	function DBupdate($table,$insertValue,$functionValue='',$find='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$updater = array();
		if (is_array($insertValue) == true) {
			foreach ($insertValue as $field=>$value) {
				$updater[] = '`'.$field.'`=\''.$this->AntiInjection($value,$db).'\'';
			}
		}

		if (is_array($functionValue) == true) {
			foreach ($functionValue as $field=>$value) {
				$updater[] = '`'.$field.'`='.$value;
			}
		}

		$updater = implode(',',$updater);

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'UPDATE `'.$this->infor[$db]['dbname'].'`.`'.$table.'` SET '.$updater.' '.$find;
				$this->sql = $query;
				$result = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
			break;
		}
		
		$this->DBlog($query);
		
		return $result;
	}

	function DBinsert($table,$insertValue,$functionValue='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$fields = array();
		$values = array();
		if (is_array($insertValue) == true) {
			foreach ($insertValue as $field=>$value) {
				$fields[] = '`'.$field.'`';
				$values[] = '\''.$this->AntiInjection($value,$db).'\'';
			}
		}

		if (is_array($functionValue) == true) {
			foreach ($functionValue as $field=>$value) {
				$fields[] = '`'.$field.'`';
				$values[] = $value;
			}
		}

		$insertor = '('.implode(',',$fields).') values ('.implode(',',$values).')';

		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'INSERT INTO `'.$this->infor[$db]['dbname'].'`.`'.$table.'` '.$insertor;
				@mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				$returnValue = mysql_insert_id($this->connector[$db]);
			break;
		}
		
		$this->DBlog($query);

		return $returnValue;
	}

	function GetFunction($code,$field,$num,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$function = '';
		switch (strtoupper($code)) {
			case 'SUM' :
				if ($this->infor[$db]['type'] == 'mysql') {
					if (preg_match('/`/',$field) == false) $function = 'sum(`'.$field.'`) as `'.$num.'`';
					else $function = 'sum('.$field.') as `'.$num.'`';
				}
			break;

			case 'MAX' :
				if ($this->infor[$db]['type'] == 'mysql') {
					if (preg_match('/`/',$field) == false) $function = 'max(`'.$field.'`) as `'.$num.'`';
					else $function = 'max('.$field.') as `'.$num.'`';
				}
			break;
			
			case 'MIN' :
				if ($this->infor[$db]['type'] == 'mysql') {
					if (preg_match('/`/',$field) == false) $function = 'min(`'.$field.'`) as `'.$num.'`';
					else $function = 'min('.$field.') as `'.$num.'`';
				}
			break;

			case 'COUNT' :
				if ($this->infor[$db]['type'] == 'mysql') {
					if ($field != '*') $field = '`'.$field.'`';
					$function = 'count('.$field.') as `'.$num.'`';
				}
			break;
		}

		return $function;
	}

	function DBjoins($tables,$selectors,$find='',$order='',$limit='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$func = 0;
		$sel = 0;
		for ($i=0, $loop=sizeof($selectors);$i<$loop;$i++) {
			for ($k=0, $loopk=sizeof($selectors[$i]);$k<$loopk;$k++) {
				if (preg_match("/([a-z]+)\(([^\)]+)\)/i",$selectors[$i][$k],$match) == true) {
					$thisSelector[$sel] = $this->GetFunction($match[1],$tables[$i].'=>'.$match[2],$func,$db);
					$func++;
				} else {
					$thisSelector[$sel] = '`'.$tables[$i].'`.`'.$selectors[$i][$k].'`';
				}
				$sel++;
			}
		}

		$select =implode(',',$thisSelector);

		if ($order) {
			if ($order == 'random') {
				switch ($this->infor[$db]['type']) {
					case 'mysql' :
						$order = 'ORDER BY RAND()';
					break;
				}
			} else {
				$temp = explode(',',$order);
				$temp[0] = explode('=>',$temp[0]);
				$order = 'ORDER BY `'.$temp[0][0].'`.`'.$temp[0][1].'` '.$temp[1];
			}
		}

		if ($limit) {
			$temp = explode(',',$limit);
			switch ($this->infor[$db]['type']) {
				case 'mysql' :
					$limit = 'LIMIT '.$temp[0].','.$temp[1];
				break;
			}
		}

		for ($i=0, $loop=sizeof($tables);$i<$loop;$i++) {
			$tables[$i] = '`'.$this->infor[$db]['dbname'].'`.`'.$tables[$i].'`';
		}

		$output = array();
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SELECT '.$select.' FROM '.implode(',',$tables);
				$query.= $find ? ' '.$find : '';
				$query.= $order ? ' '.$order : '';
				$query.= $limit ? ' '.$limit : '';

				$list = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				while ($fetch = @mysql_fetch_assoc($list)) {
					$output[] = $fetch;
				}
			break;
		}
		
		$this->DBlog($query);

		return $output;
	}

	function DBjoincount($tables,$find='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		for ($i=0, $loop=sizeof($tables);$i<$loop;$i++) {
			$tables[$i] = '`'.$this->infor[$db]['dbname'].'`.`'.$tables[$i].'`';
		}

		$output = array();
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SELECT * FROM '.implode(',',$tables);
				$query.= $find ? ' '.$find : '';

				$list = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				$count = mysql_num_rows($list);
			break;
		}
		
		$this->DBlog($query);

		return $count;
	}

	function DBfetch($table,$selector,$find='',$order='',$limit='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$select = is_array($selector) == false && $selector == '*' ? '*' : '';
		if (is_array($selector) == true) {
			for ($i=0, $total=sizeof($selector);$i<$total;$i++) {
				if (preg_match("/([a-z]+)\(([^\)]+)\)/i",$selector[$i],$match) == true) {
					$thisSelector[$i] = $this->GetFunction($match[1],$match[2],$i,$db);
				} else {
					$thisSelector[$i] = '`'.$selector[$i].'`';
				}
			}
			$select =implode(',',$thisSelector);
		}

		if ($this->infor[$db]['type'] == 'mysql' && preg_match("/MATCH \([^\)]+\) AGAINST \([^\)]+\)/",$find,$match) == true) {
			$select.= ', '.$match[0].' as `SEARCHRANK`';
		}

		if ($order) {
			if ($order == 'random') {
				switch ($this->infor[$db]['type']) {
					case 'mysql' :
						$order = 'ORDER BY RAND()';
					break;
				}
			} elseif (is_array($order) == true) {
				for ($i=0, $loop=sizeof($order);$i<$loop;$i++) {
					$temp = explode(',',$order[$i]);
					if (preg_match("/`/",$temp[0]) == false) $order[$i] = '`'.$temp[0].'` '.$temp[1];
					else $order[$i] = $temp[0].' '.$temp[1];
				}
				$order = 'ORDER BY '.implode(', ',$order);
			} else {
				$temp = explode(',',$order);
				if (preg_match("/`/",$temp[0]) == false) $order = 'ORDER BY `'.$temp[0].'` '.$temp[1];
				else $order = 'ORDER BY '.$temp[0].' '.$temp[1];
			}
		}

		if ($limit) {
			$temp = explode(',',$limit);
			switch ($this->infor[$db]['type']) {
				case 'mysql' :
					$limit = 'LIMIT '.$temp[0].','.$temp[1];
				break;
			}
		}

		$output = array();
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SELECT '.$select.' FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
				$query.= $find ? ' '.$find : '';
				$query.= $order ? ' '.$order : '';
				$query.= $limit ? ' '.$limit : '';

				$list = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				$fetch = @mysql_fetch_assoc($list);
			break;
		}
		
		$this->DBlog($query);

		return $fetch;
	}

	function DBfetchs($table,$selector,$find='',$order='',$limit='',$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		$select = is_array($selector) == false && $selector == '*' ? '*' : '';
		if (is_array($selector) == true) {
			for ($i=0, $total=sizeof($selector);$i<$total;$i++) {
				if (preg_match("/([a-z]+)\(([^\)]+)\)/i",$selector[$i],$match) == true) {
					$thisSelector[$i] = $this->GetFunction($match[1],$match[2],$i,$db);
				} else {
					$thisSelector[$i] = '`'.$selector[$i].'`';
				}
			}
			$select =implode(',',$thisSelector);
		}
		if ($this->infor[$db]['type'] == 'mysql' && preg_match("/MATCH \([^\)]+\) AGAINST \([^\)]+\)/",$find,$match) == true) {
			$select.= ', '.$match[0].' as `SEARCHRANK`';
		}

		if ($order) {
			if ($order == 'random') {
				switch ($this->infor[$db]['type']) {
					case 'mysql' :
						$order = 'ORDER BY RAND()';
					break;
				}
			} elseif (is_array($order) == true) {
				for ($i=0, $loop=sizeof($order);$i<$loop;$i++) {
					$temp = explode(',',$order[$i]);
					if (preg_match("/`/",$temp[0]) == false) $order[$i] = '`'.$temp[0].'` '.$temp[1];
					else $order[$i] = $temp[0].' '.$temp[1];
				}
				$order = 'ORDER BY '.implode(', ',$order);
			} else {
				$temp = explode(',',$order);
				if (preg_match("/`/",$temp[0]) == false) $order = 'ORDER BY `'.$temp[0].'` '.$temp[1];
				else $order = 'ORDER BY '.$temp[0].' '.$temp[1];
			}
		}

		if ($limit) {
			$temp = explode(',',$limit);
			switch ($this->infor[$db]['type']) {
				case 'mysql' :
					$limit = 'LIMIT '.$temp[0].','.$temp[1];
				break;
			}
		}

		$output = array();
		switch ($this->infor[$db]['type']) {
			case 'mysql' :
				$query = 'SELECT '.$select.' FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
				$query.= $find ? ' '.$find : '';
				$query.= $order ? ' '.$order : '';
				$query.= $limit ? ' '.$limit : '';
				$this->sql = $query;

				$list = @mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
				while ($fetch = @mysql_fetch_assoc($list)) {
					$output[] = $fetch;
				}
			break;
		}
		
		$this->DBlog($query);

		return $output;
	}

	function DBdelete($table,$find,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		if ($this->infor[$db]['type'] == 'mysql') {
			$query = 'DELETE FROM `'.$this->infor[$db]['dbname'].'`.`'.$table.'` '.$find;
			@mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
		}
		
		$this->DBlog($query);
	}

	function DBtruncate($table,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		if ($this->infor[$db]['type'] == 'mysql') {
			$query = 'TRUNCATE `'.$this->infor[$db]['dbname'].'`.`'.$table.'`';
			@mysql_query($query,$this->connector[$db]) or $this->DBerror($query,mysql_error());
		}
		
		$this->DBlog($query);
	}

	function DBerror($query,$error) {
		$error = GetUTF8($error);
		if (isset($_ENV['debug']) == true && $_ENV['debug'] == true) echo '<br />DBERROR : '.$error.' ('.$query.')<br />';
		$fp = @fopen($_ENV['userfilePath'].'/log/dberror_'.date('Ymd').'.log','a');
		@fputs($fp,'['.date('Y-m-d H:i:s').'] '.$error.' ('.$query.')'."\n");
		@fclose($fp);
		@chmod($_ENV['path'].'/log/dberror_'.date('Ymd').'.log',0707);

		return false;
	}
	
	function DBlog($query) {
		if ($this->isLog == true) {
			$fp = @fopen($_ENV['path'].'/log/dblog_'.date('Ymd').'.log','a');
			@fputs($fp,'['.date('Y-m-d H:i:s').'] '.$query."\n");
			@fclose($fp);
			@chmod($_ENV['path'].'/log/dblog_'.date('Ymd').'.log',0707);
		}
	}

	function AntiInjection($str,$db='') {
		if (!$db) $db = 'default';
		if (isset($this->infor[$db]) == false) $this->DBinfor($db);

		if ($this->infor[$db]['type'] == 'mysql') {
			if (!is_numeric($str)) $str = @mysql_escape_string($str);
		}
		return $str;
	}
}
?>