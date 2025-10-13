<div class="box pastel-purpled">
    <h2 class="subtitle"><i class="fas fa-archive"></i> Historial de Solicitudes de Refacciones</h2>

    <!-- Filtro por estado -->
    <form method="GET" class="mb-4" id="filtro-form">
        <div class="field is-grouped">
            <div class="control">
                <div class="select is-small">
                    <select name="estado" id="estado" onchange="this.form.submit()">
                        <option value="">-- Ver todos --</option>
                        <option value="AUTORIZADO" <?php if(@$_GET['estado']=='AUTORIZADO') echo 'selected'; ?>>Autorizado</option>
                        <option value="PRUEBAS" <?php if(@$_GET['estado']=='PRUEBAS') echo 'selected'; ?>>Pruebas</option>
                        <option value="RECIBIDO" <?php if(@$_GET['estado']=='RECIBIDO') echo 'selected'; ?>>Recibido</option>
                        <option value="ENTREGADO AL TECNICO" <?php if(@$_GET['estado']=='ENTREGADO AL TECNICO') echo 'selected'; ?>>Entregado al Técnico</option>
                        <option value="REQUERIDO" <?php if(@$_GET['estado']=='REQUERIDO') echo 'selected'; ?>>Requerido</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    <style>
        .estado-requerido           { background-color: #0d476a; color: #000; }
        .estado-autorizado          { background-color: #116191; color: #000; }
        .estado-compra              { background-color: #1887cb; color: #000; }
        .estado-recibido            { background-color: #1b94df; color: #000; }
        .estado-entregadoaltecnico { background-color: #1da1f2; color: #000; }
        .estado-pruebas             { background-color: #a6cff4; color: #000; }
        .estado-default             { background-color: #552626ff; color: #000; }
    </style>

    <!-- Tabla de historial -->
    <table class="table is-bordered is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th>ID</th>
                <th>ODS</th>
                <th>Producto</th>
                <th>Stock</th>
                <th>Refacción</th>
                <th>Descripción</th>
                <th>Asesor</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
                use app\models\mainModel;
                $db = mainModel::conectar();

                // Filtro de estado
                $filtro = "";
                if (isset($_GET['estado']) && $_GET['estado'] != "") {
                    $estado = mainModel::limpiarCadena($_GET['estado']);
                    $filtro = "WHERE r.estado = '$estado'";
                }

                // Consulta con filtro
                $query = $db->query("SELECT r.*, p.Nombre AS nombre_asesor 
                                     FROM refacciones r 
                                     LEFT JOIN personal p ON r.IdAsesor = p.Idasesor
                                     $filtro
                                     ORDER BY r.IdRefaccion DESC");

                // Función para obtener la clase de color según el estado
                function claseColorEstado($estado) {
                    $estado = strtolower(trim($estado));
                    $estado = strtr($estado, [
                        'á' => 'a', 'é' => 'e', 'í' => 'i',
                        'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'
                    ]);
                    $estado = str_replace([' ', '-', '_'], '', $estado);

                    return match($estado) {
                        'requerido'           => 'estado-requerido',
                        'autorizado'          => 'estado-autorizado',
                        'pruebas'             => 'estado-pruebas',
                        'recibido'            => 'estado-recibido',
                        'entregadoaltecnico'  => 'estado-entregadoaltecnico',
                        default               => 'estado-default'
                    };
                }

                // Mostrar los datos en la tabla
                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                    $estado = $row['estado'] ?: 'Sin estado';
                    $clase_estado = claseColorEstado($estado);
            ?>
                <tr>
                    <td><?php echo $row['IdRefaccion']; ?></td>
                    <td><?php echo $row['IdODS']; ?></td>
                    <td><?php echo $row['producto']; ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td><?php echo $row['refaccion']; ?></td>
                    <td><?php echo $row['descripcion']; ?></td>
                    <td><?php echo $row['nombre_asesor']; ?></td>
                    <!-- Asegurarse de que la clase CSS se aplique correctamente al <span> -->
                    <td><span class="tag <?php echo $clase_estado; ?>"><?php echo strtoupper(htmlspecialchars($estado)); ?></span></td>
                </tr>
            <?php
                }
            ?>
        </tbody>
    </table>
</div>
