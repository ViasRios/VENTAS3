<?php
namespace app\controllers;

use app\models\mainModel;

class statusController extends mainModel {

    public function obtenerStatusControlador() {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("SELECT DISTINCT Status FROM ods ORDER BY Status ASC");
        $consulta->execute();
        return $consulta->fetchAll();
    }

    public function obtenerOdsPorStatusControlador($status) {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("SELECT * FROM ods WHERE Status = :status ORDER BY Fecha DESC");
        $consulta->bindParam(":status", $status);
        $consulta->execute();
        return $consulta->fetchAll();
    }

    public function obtenerResumenStatusControlador() {
        $conexion = mainModel::conectar();
        $consulta = $conexion->prepare("
            SELECT Status, COUNT(*) AS total 
            FROM ods 
            GROUP BY Status 
            ORDER BY Status ASC
        ");
        $consulta->execute();
        return $consulta->fetchAll();
    }

}
