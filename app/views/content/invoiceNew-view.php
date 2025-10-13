<div class="container is-fluid mb-1">
	<h1 class="title">Facturas</h1>
	<h2 class="subtitle"><i class="fas fa-male fa-fw"></i> &nbsp; Nueva factura</h2>
</div>

<div class="container pb-2 pt-1">
	<?php
		use app\controllers\invoiceController;
		$insInvoice = new invoiceController();
		echo $insInvoice->formNuevaFacturaControlador(); 
	?>
</div>
