<?php if($image!='' && $image!='default.jpeg') {  ?>
<div style='background: rgba(0, 0, 0, 0) url(data:image/png;base64,<?php echo base64_encode(file_get_contents("uploads/".$image));?>) repeat scroll 0 0;' class="banner_inner">
<div class="container">
			<h2><?php echo $title;?></h2>
			<label></label>
			<div class="clearfix"></div>
</div>
</div>
<?php } ?>


<?php if(isset($content)) { echo str_replace('\n','',$content); }?>



   