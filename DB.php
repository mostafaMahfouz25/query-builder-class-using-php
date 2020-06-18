<?php 


namespace Core;
use Mysql;
class DB 
{
    private $conn;
    private $table;
    private $query;
    private $where=[];
    private $and = false;
    


    public function __construct()
    {
        $this->conn = new \mysqli(HOST, USER, PASS,DB_NAME);
        if(!$this->conn)
        {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function table($table)
    {
        $this->table = $table;
        $this->where = [];
        return $this;
    }

    public function select(string $fields = " * ")
    {
        $this->query = "SELECT {$fields} FROM `$this->table` ";
        return $this;
    }

    public function where($field,$op="=",$value)
    {
        if(!count($this->where))
        {
            if(is_numeric($value))
            {
                $value = (int) $value;
                $this->where[] = " WHERE `{$field}`{$op}$value  ";
            }
            else 
            {

                $this->where[] = " WHERE `{$field}`{$op}'{$value}'  ";
            }
        }
        else 
        {
            if(is_numeric($value))
            {
                $value = (int) $value;
                $this->where[] = " AND `{$field}`{$op}$value  ";
            }
            else 
            {

                $this->where[] = " AND `{$field}`{$op}'{$value}'  ";
            }
        }


        $this->query .= end($this->where);
        return $this;
    }



    // get all data from table with condition or not 
    public function get()
    {
        $result = $this->conn->query($this->query);
        if($this->conn->affected_rows > 0)
        {
            if($result->num_rows > 0)
            {
                return (object) $result->fetch_all(MYSQLI_ASSOC);
            }
            else 
            {
                return [];
            }
        }   
        else 
        {   
            return [];
        }
    }


    // get specific row from database 
    public function getOne()
    {
        $this->query .= " LIMIT 1 ";
        $result = $this->conn->query($this->query);
        if($this->conn->affected_rows > 0)
        {
            if($result->num_rows > 0)
            {
                return $result->fetch_assoc();
            }
            else 
            {
                return [];
            }
        }   
        else 
        {   
            return null;
        }
    }



    // insert into database 
    public function insert(array $data)
    {
        $fileds = '';
        $values = '';

        foreach($data as $f => $v)
        {
            $v = $this->conn->real_escape_string(htmlspecialchars($v));
            $fileds .="`$f`,";
            $values .="'$v',";
        }
    
        // remove ,
        $fileds = substr($fileds,0,-1);
        $values = substr($values,0,-1);

        $this->query .= " INSERT INTO `$this->table` ({$fileds}) VALUES ({$values}) ";
        return $this;
    }





    // insert into database 
    public function update(array $data)
    {
        $fileds = '';
        $values = '';
        $q = '';
        foreach($data as $f => $v)
        {
            $v = $this->conn->real_escape_string(htmlspecialchars($v));
            $q .=" `$f` = '{$v}',";
        }
    
        // remove ,
        $q = substr($q,0,-1);

        $this->query .= " UPDATE `$this->table` SET {$q}  ";
        return $this;
        
    }



    // delete record  from table 

    public function delete()
    {
        $this->query = "DELETE FROM `{$this->table}` ";
        return $this;
    }
    

    // fire query and add action to mysqli
    public function save()
    {
        $result = $this->conn->query($this->query);
        // $this->conn->query(" UPDATE `courses` SET `co_title`='CCCCC' WHERE `co_id`='12' " );
        if($this->conn->affected_rows > 0 || $result == true)
        {
            return true;
        }
        else 
        {
            $this->get_error();
        }
    }



    // joining two tables 

    public function innerJoin($secondTable,$f1,$f2)
    {
        $condOne =$this->table.'.'.$f1;
        $condTwo =$secondTable.'.'.$f2;
        $this->query .= "INNER JOIN `{$secondTable}` ON {$condOne} = {$condTwo} ";
        
        return $this;
    }


    // add limit 

    public function limit($n)
    {
        $n = (int) $n;
        $this->query .= " LIMIT {$n} ";
        return $this;
    }




    public function typeQuery()
    {
        echo $this->query;
    }







    // type error 
    private function get_error()
    {
        return die("Error Occurre : ". $this->conn->error );
    }



}