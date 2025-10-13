<div class="container is-fluid mb-2">
    <div class="level">
        <div class="level-left">
            <div>
                <h1 class="title">Caja</h1>
                <h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Resumen Caja</h2>
            </div>
        </div>
        <div class="level-right is-flex is-flex-direction-column">
			<button class="button is-link is-rounded btn-back mb-2" onclick="window.location.href='/VENTAS3/odsNew'">
				<i class="fas fa-ticket-alt"></i> &nbsp; Ticket Salida
			</button>
			<button class="button is-success is-rounded btn-back mb-2" onclick="window.location.href='/VENTAS3/invoiceList'">
				<i class="fas fa-file-invoice-dollar"></i> &nbsp; Facturas
			</button>
		</div>
    </div>
</div>

<div class="container pb-2 pt-2">
	<div class="form-rest mb-2 mt-2"></div>
	<?php
		use app\controllers\cashierController;

		$insCaja = new cashierController();

		echo $insCaja->listarCajaControlador($url[1],15,$url[0],"");
	?>
</div>