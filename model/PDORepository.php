<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PDORepository
 *
 * @author fede
 */
abstract class PDORepository {
    
    const USERNAME = "root";
    const PASSWORD = "";
	const HOST ="localhost";
	const DB = "un_aventon1";
    
//poner ahi arriba los datos de la BD para que quede linkeada


    private function getConnection(){
        $u=self::USERNAME;
        $p=self::PASSWORD;
        $db=self::DB;
        $host=self::HOST;
        $connection = new PDO("mysql:dbname=$db;host=$host", $u, $p);
        return $connection;
    }
    
    protected function queryList($sql, $args){
        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll();
    }
	
	protected function queryDevuelveId($sql, $args){
        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute($args);
        return $connection->lastInsertId();
    }
	

    //funcion para a la que se le envian las consultas... parametros (sql= consulta , args= parametros)
    // hay ejemplos en el MODEL
    
}
