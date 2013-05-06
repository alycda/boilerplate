<!--END Content-->
	</div>
      <div id="push"></div>
    </div>

    <div id="footer">
      <div class="container">
        <p class="muted credit">&copy; 2013 <a href="http://alyda.me">Alyssa Davis</a>.</p>
      </div>
    </div>

    <!-- javascript
    ================================================== -->
    <script src="../js/vendor/jquery-latest.min.js" type="text/javascript"></script><!--wysihtml5 works on 1.8.3-->
    <script src="../js/vendor/bootstrap.min.js"></script>
    <script src="../js/vendor/bootstrap-datepicker.js" type="text/javascript"></script>
    <script src="../js/vendor/wysihtml5-0.3.0.min.js"></script>
    <script src="../js/vendor/bootstrap-wysihtml5.js"></script><!--this MUST be after wysihtml5-0.3.0.min.js-->
    <script src="../js/cms.js" type="text/javascript"></script>
    
    <script type="text/javascript">
    <?php if(basename($_SERVER['PHP_SELF']) == 'category_edit_config.php') { ?>
    	updateImageResizeNames();
    <?php } ?>
	<?php if (defined('KEEP_SESSION_ALIVE')) { ?>
		
			new PeriodicalExecuter(function() {
				new Ajax.Request('<?php echo $_SERVER['REQUEST_URI'] ?>', { 
					parameters: 'keep_session_alive=true', 
					method: 'post' 
				})
			}, <?php echo KEEP_SESSION_ALIVE ?>);
		
	<?php } ?>
	
	</script>
    </body>
</html>
