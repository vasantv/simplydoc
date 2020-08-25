  <div id="footer">
    <hr class="blueline"/>
		<center>
			<ul id="nav">
				<li><a href="about.php">about</a></li>
				<li><a href="terms.php">terms of use</a></li>
				<li><a href="privacy.php">privacy</a></li>
				<li><a href="faq.php">questions</a></li>
				<li><a href="contact.php">contact</a></li>
				<li><a href="http://www.medcenter.medly.in">blog</a></li>
			</ul>
		</center>

    </div>
  
  <script type="text/javascript">
    //for the sticky footer
    //source: http://www.hardcode.nl/archives_139/article_244-jquery-sticky-footer.htm

    $(function(){
    positionFooter(); 
    function positionFooter(){
      if($(document).height() < $(window).height()){
        $("#footer").css({position: "absolute",top:($(window).scrollTop()+$(window).height()-$("#pageFooterOuter").height())+"px"})
      } 
    }
   
    $(window)
      .scroll(positionFooter)
      .resize(positionFooter)
    });

  </script>
