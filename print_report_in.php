<?php
include "header.php";

// if session is null, showing up the text and exit
if ($_SESSION['staffID'] == '' && $_SESSION['email'] == '')
{
	// show up the text and exit
	echo "You have not authorization for access the modules.";
	exit();
}

else{
	
	ob_start();
	require ("includes/html2pdf/html2pdf.class.php");
	
	$act = $_GET['act'];
	$q = mysqli_real_escape_string($connect, $_GET['q']);
	$sDate = mysqli_real_escape_string($connect, $_GET['startDate']);
	$eDate = mysqli_real_escape_string($connect, $_GET['endDate']);
	
	$s2Date = explode("-", $sDate);
	$e2Date = explode("-", $eDate);
	
	$startDate = $s2Date[2]."-".$s2Date[1]."-".$s2Date[0];
	$endDate = $e2Date[2]."-".$e2Date[1]."-".$e2Date[0];
	
	if ($act == 'print')
	{
		$now = date('Y-m-d');
		
		$filename="laporan_pembelian.pdf";
		$content = ob_get_clean();
		
		$content = "<table style='padding-bottom: 10px; width: 240mm;'>
						<tr valign='top'>
							<td style='width: 150mm;' valign='middle'>
								<div style='font-weight: bold; padding-bottom: 5px; font-size: 12pt;'>
									PT. LOTTE MART INDONESIA CABANG MAKASSAR
								</div>
							</td>
							<td style='width: 83mm;'></td>
						</tr>
						<tr valign='top'>
							<td><span style='font-size: 8pt;'>Mall Panakukang, Jl.Boulevard Raya No.1<br>Makassar, Sulawesi Selatan, Indonesia
								</span>
							</td>
							<td>
								<span style='font-size: 11pt;'><b>LAPORAN TRANSAKSI PEMBELIAN</b><br>Periode : $_GET[startDate] s/d $_GET[endDate]</span>
							</td>
						</tr>
					</table>
					<table cellpadding='0' cellspacing='0' style='width: 290mm;'>
						<tr>
							<th style='width: 10mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>NO.</th>
							<th style='width: 23mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>NO FAKTUR</th>
							<th style='width: 23mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>NO PO</th>
							<th style='width: 22mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>TGL</th>
							<th style='width: 10mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>NO</th>
							<th style='width: 80mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>NAMA BARANG</th>
							<th style='width: 22mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>HARGA</th>
							<th style='width: 20mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>QTY</th>
							<th style='width: 25mm; padding: 5px 0px 5px 0px; font-size: 9pt; border-top: 1px solid #000; border-bottom: 1px solid #000;'>SUBTOTAL</th>
						</tr>";
						
						// showing the buy invoice
						if ($_GET['startDate'] != '' || $_GET['endDate'] != '')
						{
							$queryBuy = "SELECT * FROM faktur_beli WHERE tglfaktur BETWEEN '$startDate' AND '$endDate' ORDER BY nofaktur DESC";
						}
						else
						{
							$queryBuy = "SELECT * FROM faktur_beli ORDER BY nofaktur DESC";
						}
						$sqlBuy = mysqli_query($connect, $queryBuy);
						
						// fetch data
						$i = 1;
						while ($dtBuy = mysqli_fetch_array($sqlBuy))
						{
							$tglfaktur = tgl_indo2($dtBuy['tglfaktur']);
							
							// showing up the detail bbm
							$queryDetail = "SELECT * FROM detail_fakturbeli WHERE nofaktur = '$dtBuy[nofaktur]'";
							$sqlDetail = mysqli_query($connect, $queryDetail);
							
							$content .= "
									<tr valign='top'>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$i</td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$dtBuy[nofaktur]</td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$dtBuy[spbNo]</td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$tglfaktur</td>
										<td colspan='4'></td>
									</tr>
							";
							
							// fetch data
							$k = 1;
							$sum = array();
							while ($dtDetail = mysqli_fetch_array($sqlDetail))
							{
								$price = rupiah($dtDetail['price']);
								$subt = $dtDetail['price'] * $dtDetail['qty'];
								$sum[] = $subt;
								$subtotal = rupiah($subt);
								
								$grand = array_sum($sum);
								
								$content .= "
									<tr valign='top'>
										<td colspan='4'></td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$k</td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$dtDetail[productName]</td>
										<td style='padding: 2px 25px 2px 0px; font-size: 9pt; '>$price</td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt;'>$dtDetail[qty]</td>
										<td style='padding: 2px 0px 2px 0px; font-size: 9pt; text-align: right;'>$subtotal</td>
									</tr>
								";
								$k++;
							}
							$grandtotal = rupiah($grand);
							$content .= "<tr valign='top'>
											<td colspan='8' style='text-align: right;'><br><b>Total</b><br></td>
											<td style='padding: 2px 0px 2px 0px; font-size: 9pt; text-align: right;'><br>$grandtotal<br></td>
										</tr>";
							$i++;
						}
			$content .= 
						"
						
					</table>
				
					";
	}
	
	ob_end_clean();
	// conversion HTML => PDF
	try
	{
		$html2pdf = new HTML2PDF('L', array('240', '130'),'fr', false, 'ISO-8859-15',array(2, 2, 2, 2)); //setting ukuran kertas dan margin pada dokumen anda
		// $html2pdf->setModeDebug();
		$html2pdf->setDefaultFont('Arial');
		$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
		$html2pdf->Output($filename);
	}
	catch(HTML2PDF_exception $e) { echo $e; }
}
?>