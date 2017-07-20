<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="refresh" content="60">
	<title>Telegram Bot</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url($css.'bootstrap.min.css')?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url($css.'bootstrap-theme.min.css')?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url($css.'jquery.dataTables.min.css')?>">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url($css.'dataTables.bootstrap.min.css')?>">
</head>
<body>
	<div class="container" style="padding-top: 50px;">
		<div class="row">
			<div class="col-xs-9 col-md-9 col-lg-9">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Telegram Message</h3>
					</div>
					<div class="panel-body">
						<table id="message-table" class="display" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th>MESSAGE ID</th>
									<th>MESSAGE CONTENT</th>
									<th>DATE</th>
									<th>TICKET ID</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th>MESSAGE ID</th>
									<th>MESSAGE CONTENT</th>
									<th>DATE</th>
									<th>TICKET ID</th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>
			<div class="col-xs-3 col-md-3 col-lg-3">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">Pooling Status Report</h3>
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-xs-12" id="last-update-status">
								<h4>
									<?php echo date("D, m/Y H:i:s") ?>
								</h4>
							</div>
						</div>
					</div>
				</div>
			</div>	
		</div>
	</div>
</body>
<footer>
	<script type="text/javascript" src="<?php echo base_url($js.'jquery-3.2.1.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo base_url($js.'bootstrap.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo base_url($js.'jquery.dataTables.min.js'); ?>"></script>
	<script type="text/javascript" src="<?php echo base_url($js.'dataTables.bootstrap.min.js'); ?>"></script>
	<script type="text/javascript">
		$(function(){
			message_table = $('#message-table').DataTable({
				ajax: 'welcome/get_telegram_message',
				pageLength : 5,
				order: [ [2, 'desc']]
			});
		});
	</script>
</footer>
</html>
