<?php
/************************************************************************************************
 *
 *	CLEANER CLASS: DOING THE REAL CLEANING / OPTIMIZATION
 *
 ************************************************************************************************/
?>
<?php
class ODB_Cleaner {
	var $start_size;
	var $nr_of_optimized_tables;


	/********************************************************************************************
	 *	CONSTRUCTOR
	 ********************************************************************************************/	
    function __construct() {
	} // __construct()
	 

	/********************************************************************************************
	 *	RUN CLEANER
	 ********************************************************************************************/
	function odb_run_cleaner($scheduler, $analyzing = false) {
		global $odb_class;

		if(!$scheduler) {
			if ($analyzing) {
				echo '
		  <div id="odb-cleaner" class="odb-padding-left">
			<div class="odb-title-bar">
			  <h2>'.__('Analyzing Database', $odb_class->odb_txt_domain).'</h2>
			</div>
			<br>
			<br>
				';
			} else {
				echo '
		  <div id="odb-cleaner" class="odb-padding-left">
			<div class="odb-title-bar">
			  <h2>'.__('Cleaning Database', $odb_class->odb_txt_domain).'</h2>
			</div>
			<br>
			<br>
				';
			}
		} // if(!$scheduler)
		
		// GET THE SIZE OF THE DATABASE BEFORE OPTIMIZATION
		$this->start_size = $odb_class->odb_utilities_obj->odb_get_db_size();
	
		// TIMESTAMP FOR LOG FILE - v4.6
		$ct = ($scheduler) ? ' (cron)' : '';

		$odb_class->log_arr["timestamp"]  = current_time('YmdHis', 0);
		$odb_class->log_arr["after"]      = 0;
		$odb_class->log_arr["before"]     = 0;
		$odb_class->log_arr["orphans"]    = 0;
		$odb_class->log_arr["pingbacks"]  = 0;
		$odb_class->log_arr["oembeds"]    = 0;
		$odb_class->log_arr["orphans"]    = 0;		
		$odb_class->log_arr["revisions"]  = 0;
		$odb_class->log_arr["savings"]    = 0;
		$odb_class->log_arr["spam"]       = 0;
		$odb_class->log_arr["tables"]     = 0;
		$odb_class->log_arr["tags"]       = 0;
		$odb_class->log_arr["transients"] = 0;
		$odb_class->log_arr["trash"]      = 0;	
	
		/****************************************************************************************
		 *	DELETE REVISIONS
		 ****************************************************************************************/
		if($odb_class->odb_rvg_options['delete_older'] == 'Y' || $odb_class->odb_rvg_options['rvg_revisions'] == 'Y') {
			// FIND REVISIONS
			$results_older_than = array();
			if($odb_class->odb_rvg_options['delete_older'] == 'Y') {
				$results_older_than = $this->odb_get_revisions_older_than();
			}

			$results_keep_revisions = array();
			if($odb_class->odb_rvg_options['rvg_revisions'] == 'Y') {
				$results_keep_revisions = $this->odb_get_revisions_keep_revisions();
			}
		
			$total_deleted = 0;
			if(count($results_older_than) > 0 || count($results_keep_revisions) > 0) {
				// WE HAVE REVISIONS TO DELETE!
				if(!$scheduler) {
					if ($analyzing) {
						$msg1 = __('REVISIONS','rvg-optimize-database');
						$msg2 = __('revisions', $odb_class->odb_txt_domain);
					} else {
						$msg1 = __('DELETED REVISIONS','rvg-optimize-database');
						$msg2 = __('revisions deleted', $odb_class->odb_txt_domain);
					}
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
	  <tr>
		<td colspan="4"><div class="odb-found"><?php echo $msg1?></div></td>
	  </tr>
	  <tr>
		<th align="right" class="odb-border-bottom">#</th>
		<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('post / page', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('revision date', $odb_class->odb_txt_domain);?></th>
		<th align="right" class="odb-border-bottom"><?php echo $msg2?></th>
	  </tr>
		  <?php
                } // if(!$scheduler)
                
                // LOOP THROUGH THE REVISIONS AND DELETE THEM
                $total_deleted = $this->odb_delete_revisions($scheduler, $analyzing);
                
                if(!$scheduler) {
					if ($analyzing) {
						$msg1 = __('total number of revisions', $odb_class->odb_txt_domain);
					} else {
						$msg1 = __('total number of revisions deleted', $odb_class->odb_txt_domain);
					} // if (!$analyzing)
            ?>
	  <tr>
		<td colspan="5" align="right" class="odb-border-top odb-bold"><?php echo $msg1 ?>: <?php echo $total_deleted?></td>
	  </tr>
	</table>
		<?php
                } // if(!$scheduler)
            } else {
				if(!$scheduler) {
        ?>
	<div class="odb-not-found">
	  <?php _e('No REVISIONS found to delete', $odb_class->odb_txt_domain);?>
	</div>
		<?php
                } // if(!$scheduler)
            } // if(count($results)>0)
            
            // NUMBER OF DELETED REVISIONS FOR LOG FILE
            $odb_class->log_arr["revisions"] = $total_deleted;
		} // if($odb_class->odb_rvg_options['delete_older'] == 'Y' || $odb_class->odb_rvg_options['rvg_revisions'] == 'Y')

	
		/****************************************************************************************
		 *	DELETE TRASHED ITEMS
		 ****************************************************************************************/
		if($odb_class->odb_rvg_options['clear_trash'] == 'Y') {
			// GET TRASHED POSTS / PAGES AND COMMENTS
			$results = $this->odb_get_trash($analyzing);
	
			$total_deleted = 0;		
			if(count($results)>0) {
				// WE HAVE TRASH TO DELETE!
				if(!$scheduler) {
					if ($analyzing) {
						$msg1 = __('TRASHED ITEMS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of trashed items', $odb_class->odb_txt_domain);					
					} else {
						$msg1 = __('DELETED TRASHED ITEMS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of trashed items deleted', $odb_class->odb_txt_domain);
					} // if ($analyzing)
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
	  <tr>
		<td colspan="4"><div class="odb-found">
			<?php echo $msg1 ?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="odb-border-bottom">#</th>
		<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('type', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('IP address / title', $odb_class->odb_txt_domain);?></th>
		<th align="left" nowrap="nowrap" class="odb-border-bottom"><?php _e('date', $odb_class->odb_txt_domain);?></th>
	  </tr>
	  <?php
				} // if(!$scheduler)
	  
				// LOOP THROUGH THE TRASHED ITEMS AND DELETE THEM
				$total_deleted = $this->odb_delete_trash($results, $scheduler, $analyzing);
				
				if(!$scheduler) {
	?>
	  <tr>
		<td colspan="5" align="right" class="odb-border-top odb-bold"><?php echo $msg2 ?>: <?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if(!$scheduler)
			} else {
				if(!$scheduler) {
	?>
	<div class="odb-not-found">
	  <?php _e('No TRASHED ITEMS found to delete', $odb_class->odb_txt_domain);?>
	</div>
	<?php
				} // if(!$scheduler)
			} // if(count($results)>0)
			
			// NUMBER OF DELETED TRASH FOR LOG FILE
			$odb_class->log_arr["trash"] = $total_deleted;
		} // if($odb_class->odb_rvg_options['clear_trash'] == 'Y')
	
	
		/****************************************************************************************
		 *	DELETE SPAMMED ITEMS
		 ****************************************************************************************/
		if($odb_class->odb_rvg_options['clear_spam'] == 'Y') {
			// GET SPAMMED COMMENTS
			$results = $this->odb_get_spam($analyzing);		
	
			$total_deleted = 0;		
			if(count($results)>0) {
				// WE HAVE SPAM TO DELETE!
				if (!$scheduler) {
					if ($analyzing) {
						$msg1 = __('SPAMMED ITEMS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of spammed items', $odb_class->odb_txt_domain);
					} else {
						$msg1 = __('DELETED SPAMMED ITEMS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of spammed items deleted', $odb_class->odb_txt_domain);
					} // if ($analyzing)
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
	  <tr>
		<td colspan="4"><div class="odb-found">
			<?php echo $msg1 ?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="odb-border-bottom">#</th>
		<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('comment author', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('comment author email', $odb_class->odb_txt_domain);?></th>
		<th align="left" nowrap="nowrap" class="odb-border-bottom"><?php _e('comment date', $odb_class->odb_txt_domain);?></th>
	  </tr>
	  <?php
				} // if (!$scheduler)
	  
				// LOOP THROUGH SPAMMED ITEMS AND DELETE THEM
				$total_deleted = $this->odb_delete_spam($results, $scheduler, $analyzing);
				
				if (!$scheduler) {
	?>
	  <tr>
		<td colspan="5" align="right" class="odb-border-top odb-bold"><?php echo $msg2 ?>: <?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if (!$scheduler)
			} else{
				if (!$scheduler) {
	?>
	<div class="odb-not-found">
	  <?php _e('No SPAMMED ITEMS found to delete', $odb_class->odb_txt_domain);?>
	</div>
	<?php
				} // if (!$scheduler)
			} // if(count($results)>0)

			// NUMBER OF SPAM DELETED FOR LOG FILE
			$odb_class->log_arr["spam"] = $total_deleted;			
		} // if($odb_class->odb_rvg_options['clear_spam'] == 'Y')
	
	
		/****************************************************************************************
		 *	DELETE UNUSED TAGS
		 ****************************************************************************************/
		if($odb_class->odb_rvg_options['clear_tags'] == 'Y') {
			// GET UNUSED TAGS
			$results = $this->odb_get_unused_tags($analyzing);
			
			$total_deleted = 0;
			
			if(count($results)>0) {
				// WE HAVE TAGS TO DELETE!
				if (!$scheduler) {
					if ($analyzing) {
						$msg1 = __('UNUSED TAGS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of unused tags', $odb_class->odb_txt_domain);						
					} else {
						$msg1 = __('DELETED UNUSED TAGS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of unused tags deleted', $odb_class->odb_txt_domain);						
					}
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
	  <tr>
		<td colspan="4"><div class="odb-found">
			<?php echo $msg1 ?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="odb-border-bottom">#</th>
		<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('tag', $odb_class->odb_txt_domain);?></th>
	  </tr>
<?php
				} // if (!$scheduler)

				// LOOP THROUGH UNUSED TAGS AND DELETE THEM
				$total_deleted = $this->odb_delete_unused_tags($results, $scheduler, $analyzing);

				if (!$scheduler) {
	?>
	  <tr>
		<td colspan="5" align="right" class="odb-border-top odb-bold"><?php echo $msg2 ?>: <?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if (!$scheduler)
			} else {
				if (!$scheduler) {
	?>
	<div class="odb-not-found">
	  <?php _e('No UNUSED TAGE found to delete', $odb_class->odb_txt_domain);?>
	</div>
	<?php
				} // if (!$scheduler)
			} // if(count($results)>0)

			// NUMBER OF SPAM DELETED FOR LOG FILE
			$odb_class->log_arr["tags"] = $total_deleted;			
		} // if($odb_class->odb_rvg_options['clear_spam'] == 'Y')      
	
	
		/****************************************************************************************
		 *	DELETE EXPIRED TRANSIENTS
		 ****************************************************************************************/
		if ($odb_class->odb_rvg_options['clear_transients'] !== 'N') {
			// DELETE TRAMSIENTS ENABLED
			$total_deleted = 0;
			
			if (!$scheduler) {
				// NOT VIA SCHEDULER
				if ($odb_class->odb_rvg_options['clear_transients'] == 'Y') {
					// EXPRIRED TRANSIENTS ONLY
					if ($analyzing){
						$msg1 = __('EXPIRED TRANSIENTS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of expired transients', $odb_class->odb_txt_domain);
					} else {
						$msg1 = __('EXPIRED TRANSIENTS DELETED', $odb_class->odb_txt_domain);
						$msg2 = __('total number of expired transients deleted', $odb_class->odb_txt_domain);						
					} // if ($analyzing)
				} else {
					// ALL TRANSIENTS
					if ($analyzing){
						$msg1 = __('TRANSIENTS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of transients', $odb_class->odb_txt_domain);
					} else {
						$msg1 = __('TRANSIENTS DELETED', $odb_class->odb_txt_domain);
						$msg2 = __('total number of transients', $odb_class->odb_txt_domain);					
					} // if ($analyzing
				} // if ($odb_class->odb_rvg_options['clear_transients'] == 'Y')
				$res = $this->odb_delete_transients($scheduler, $analyzing);
				$total_deleted = count($res);
				if ($total_deleted > 0) {
?>
<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
  <tr>
	<td colspan="4"><div class="odb-found">
		<?php echo $msg1 ?>
	  </div></td>
  </tr>
  <tr>
	<th align="right" class="odb-border-bottom">#</th>
	<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
	<th align="left" class="odb-border-bottom"><?php _e('option name', $odb_class->odb_txt_domain);?></th>
  </tr>
  <?php
				// LOOP THROUGH TRANSIENS ITEMS AND DELETE THEM
				$nr = 1;
				for ($j = 0; $j < $total_deleted; $j++) {
?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $res[$j]['site']?></td>
	  <td valign="top"><?php echo $res[$j]['option_name']; ?></td>
	</tr>	
<?php
					$nr++;
				} // for ($j = 0; $j < count($res);$j++) {
?>
  <tr>
	<td colspan="3" align="right" class="odb-border-top odb-bold"<?php echo $msg2 ?>: <?php echo $total_deleted?></td>
  </tr>
</table>
  <?php						
				} else {
?>					
<div class="odb-not-found">
<?php
				if ($odb_class->odb_rvg_options['clear_transients'] == 'Y') {
					$msg = _e('No EXPIRED TRANSIENTS found to delete', $odb_class->odb_txt_domain);
				} else {
					$msg = _e('No TRANSIENTS found to delete', $odb_class->odb_txt_domain);
				}
?>						
</div>
<?php		
				}
			} else {
				return;
			} // if (!$scheduler)
		} // if($odb_class->odb_rvg_options['clear_transients'] != 'N')
	
		/****************************************************************************************
		 *	DELETE PINGBACKS AND TRACKBACKS
		 ****************************************************************************************/	
		if($odb_class->odb_rvg_options['clear_pingbacks'] == 'Y') {
			// GET PINGBACKS AND TRACKBACKS
			$results = $this->odb_get_pingbacks($analyzing);
	
			$total_deleted = 0;		
			if(count($results)>0) {
				// WE HAVE TRASH TO DELETE!
				if(!$scheduler) {
					if ($analyzing) {
						$msg1 = __('PINGBACKS AND TRACKBACKS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of pingbacks and trackbacks', $odb_class->odb_txt_domain);						
					} else {
						$msg1 = __('DELETED PINGBACKS AND TRACKBACKS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of pingbacks and trackbacks deleted', $odb_class->odb_txt_domain);
					} // if ($analyzing)
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
	  <tr>
		<td colspan="4"><div class="odb-found">
			<?php echo $msg1 ?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="odb-border-bottom">#</th>
		<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('type', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('comment_author', $odb_class->odb_txt_domain);?></th>
		<th align="left" nowrap="nowrap" class="odb-border-bottom"><?php _e('date', $odb_class->odb_txt_domain);?></th>
	  </tr>
	  <?php
				} // if(!$scheduler)
	  
				// LOOP THROUGH THE TRASHED ITEMS AND DELETE THEM
				$total_deleted = $this->odb_delete_pingbacks($results, $scheduler, $analyzing);
				
				if(!$scheduler) {
	?>
	  <tr>
		<td colspan="5" align="right" class="odb-border-top odb-bold"><?php echo $msg2 ?>: <?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if(!$scheduler)
			} else {
				if(!$scheduler) {
	?>
	<div class="odb-not-found">
	  <?php _e('No PINGBACKS nor TRACKBACKS found to delete', $odb_class->odb_txt_domain);?>
	</div>
	<?php
				} // if(!$scheduler)
			} // if(count($results)>0)
			
			// NUMBER OF DELETED PINGBACKS FOR LOG FILE
			$odb_class->log_arr["pingbacks"] = $total_deleted;	
		} // if($odb_class->odb_rvg_options['clear_pingbacks'] == 'Y')


		/****************************************************************************************
		 *	DELETE OEMBED CACHE
		 ****************************************************************************************/	
		if($odb_class->odb_rvg_options['clear_oembed'] == 'Y') {
			
			// GET OEMBED CACHE
			$results = $this->odb_get_oembed($analyzing);
	
			$total_deleted = 0;		
			if(count($results)>0) {
				// WE HAVE TRASH TO DELETE!
				if(!$scheduler) {
					if ($analyzing) {
						$msg1 = __('OEMBED CACHE', $odb_class->odb_txt_domain);
						$msg2 = __('total number of oEmbeds', $odb_class->odb_txt_domain);						
					} else {
						$msg1 = __('DELETED OEMBEDS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of oEmbeds deleted', $odb_class->odb_txt_domain);
					} // if ($analyzing)
	?>
	<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
	  <tr>
		<td colspan="4"><div class="odb-found">
			<?php echo $msg1 ?>
		  </div></td>
	  </tr>
	  <tr>
		<th align="right" class="odb-border-bottom">#</th>
		<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('meta key', $odb_class->odb_txt_domain);?></th>
		<th align="left" class="odb-border-bottom"><?php _e('meta value', $odb_class->odb_txt_domain);?></th>
	  </tr>
	  <?php
				} // if(!$scheduler)
	  
				// LOOP THROUGH THE TRASHED ITEMS AND DELETE THEM
				$total_deleted = $this->odb_delete_oembed($results, $scheduler, $analyzing);
				
				if(!$scheduler) {
	?>
	  <tr>
		<td colspan="4" align="right" class="odb-border-top odb-bold"><?php echo $msg2 ?>: <?php echo $total_deleted?></td>
	  </tr>
	</table>
	<?php
				} // if(!$scheduler)
			} else {
				if(!$scheduler) {
	?>
	<div class="odb-not-found">
	  <?php _e('No OEMED CHACHE found to delete', $odb_class->odb_txt_domain);?>
	</div>
	<?php
				} // if(!$scheduler)
			} // if(count($results)>0)
			
			// NUMBER OF DELETED PINGBACKS FOR LOG FILE
			$odb_class->log_arr["oembeds"] = $total_deleted;
		} // if($odb_class->odb_rvg_options['clear_oembed'] == 'Y')

	
		/****************************************************************************************
		 *	DELETE ORPHANS
		 ****************************************************************************************/
		if($odb_class->odb_rvg_options['clear_orphans'] == 'Y') {
			$results = $this->odb_get_orphans($results, $scheduler,$analyzing);
	
			$total_deleted = count($results);
			
			//$total_deleted = $this->odb_delete_orphans($analyzing);
			
			if($total_deleted > 0) {
				if (!$scheduler) {
					if ($analyzing) {
						$msg1 = __('POSTMETA ORPHANS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of orphans', $odb_class->odb_txt_domain);
					} else {
						$msg1 = __('DELETED POSTMETA ORPHANS', $odb_class->odb_txt_domain);
						$msg2 = __('total number of orphans', $odb_class->odb_txt_domain);
					}
		?>
	<!--	<div class="odb-found-number">
		  <?php echo $msg1 ?>: <span class="odb-blue"><?php echo $total_deleted;?></span>
		</div>-->
		<table border="0" cellspacing="8" cellpadding="2" class="odb-result-table">
		  <tr>
			<td colspan="8"><div class="odb-found">
				<?php echo $msg1 ?>
			  </div></td>
		  </tr>
		  <tr>
			<th align="right" class="odb-border-bottom">#</th>
			<th align="left" class="odb-border-bottom"><?php _e('prefix', $odb_class->odb_txt_domain);?></th>
			<th align="left" class="odb-border-bottom"><?php _e('type', $odb_class->odb_txt_domain);?></th>
			<th align="left" class="odb-border-bottom"><?php _e('id', $odb_class->odb_txt_domain);?></th>
			<th align="left" class="odb-border-bottom"><?php _e('title', $odb_class->odb_txt_domain);?></th>
			<th align="left" nowrap="nowrap" class="odb-border-bottom"><?php _e('modified', $odb_class->odb_txt_domain);?></th>
			<th align="left" class="odb-border-bottom"><?php _e('meta key', $odb_class->odb_txt_domain);?></th>
			<th align="left" class="odb-border-bottom"><?php _e('meta value', $odb_class->odb_txt_domain);?></th>
		  </tr>
		  <?php
					} // if(!$scheduler)
		  
					// LOOP THROUGH THE TRASHED ITEMS AND DELETE THEM
					$total_deleted = $this->odb_delete_orphans($results, $scheduler, $analyzing);
					
					if(!$scheduler) {
		?>
		  <tr>
			<td colspan="8" align="right" class="odb-border-top odb-bold"><?php echo $msg2 ?> <?php echo $total_deleted?></td>
		  </tr>
		</table>      
		  
		<?php
				} // if (!$scheduler)
			} else {
				if (!$scheduler) {
		?>
		<div class="odb-not-found">
		  <?php _e('No POSTMETA ORPHANS found to delete', $odb_class->odb_txt_domain);?>
		</div>
		<?php
				} // if (!$scheduler)
			} // if($total_deleted > 0)
			// FOR LOG FILE
			$odb_class->log_arr["orphans"] = $total_deleted;
			
			if (!$scheduler) {
			?>
			</div><!-- /odb-cleaner -->
			<?php
			} // if (!$scheduler)
		} // if($odb_class->odb_rvg_options['clear_oembed'] == 'Y')
	} // odb_run_cleaner()


	/********************************************************************************************
	 *	RUN OPTIMIZER
	 ********************************************************************************************/	
	function odb_run_optimizer($scheduler) {
		global $odb_class;
	
		if(!$scheduler) {
?>
	<div class="odb-optimizing-table" class="odb-padding-left">
	  <div class="odb-title-bar">
		<h2><?php _e('Optimizing Database Tables', $odb_class->odb_txt_domain);?></h2>
	  </div>
	  <br>
	  <br>
	  <table border="0" cellspacing="8" cellpadding="2">
		<tr>
		  <th class="odb-border-bottom" align="right">#</th>
		  <th class="odb-border-bottom" align="left"><?php _e('table name', $odb_class->odb_txt_domain);?></th>
		  <th class="odb-border-bottom" align="left"><?php _e('optimization result', $odb_class->odb_txt_domain);?></th>
		  <th class="odb-border-bottom" align="left"><?php _e('engine', $odb_class->odb_txt_domain);?></th>
		  <th class="odb-border-bottom" align="right"><?php _e('table rows', $odb_class->odb_txt_domain);?></th>
		  <th class="odb-border-bottom" align="right"><?php _e('table size', $odb_class->odb_txt_domain);?></th>
		</tr>
		<?php
		} // if(!$scheduler)
		
		# OPTIMIZE THE DATABASE TABLES
		$this->nr_of_optimized_tables = $this->odb_optimize_tables($scheduler);
		
		if(!$scheduler) {
	?>
	  </table>
	</div><!-- /odb-optimizing-table -->	
<?php
		} // if(!$scheduler)
	} // odb_run_optimizer()


	/********************************************************************************************
	 *	CALCULATE AND DISPLAY SAVINGS
	 ********************************************************************************************/		
	function odb_savings($scheduler) {
		global $odb_class;
		global $odb_logger_obj;

		// NUMBER OF TABLES
		$odb_class->log_arr["tables"] = $this->nr_of_optimized_tables;
		// DATABASE SIZE BEFORE OPTIMIZATION
		$odb_class->log_arr["before"] = $odb_class->odb_utilities_obj->odb_format_size($this->start_size,3);
		// DATABASE SIZE AFTER OPTIMIZATION
		$end_size = $odb_class->odb_utilities_obj->odb_get_db_size();
		$odb_class->log_arr["after"] = $odb_class->odb_utilities_obj->odb_format_size($end_size,3);
		// TOTAL SAVING
		$odb_class->log_arr["savings"] = $odb_class->odb_utilities_obj->odb_format_size(($this->start_size - $end_size),3);
		
		// WRITE RESULTS TO LOG FILE - v4.6
		$odb_class->odb_logger_obj->odb_add_log($odb_class->log_arr);
	
		$total_savings = $odb_class->odb_rvg_options['total_savings'];
		$total_savings += ($this->start_size - $end_size);
		$odb_class->odb_rvg_options['total_savings'] = $total_savings;		
		
		$odb_class->odb_multisite_obj->odb_ms_update_option('odb_rvg_options', $odb_class->odb_rvg_options);
		
		if(!$scheduler) {	
	?>
    <div id="odb-savings" class="odb-padding-left">
	  <div class="odb-title-bar">
		<h2><?php _e('Savings', $odb_class->odb_txt_domain);?></h2>
	  </div>
	  <br>
	  <br>
	  <table border="0" cellspacing="8" cellpadding="2">
		<tr>
		  <th>&nbsp;</th>
		  <th class="odb-border-bottom"><?php _e('size of the database', $odb_class->odb_txt_domain);?></th>
		</tr>
		<tr>
		  <td align="right"><?php _e('BEFORE optimization', $odb_class->odb_txt_domain);?></td>
		  <td align="right" class="odb-bold"><?php echo $odb_class->odb_utilities_obj->odb_format_size($this->start_size,3); ?></td>
		</tr>
		<tr>
		  <td align="right"><?php _e('AFTER optimization', $odb_class->odb_txt_domain);?></td>
		  <td align="right" class="odb-bold"><?php echo $odb_class->odb_utilities_obj->odb_format_size($end_size,3); ?></td>
		</tr>
		<tr>
		  <td align="right" class="odb-bold"><?php _e('SAVINGS THIS TIME', $odb_class->odb_txt_domain);?></td>
		  <td align="right" class="odb-border-top odb-bold"><?php echo $odb_class->odb_utilities_obj->odb_format_size(($this->start_size - $end_size),3); ?></td>
		</tr>
		<tr>
		  <td align="right" class="odb-bold"><?php _e('TOTAL SAVINGS SINCE THE FIRST RUN', $odb_class->odb_txt_domain);?></td>
		  <td align="right" class="odb-border-top odb-bold"><?php echo $odb_class->odb_utilities_obj->odb_format_size($total_savings,3); ?></td>
		</tr>
	  </table>      
    </div><!-- /odb-savings -->
<?php
		} // if(!$scheduler)
	} // odb_savings()
	

	/********************************************************************************************
	 *	SHOW LOADING TIME
	 ********************************************************************************************/	
	function odb_done($analyze = false) {
		global $odb_class;
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		
		$total_time = round(($finish - $odb_class->odb_start_time), 4);
		?>
      <div id="odb-done" class="odb-padding-left">
		<div class="odb-title-bar">
		  <h2>
			<?php _e('DONE!', $odb_class->odb_txt_domain);?>
		  </h2>
		</div>
		<br>
		<br>
<?php
		if (!$analyze) {
?>
		<span class="odb-padding-left"><?php _e('Optimization took', $odb_class->odb_txt_domain)?>&nbsp;<strong><?php echo $total_time;?></strong>&nbsp;<?php _e('seconds', $odb_class->odb_txt_domain)?>.</span>
<?php			
		} // if ($analyze)
?>
		<?php
		// v4.5.1
		$odb_class->odb_last_run_seconds = $total_time;
		
		if($odb_class->odb_logger_obj->odb_log_count() > 0) {
		?>
<script>
function odb_confirm_delete() {
<?php
		// v4.6.2
		$msg = str_replace("'", "\'", __('Clear the log?', $odb_class->odb_txt_domain));
?>	
	if(confirm('<?php echo $msg?>')) {
		self.location = 'tools.php?page=rvg-optimize-database&action=clear_log'
		return;
	}
} // odb_confirm_delete()
</script>    
		<br><br>
		&nbsp;
		<input class="button odb-normal" type="button" name="view_log" value="<?php _e('View Log', $odb_class->odb_txt_domain);?>" onclick="self.location='tools.php?page=rvg-optimize-database&action=view_log'" />
		&nbsp;
		<input class="button odb-normal" type="button" name="clear_log" value="<?php _e('Clear Log', $odb_class->odb_txt_domain);?>" onclick="return odb_confirm_delete();" />
		<?php	
		} // if($odb_class->odb_logger_obj->odb_log_count() > 0)
?>
      </div><!-- /odb-done -->		
<?php	
	} // odb_done()


	/********************************************************************************************
	 *	GET REVISIONS (OLDER THAN x DAYS)
	 ********************************************************************************************/
	function odb_get_revisions_older_than() {
		global $odb_class, $wpdb;
		
		$res_arr = array();
		
		// CUSTOM POST TYPES (from v4.4)
		$rel_posttypes = $odb_class->odb_rvg_options['post_types'];
		$in = '';
		foreach ($rel_posttypes as $posttype => $value) {
			if ($value == 'Y') {
				if ($in != '') $in .= ',';
				$in .= "'" . $posttype . "'";
			} // if ($value == 'Y')
		} // foreach($rel_posttypes as $posttypes)
		
		$where = '';
		if($in != '') {
			$where = " AND p2.`post_type` IN ($in)";
		} else {
			// NO POST TYPES TO DELETE REVISIONS FOR... SKIP!
			return $res_arr;			
		} // if($in != '')

		$older_than = $odb_class->odb_rvg_options['older_than'];

		$index = 0;

		// LOOP THROUGH THE SITES (IF MULTI SITE)
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf("
			  SELECT '%s' AS site, 
			    p1.`ID`,
				p1.`post_parent`,
				p1.`post_title`,
				p1.`post_modified`
				FROM %sposts p1, %sposts p2
			   WHERE p1.`post_type`   = 'revision'
                 AND p1.`post_parent` = p2.ID			   
			         %s
				 AND p1.`post_modified` < date_sub(now(), INTERVAL %d DAY)
			ORDER BY UCASE(p1.`post_title`)	
			",
			$prefix,
			$prefix,
			$prefix,
			$where,
			$older_than);
			
			//echo 'OLDER: '.$sql.'<br>';
	
			$res = $wpdb->get_results($sql, ARRAY_A);
			
			for($j=0; $j<count($res); $j++) {
				if(isset($res[$j]) && !$this->odb_post_is_excluded($res[$j]['post_parent'])) {
					$res_arr[$index] = $res[$j];		
					$index++;
				} // if(isset($res[$j]) && !$this->odb_post_is_excluded($res[$j]['post_parent']))
			} // for($j=0; $j<count($res); $j++)
			
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)
		
		return $res_arr;
	} // odb_get_revisions_older_than()


	/********************************************************************************************
	 *	GET REVISIONS (KEEP MAX NUMBER OF REVISIONS)
	 ********************************************************************************************/	
	function odb_get_revisions_keep_revisions() {
		global $odb_class, $wpdb;
		
		$res_arr = array();
		
		// CUSTOM POST TYPES (from v4.4)
		$rel_posttypes = $odb_class->odb_rvg_options['post_types'];
		$in = '';
		foreach ($rel_posttypes as $posttype => $value) {
			if ($value == 'Y') {
				if ($in != '') $in .= ',';
				$in .= "'" . $posttype . "'";
			} // if ($value == 'Y')
		} // foreach($rel_posttypes as $posttypes)
		
		$where1 = '';
		if($in != '') {
			$where1 = " AND p2.`post_type` IN ($in)";
		} else {
			// NO POST TYPES TO DELETE REVISIONS FOR... SKIP!
			return $res_arr;
		} // if($in != '')
				
		// MAX NUMBER OF REVISIONS TO KEEP
		$max_revisions = $odb_class->odb_rvg_options['nr_of_revisions'];
		
		$index = 0;

		// SKIP REVISIONS THAT WILL BE DELETED BY THE 'OLDER THAN' OPTION
		$where2 = '';
		if($odb_class->odb_rvg_options['delete_older'] == 'Y') {
			$older_than = $odb_class->odb_rvg_options['older_than'];
			$where2 = 'AND p1.`post_modified` >= date_sub(now(), INTERVAL '.$older_than.' DAY)';
		}
		
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf ("
			  SELECT '%s' AS site,
			  	p1.`ID`, p1.`post_parent`, p1.`post_title`, COUNT(*) cnt
				FROM %sposts p1, %sposts p2
			   WHERE p1.`post_type` = 'revision'
                 AND p1.`post_parent` = p2.ID			   
                     %s
					 %s
			GROUP BY p1.`post_parent`
			  HAVING COUNT(*) > %d
			ORDER BY UCASE(p1.`post_title`)	
			",
			$prefix,
			$prefix,
			$prefix,
			$where1,
			$where2,
			$max_revisions);
			
			//echo 'KEEP: '.$sql.'<br>';
			
			$res = $wpdb->get_results($sql, ARRAY_A);
			for($j=0; $j<count($res); $j++) {
				if(isset($res[$j]) && !$this->odb_post_is_excluded($res[$j]['post_parent'])) {
					$res_arr[$index] = $res[$j];
					$index++;
				}
			} // for($j=0; $j<count($res); $j++)
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)
		
		return $res_arr;	
	} // odb_get_revisions_keep_revisions()


	/********************************************************************************************
	 *	DELETE THE REVISIONS
	 ********************************************************************************************/
	function odb_delete_revisions($scheduler, $analyzing = false) {
		global $odb_class, $wpdb;

		$total_deleted = 0;
		$nr = 1;

		if($odb_class->odb_rvg_options['delete_older'] == 'Y') {
			// DELETE REVISIONS OLDER THAN x DAYS
			$results    = $this->odb_get_revisions_older_than();
			$older_than = $odb_class->odb_rvg_options['older_than'];
			$total_deleted += count($results);
			
			for($i=0; $i<count($results); $i++) {
				if (!$scheduler) {
			?>
		<tr>
		  <td align="right" valign="top"><?php echo $nr?>.</td>
		  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
		  <td valign="top" class="odb-bold"><?php echo $results[$i]['post_title']?></td>
		  <td valign="top" class="odb-bold"><?php echo $results[$i]['post_modified']?></td><?php
				} // if (!$scheduler)

				if (!$analyzing) {
					$sql_delete = sprintf ("
					DELETE FROM %sposts
					 WHERE `ID` = %d
					", $results[$i]['site'], $results[$i]['ID']);
					
					$wpdb->get_results($sql_delete);
				} // if (!$analyzing)
				
				$nr++;
				if(!$scheduler) {
		?>
		  <td align="right" valign="top" class="odb-bold">1</td>
		</tr>
		<?php
				} // if(!$scheduler)				
			} // for($i=0; $i<count($results); $i++)			
		} // if($odb_class->odb_rvg_options['delete_older'] == 'Y')
		
		if($odb_class->odb_rvg_options['rvg_revisions'] == 'Y') {
			// KEEP MAX NUMBER OF REVISIONS
			$results       = $this->odb_get_revisions_keep_revisions();
			$max_revisions = $odb_class->odb_rvg_options['nr_of_revisions'];
			
			for($i=0; $i<count($results); $i++) {
				$nr_to_delete  = $results[$i]['cnt'] - $max_revisions;
				$total_deleted += $nr_to_delete;
					
				if (!$scheduler) {
			?>
		<tr>
		  <td align="right" valign="top"><?php echo $nr?>.</td>
		  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
		  <td valign="top" class="odb-bold"><?php echo $results[$i]['post_title']?></td>
		  <td valign="top"><?php
				} // if (!$scheduler)
				
				$sql_get_posts = sprintf( "
				  SELECT `ID`, `post_modified`
					FROM %sposts
				   WHERE `post_parent` = %d
					 AND `post_type`   = 'revision'
				ORDER BY `post_modified` ASC		
				", $results[$i]['site'], $results[$i]['post_parent']);
	
				$results_get_posts = $wpdb->get_results($sql_get_posts);
				
				for($j=0; $j<$nr_to_delete; $j++) {
					if(!$scheduler) echo $results_get_posts[$j]->post_modified.'<br>';
					if (!$analyzing) {
						$sql_delete = sprintf ("
						DELETE FROM %sposts
						 WHERE `ID` = %d
						", $results[$i]['site'], $results_get_posts[$j]->ID);
						
						$wpdb->get_results($sql_delete);
					} // if (!$analyzing)
				} // for($j=0; $j<$nr_to_delete; $j++)
				
				$nr++;
				if(!$scheduler) {
		?></td>
		  <td align="right" valign="top" class="odb-bold"><?php echo $nr_to_delete?> <?php _e('of', $odb_class->odb_txt_domain)?> <?php echo $results[$i]['cnt'];?></td>
		</tr>
		<?php
				} // if(!$scheduler)
			} // for($i=0; $i<count($results); $i++)
		} // if($odb_class->odb_rvg_options['rvg_revisions'] == 'Y')
		
		return $total_deleted;		
	} // function odb_delete_revisions()


	/********************************************************************************************
	 *	CHECK IF POST IS EXCLUDED BY A CUSTOM FIELD ('keep_revisions')
	 ********************************************************************************************/
	function odb_post_is_excluded($parent_id) {
		$keep_revisions = get_post_meta($parent_id, 'keep_revisions', true);
		return ($keep_revisions === 'Y');
	} // odb_post_is_exclude()


	/********************************************************************************************
	 *	GET TRASHED POSTS / PAGES AND COMMENTS
	 ********************************************************************************************/
	function odb_get_trash($analyzing = false) {
		global $wpdb, $odb_class;
		
		$res_arr = array();

		$index = 0;
		// LOOP TROUGH SITES
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf ("
			   SELECT '%s' AS site,
			   	`ID` AS id,
				'post' AS post_type,
				`post_title` AS title,
				`post_modified` AS modified
				 FROM %sposts
				WHERE `post_status` = 'trash'
			UNION ALL
			   SELECT '%s' AS site,
			   	`comment_ID` AS id,
				'comment' AS post_type,
				`comment_author_IP` AS title,
				`comment_date` AS modified
				 FROM %scomments
				WHERE `comment_approved` = 'trash'
			 ORDER BY post_type, UCASE(title)		
			", $prefix, $prefix, $prefix, $prefix);
			
			$res = $wpdb->get_results($sql, ARRAY_A);

			if($res != null) {
				$res_arr[$index] = $res[0];		
				$index++;
			} // if($res != null)	
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)
		
		return $res_arr;
	} // odb_get_trash()


	/********************************************************************************************
	 *	DELETE TRASHED POSTS AND PAGES
	 ********************************************************************************************/
	function odb_delete_trash($results, $scheduler, $analyzing) {
		global $wpdb;
	
		$nr = 1;
		$total_deleted = count($results);
		
		for($i=0; $i<$total_deleted; $i++) {
			if(!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['post_type']; ?></td>
	  <td valign="top"><?php echo $results[$i]['title']; ?></td>
	  <td valign="top" nowrap="nowrap"><?php echo $results[$i]['modified']; ?></td>
	</tr>
	<?php
			} // if(!$scheduler)
			
			if($results[$i]['post_type'] == 'comment') {
				// DELETE META DATA (IF ANY...)
				if (!$analyzing) {
					$sql_delete = sprintf ("
					DELETE FROM %scommentmeta
					 WHERE `comment_id` = %d
					", $results[$i]['site'], $results[$i]['id']);
					$wpdb->get_results($sql_delete);  
				} // if (!$analyzing)
			} // if($results[$i]['post_type'] == 'comment')
			
			if (!$analyzing) {
				// DELETE TRASHED POSTS / PAGES
				$sql_delete = sprintf ("
				DELETE FROM %sposts
				 WHERE `post_status` = 'trash'			
				", $results[$i]['site']);
				$wpdb->get_results($sql_delete);		
		
				// DELETE TRASHED COMMENTS
				$sql_delete = sprintf ("
				DELETE FROM %scomments
				 WHERE `comment_approved` = 'trash'
				", $results[$i]['site']);
				$wpdb->get_results($sql_delete);
			} // if (!$analyzing)
			
			$nr++;
		} // for($i=0; $i<count($results); $i++)
	
		return $total_deleted;
	} // odb_delete_trash()


	/********************************************************************************************
	 *	GET SPAMMED COMMENTS
	 ********************************************************************************************/
	function odb_get_spam($analyzing = false) {
		global $wpdb, $odb_class;
	
		$res_arr = array();

		// LOOP THROUGH SITES
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf ("
			  SELECT '%s' AS site,
			  	`comment_ID`,
				`comment_author`,
				`comment_author_email`,
				`comment_date`
				FROM %scomments
			   WHERE `comment_approved` = 'spam'
			ORDER BY UCASE(`comment_author`)
			", $prefix, $prefix);
			
			$res = $wpdb->get_results($sql, ARRAY_A);
	
			for($j = 0; $j < count($res); $j++) {
				array_push($res_arr, $res[$j]);
			} // for($j = 0; $j < count($res); $j++)
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)	
		return $res_arr;
	} // odb_get_spam()


	/********************************************************************************************
	 *	DELETE SPAMMED ITEMS
	 ********************************************************************************************/
	function odb_delete_spam($results, $scheduler, $analyzing = false) {
		global $wpdb;
	
		$nr = 1;
		$total_deleted = count($results);
		for($i=0; $i<count($results); $i++) {
			if (!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['comment_author']; ?></td>
	  <td valign="top"><?php echo $results[$i]['comment_author_email']; ?></td>
	  <td valign="top" nowrap="nowrap"><?php echo $results[$i]['comment_date']; ?></td>
	</tr>
	<?php
			} // if (!$scheduler)
			
			if (!$analyzing) {
				$sql_delete = sprintf ("
				DELETE FROM %scommentmeta
				 WHERE `comment_id` = %d
				", $results[$i]['site'], $results[$i]['comment_ID']);
				$wpdb->get_results($sql_delete);
				
				$sql_delete = sprintf ("
				DELETE FROM %scomments
				 WHERE `comment_approved` = 'spam'
				", $results[$i]['site']);
				$wpdb->get_results($sql_delete);
			} // if (!$analyzing)
	
			$nr++;				
		} // for($i=0; $i<count($results); $i++)
		
		return $total_deleted;
	} // odb_delete_spam()


	/********************************************************************************************
	 *	GET UNUSED TAGS
	 ********************************************************************************************/
	 function odb_get_unused_tags($analyzing = false) {
		 global $wpdb, $odb_class;
		 
		 $res_arr = array();

		// LOOP THROUGH SITES
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf ("
			SELECT '%s' AS site,
				a.term_id AS term_id, a.name AS name
			  FROM `%sterms` a, `%sterm_taxonomy` b
			 WHERE a.term_id = b.term_id
			   AND b.taxonomy = 'post_tag'
			   AND b.term_taxonomy_id NOT IN (
				SELECT term_taxonomy_id
				  FROM %sterm_relationships
			    )
			ORDER BY name
			", $prefix,	$prefix, $prefix, $prefix);

			$res = $wpdb->get_results($sql, ARRAY_A);
	
			for($j = 0; $j < count($res); $j++) {
				array_push($res_arr, $res[$j]);
			} // for($j = 0; $j < count($res); $j++)
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)
		return $res_arr;		 
	 } // odb_get_unused_tags
	 

	/********************************************************************************************
	 *	DELETE UNUSED TAGS
	 ********************************************************************************************/
	function odb_delete_unused_tags($results, $scheduler, $analyzing = false) {
		global $wpdb, $odb_class;

		$nr = 1;
		$total_deleted = count($results);
		for($i=0; $i<count($results); $i++) {
			if (!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['name']; ?></td>
	</tr>
	<?php
			} // if (!$scheduler)
			
			if (!$analyzing) {
				$sql_del = sprintf ("
				DELETE FROM %sterm_taxonomy
				 WHERE term_id = %d
				", $results[$i]['site'], $results[$i]['term_id']);
				$wpdb->get_results($sql_del);
				
				$sql_del = sprintf ("
				DELETE FROM %sterms
				 WHERE term_id = %d
				", $results[$i]['site'], $results[$i]['term_id']);
				$wpdb->get_results($sql_del);
			} // if (!$analyzing)
	
			$nr++;				
		} // for($i=0; $i<count($results); $i++)
		
		return $total_deleted;
	} // odb_delete_unused_tags()


	/********************************************************************************************
	 *	IS THE UNUSED TAG USED IN ONE OR MORE SCHEDULED POSTS?
	 ********************************************************************************************/
	function odb_delete_tags_is_scheduled($term_id, $odb_prefix) {
		global $wpdb;
	
		$sql_get_posts = sprintf ("
		SELECT p.post_status
		  FROM %sterm_relationships t, %sposts p
		 WHERE t.term_taxonomy_id = '%s'
		   AND t.object_id        = p.ID
		", $odb_prefix, $odb_prefix, $term_id);
	
		$results_get_posts = $wpdb->get_results($sql_get_posts);
		for($i=0; $i<count($results_get_posts); $i++)
			if($results_get_posts[$i]->post_status == 'future') return true;
	
		return false;	
	} // odb_delete_tags_is_scheduled()
	
	/********************************************************************************************
	 *	DELETE TRANSIENTS (v4.7.3)
	 ********************************************************************************************/
	function odb_delete_transients($scheduler, $analyzing = false) {
		global $wpdb, $odb_class;
		
		$total_deleted = 0;
		$nr = 1;

		if ($odb_class->odb_rvg_options['clear_transients'] !== 'Y' && $odb_class->odb_rvg_options['clear_transients'] !== 'A') {
			// DISABLED
			return $total_deleted;
		}

		// ONE MINUTE DELAY
		$delay = time() - 60;
		
		// LOOP THROUGH SITES		
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			if ($odb_class->odb_rvg_options['clear_transients'] == 'Y') {
				// EXPIRED TRANSIENTS ONLY 
				$sql = "
				SELECT '" . $prefix . "' AS site,
					`option_name`
				FROM " . $prefix . "options
				WHERE (
					option_name LIKE '_transient_timeout_%'
					OR option_name LIKE '_site_transient_timeout_%'
				)
				AND option_value < '" . $delay . "'" .  "
				ORDER BY `option_name`
				";
				
				// DELETE EXPIRED TRANSINTS FOR THIS (MULTI-) SITE
				$sqldel = "
				DELETE FROM " . $prefix . "options
				WHERE (
					option_name LIKE '_transient_timeout_%'
					OR option_name LIKE '_site_transient_timeout_%'
				)
				AND option_value < '" . $delay . "'" .  "		
				";
			} else if ($odb_class->odb_rvg_options['clear_transients'] == 'A') {
				// ALL TRANSIENTS 
				$sql = "
				SELECT '" . $prefix . "' AS site,
					`option_name`
				FROM " . $prefix . "options
				WHERE (
					option_name LIKE '_transient_timeout_%'
					OR option_name LIKE '_site_transient_timeout_%'
				)
				ORDER BY `option_name`
				";
				
				// DELETE TRANSINTS FOR THIS (MULTI-) SITE
				$sqldel = "
				DELETE FROM " . $prefix . "options
				WHERE (
					option_name LIKE '_transient_timeout_%'
					OR option_name LIKE '_site_transient_timeout_%'
				)
				";
			} // if ($odb_class->odb_rvg_options['clear_transients'] == 'Y')
			
			$res = $wpdb->get_results($sql, ARRAY_A);
			// ACTUALLY DELETE THE TRANSIENTS
			if (!$analyzing) $resdel = $wpdb->get_results($sqldel, ARRAY_A);
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)

		return $res;
	} // odb_delete_transients()

	/********************************************************************************************
	 *	GET PINGBACKS AND TRACKBACKS
	 ********************************************************************************************/
	function odb_get_pingbacks($analyzing = false) {

		global $wpdb, $odb_class;
				
		$res_arr = array();
		
		// LOOP THROUGH SITES
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];;
			
			$sql = sprintf ("
			SELECT '%s' AS site,
				`comment_ID`,
				`comment_type`,
				`comment_author`,
				`comment_date`
			FROM %scomments
			WHERE (
				`comment_type` = 'pingback' OR `comment_type` = 'trackback'
			)
			ORDER BY `comment_type`, `comment_author`
			", $prefix, $prefix);

			$res = $wpdb->get_results($sql, ARRAY_A);
	
			for($j = 0; $j < count($res); $j++) {
				array_push($res_arr, $res[$j]);
			} // for($j = 0; $j < count($res); $j++)
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)	
		return $res_arr;
	} // odb_get_pingbacks


	/********************************************************************************************
	 *	DELETE PINGBACKS AND TRACKBACKS
	 ********************************************************************************************/
	function odb_delete_pingbacks($results, $scheduler, $analyzing) {
		global $wpdb, $odb_class;

		$nr = 1;
		$total_deleted = count($results);
		for($i=0; $i<count($results); $i++) {
			if (!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['comment_type']?></td>      
	  <td valign="top"><?php echo $results[$i]['comment_author']?></td>
	  <td valign="top" nowrap="nowrap"><?php echo $results[$i]['comment_date']; ?></td>
	</tr>
	<?php
			} // if (!$scheduler)
			
			if (!$analyzing) {
				for($j=0; $j<count($results); $j++) {
					// DELETE METADATA FOR THIS COMMENT (IF ANY)
					$sql = sprintf ("
					DELETE FROM %scommentmeta
					 WHERE `comment_id` = %d
					", $results[$j]['site'], $results[$j]['comment_ID']);
					$wpdb->get_results($sql);
					
					$sql = sprintf ("
					DELETE FROM %scomments
					WHERE (
						`comment_type` = 'pingback'
						OR `comment_type` = 'trackback'
					)	
					", $results[$j]['site']);
					$wpdb->get_results($sql);			
				} // for($j=0; $j<count($results); $j++)
			} // if (!$analyzing)
	
			$nr++;				
		} // for($i=0; $i<count($results); $i++)
		
		return $total_deleted;
	} // odb_delete_pingbacks()


	/********************************************************************************************
	 *	GET OEMBED CACHE
	 ********************************************************************************************/
	function odb_get_oembed($analyzing = false) {
		global $wpdb, $odb_class;
				
		$res_arr = array();
		
		// LOOP THROUGH SITES
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf ("
			SELECT '%s' AS site,
				`meta_id`,
				`meta_key`,
				`meta_value`
			FROM %spostmeta
			WHERE `meta_key` LIKE '_oembed_%%'
			ORDER BY `meta_key`
			", $prefix, $prefix);

			$res = $wpdb->get_results($sql, ARRAY_A);
	
			for($j = 0; $j < count($res); $j++) {
				array_push($res_arr, $res[$j]);
			} // for($j = 0; $j < count($res); $j++)
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)	
		return $res_arr;
	} // odb_get_oembed()
	

	/********************************************************************************************
	 *	CLEAR OEMBED CACHE
	 ********************************************************************************************/
	function odb_delete_oembed($results, $scheduler, $analyzing) {
		global $wpdb, $odb_class;
		
		$nr = 1;
		$total_deleted = count($results);
		for($i=0; $i<count($results); $i++) {
			if (!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['meta_key']?></td>      
	  <td valign="top"><?php echo $results[$i]['meta_value']?></td>
	</tr>
	<?php
			} // if (!$scheduler)

			if (!$analyzing) {
				// DELETE COMMENTS			
				$sql = sprintf ("
				DELETE FROM %spostmeta
				WHERE `meta_key` LIKE '_oembed_%%'	
				", $results[$i]['site']);
				
				$wpdb->get_results($sql);
			} // if (!$analyzing)
	
			$nr++;				
		} // for($i=0; $i<count($results); $i++)
		
		return $total_deleted;
	} // odb_delete_oembed()
	

	/********************************************************************************************
	 *	GET ORPHAN POSTMETA AND MEDIA RECORDS
	 ********************************************************************************************/
	function odb_get_orphans($results, $scheduler, $analyzing) {
		global $wpdb, $odb_class;
			
		$res_arr = array();

		// LOOP THROUGH SITES
		for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++) {
			$prefix = $odb_class->odb_ms_prefixes[$i];
			
			$sql = sprintf ("
			SELECT '%s' AS site,
				`ID`,
				'post' AS type,
				`post_title`,
				`post_modified`,
				 '' AS meta_key,
				 '' AS meta_value
			  FROM %sposts
			 WHERE ID NOT IN (SELECT post_id FROM %spostmeta)
			   AND post_status = 'auto-draft'
			 ORDER BY `ID`
			", $prefix, $prefix, $prefix);

			$results = $wpdb->get_results($sql, ARRAY_A);
			for ($j = 0; $j < count($results); $j++) {
				array_push($res_arr, $results[$j]);
			} // for ($j = 0; $j < count($results); $j++)
			
			// DELETE POSTMETA ORPHANS
			$sql = sprintf ("
			SELECT '%s' AS site,
				`post_id` AS ID, 
				'meta' AS type,
				'' AS post_title,
				'' AS post_modified,`meta_key`, `meta_value`
			  FROM %spostmeta
			 WHERE post_id NOT IN (SELECT ID FROM %sposts)
			 ORDER BY `meta_key`
			", $prefix, $prefix, $prefix);			

			$results = $wpdb->get_results($sql, ARRAY_A);
			for ($j = 0; $j < count($results); $j++) {
				array_push($res_arr, $results[$j]);
			} // for ($j = 0; $j < count($results); $j++)
		} // for($i=0; $i<count($odb_class->odb_ms_prefixes); $i++)
		return $res_arr;
	} // odb_get_orphans()
	 
	 
	/********************************************************************************************
	 *	DELETE ORPHAN POSTMETA AND MEDIA RECORDS
	 ********************************************************************************************/
	function odb_delete_orphans($results, $scheduler, $analyzing) {
		
		global $wpdb, $odb_class;
	
		$nr = 1;
		$total_deleted = count($results);
		for($i=0; $i<count($results); $i++) {
			if (!$scheduler) {
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $nr; ?></td>
	  <td align="left" valign="top"><?php echo $results[$i]['site']?></td>
	  <td valign="top"><?php echo $results[$i]['type']?></td>           
	  <td valign="top"><?php echo $results[$i]['ID']?></td>      
	  <td valign="top"><?php echo $results[$i]['post_title']?></td>
	  <td valign="top" nowrap="nowrap"><?php echo substr($results[$i]['post_modified'], 0, 10); ?></td>
      <td valign="top" nowrap="nowrap"><?php echo $results[$i]['meta_key']; ?></td>
      <td valign="top" nowrap="nowrap"><?php echo $results[$i]['meta_value']; ?></td>
	</tr>
	<?php
			} // if (!$scheduler)	
			
			if (!$analyzing) {
				for($j=0; $j<count($results); $j++) {
					// DELETE METADATA FOR THIS COMMENT (IF ANY)
					if ($results[$j]['type'] == 'meta') {
						$sql = sprintf ("
						DELETE FROM %spostmeta
						 WHERE `post_id` = %d
						", $results[$j]['site'], $results[$j]['ID']);
					} else if ($results[$j]['type'] == 'post') {
						$sql = sprintf ("
						DELETE FROM %sposts
						 WHERE `ID` = %d
						", $results[$j]['site'], $results[$j]['ID']);		
					} // if ($results[$j]['type'] == 'meta')
					//echo 'SQL: ' . $sql.'<br>';
					$wpdb->get_results($sql);
				}// for($j=0; $j<count($results); $j++)
			} // if (!$analyzing)
			$nr++;				
		} // for($i=0; $i<count($results); $i++)
		
		return $total_deleted;		
	} // odb_delete_orphans()


	/********************************************************************************************
	 *	OPTIMIZE DATABASE TABLES
	 ********************************************************************************************/
	function odb_optimize_tables($scheduler) {
		global $odb_class, $wpdb;

		$cnt = 0;
		for ($i=0; $i<count($odb_class->odb_tables); $i++) {
			if(!isset($odb_class->odb_rvg_excluded_tabs[$odb_class->odb_tables[$i][0]])) {
				# TABLE NOT EXCLUDED
				$cnt++;

				$sql = sprintf ("
				SELECT engine, (data_length + index_length) AS size, table_rows
				  FROM information_schema.TABLES
				 WHERE table_schema = '%s'
				   AND table_name   = '%s'
				", DB_NAME, $odb_class->odb_tables[$i][0]);
				$table_info = $wpdb->get_results($sql);

				if($odb_class->odb_rvg_options["optimize_innodb"] == 'N' && strtolower($table_info[0]->engine) == 'innodb') {
					// SKIP InnoDB tables
					$msg = __('InnoDB table: skipped...', 'rvg-optimize-database');
				} else {
					// v4.6.3
					if (strtolower($table_info[0]->engine) == 'myisam') {
						$result = $this->odb_optimize_myisam($odb_class->odb_tables[$i][0]);
						$msg    = $result[0]->Msg_text;
						if ($msg == 'OK') {
							$msg = __('<span class="odb-optimized">TABLE OPTIMIZED</span>', 'rvg-optimize-database');
						} else if ($msg == 'Table is already up to date') {
							$msg = __('Table is already up to date', 'rvg-optimize-database');
						}
					} else {
						$result = $this->odb_optimize_innodb($odb_class->odb_tables[$i][0]);
						$msg    = $result[0]->Msg_text;
						if ($msg == 'Table is already up to date') {
							$msg = __('Table is already up to date', 'rvg-optimize-database');
						} else {
							$msg = __('<span class="odb-optimized">TABLE OPTIMIZED</span>', 'rvg-optimize-database');
						}
					} // if (strtolower($table_info[0]->engine) == 'myisam')
				} // if($odb_class->odb_rvg_options["optimize_innodb"] == 'N' && strtolower($table_info[0]->engine) == 'innodb')
				
				if (!$scheduler)
				{	// NOT FROM THE SCEDULER
	?>
	<tr>
	  <td align="right" valign="top"><?php echo $cnt?>.</td>
	  <td valign="top" class="odb-bold"><?php echo $odb_class->odb_tables[$i][0] ?></td>
	  <td valign="top"><?php echo $msg ?></td>
	  <td valign="top"><?php echo $table_info[0]->engine ?></td>
	  <td align="right" valign="top"><?php echo $table_info[0]->table_rows ?></td>
	  <td align="right" valign="top"><?php echo $odb_class->odb_utilities_obj->odb_format_size($table_info[0]->size) ?></td>
	</tr>
	<?php
				} // if (!$scheduler)
			} // if(!$excluded)
		} // for ($i=0; $i<count($tables); $i++)
		return $cnt;
		
	} // odb_optimize_tables()


	/********************************************************************************************
	 *	OPTIMIZE A MyISAM TABLE
	 ********************************************************************************************/	
	function odb_optimize_myisam($table_name) {
		global $wpdb;
		$query  = "OPTIMIZE TABLE " . $table_name;
		return $wpdb->get_results($query);
	} // odb_optimize_myisam()


	/********************************************************************************************
	 *	OPTIMIZE AN InnoDB TABLE
	 ********************************************************************************************/		
	function odb_optimize_innodb($table_name) {
		global $wpdb;

		$query  = "OPTIMIZE TABLE " . $table_name;
		return $wpdb->get_results($query);
		
/*		// https://www.percona.com/blog/2010/12/09/mysql-optimize-tables-innodb-stop/
		$query = "SHOW KEYS FROM " . $table_name . " WHERE Key_name <> 'PRIMARY'";
		$result = $wpdb->get_results($query);
		if (count($result) > 0) {
			for ($i = 0; $i < count($result); $i++) {
				$key_name = $result[$i]->Key_name;
				
				$query = "ALTER TABLE " . $table_name . " DROP KEY " . $key_name;
				$result = $wpdb->get_results($query);
		
				$query = "ALTER TABLE " . $table_name . " add key(" . $key_name . ")";
				$result = $wpdb->get_results($query);
			} // for ($i = 0; $i < count($result); $i++)
			return __('<span class="odb-optimized">TABLE OPTIMIZED</span>', 'rvg-optimize-database');
		} else {
			return __('Table is already up to date', 'rvg-optimize-database');
		} // if (count($result) > 0*/
	} // odb_optimize_innodb()
	
} // ODB_Cleaner
?>