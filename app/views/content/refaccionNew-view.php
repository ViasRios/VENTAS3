<div class="box pastel-purpled">
    <h2 class="subtitle"><i class="fas fa-clipboard-check"></i> Solicitudes de Refacciones Pendientes</h2>

    <div class="level mb-4">
        <div class="level-left">
            <a href="<?php echo APP_URL; ?>refaccionHistorial/" class="button is-link is-small">
                <i class="fas fa-history"></i>&nbsp; Ver Historial de Solicitudes
            </a>
        </div>
        <div class="level-right">
            <a href="<?php echo APP_URL; ?>refaccionEstado/" class="button is-link is-small">
                <i class="fas fa-history"></i>&nbsp; Ver Refacción por Estado
            </a>
        </div>
    </div>

    <table class="table is-bordered is-fullwidth is-striped is-hoverable">
        <thead>
            <tr>
                <th>Ver Petición</th>
                <th>ODS</th>
                <th>Producto</th>
                <th>Asesor</th>
                <th>Stock</th>
                <th>Refacción</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
                use app\models\mainModel;
                $db = mainModel::conectar();
                // Mostrar solo las refacciones pendientes y donde 'IdAsesor' no esté vacío o nulo
                $query = $db->query("SELECT r.*, p.Nombre AS nombre_asesor 
                                    FROM refacciones r 
                                    LEFT JOIN personal p ON r.IdAsesor = p.Idasesor
                                    WHERE (r.autorizacion IS NULL OR r.autorizacion = '')
                                    AND r.IdAsesor IS NOT NULL 
                                    AND r.IdAsesor != ''
                                    ORDER BY r.IdRefaccion DESC");
                    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
					echo "<tr style='background-color: #fdf192ff;'>
                        <td>
                                <br>
                                <a href='" . APP_URL . "peticionRefaccion/{$row['IdODS']}/' class='button is-small is-link' title='Ver Peticion'>
                                <i class='fas fa-eye'></i>
                                </a>
                        </td>
                        <td> 
                                {$row['IdODS']}
                                <a href='" . APP_URL . "odsView/{$row['IdODS']}/' class='button is-small is-link' title='Ver ODS'>
                                <i class='fas fa-eye'></i>
                                </a>
                        </td>
                        <td>{$row['producto']}</td>
                        <td>{$row['nombre_asesor']}</td>
                        <td>{$row['stock']}</td>
                        <td>" . ($row['refaccion'] ? 'Sí' : 'No') . "</td>
                        <td>{$row['descripcion']}</td>
                        <td><strong>{$row['estado']}</strong></td>
                        
                        <td>
                            <button class='button is-small is-success autorizar-btn' data-id='{$row['IdRefaccion']}'>
                                <i class='fas fa-check'></i>
                            </button>
                            <button class='button is-small is-danger cancelar-btn' data-id='{$row['IdRefaccion']}'>
                                <i class='fas fa-times'></i>
                            </button>
                        </td>
                    </tr>";
                }
            ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const cancelBtns = document.querySelectorAll('.cancelar-btn');

  cancelBtns.forEach((btn) => {
    btn.addEventListener('click', async function () {
      const refaccionId = this.getAttribute('data-id');
      if (!refaccionId) return;

      if (!confirm("¿Estás seguro de que deseas eliminar esta refacción?")) return;

      // evita doble clic durante el fetch
      if (this.dataset.sending === '1') return;
      this.dataset.sending = '1';
      this.disabled = true;

      try {
        const formData = new FormData();
        formData.append('modulo_refaccion', 'eliminar'); // <-- módulo correcto
        formData.append('IdRefaccion', refaccionId);     // <-- clave consistente

        const resp = await fetch('<?php echo APP_URL; ?>app/ajax/refaccionAjax.php', { // <-- endpoint correcto
          method: 'POST',
          body: formData,
          credentials: 'include',
        });

        const data = await resp.json();
        if (data.ok || data.success) {
          this.closest('tr')?.remove();
        } else {
          alert(data.error || 'Hubo un problema al eliminar la refacción.');
        }
      } catch (err) {
        console.error('Error al eliminar:', err);
        alert('Hubo un error al intentar eliminar la refacción.');
      } finally {
        // permitir otro intento si falló
        this.disabled = false;
        this.dataset.sending = '0';
      }
    }, { once: false });
  });
});
</script>
