<!-- start facebook code -->
<div id="fb-root"></div>
<script type="text/javascript">
window.fbAsyncInit = function() {
FB.init({appId: '295819117183669', status: true, cookie: true, xfbml: true});

(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);	
}(document, 'script', 'facebook-jssdk'));

}
</script>

<?php if($docName != "") { ?>
<div class="fb-like" ref="<?php echo $_GET['doctor']; ?>" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-action="recommend" data-font="arial"></div>
<?php }
else {
?>
<div class="fb-like" ref="Medly" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-font="arial"></div>
<?php } ?>
<!-- end facebook code -->