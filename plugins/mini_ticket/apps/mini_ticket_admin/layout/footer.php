<br><br><br><br>

     <!-- Footer
      ================================================== -->
      <hr>

      <footer id="footer">
        <p class="pull-right"><a href="#top">Back to top</a></p>
        <div class="links">
          <a href="https://github.com/andrewvt/MiniTicket/">GitHub</a>
        </div>
        Code licensed under the <a href="http://www.apache.org/licenses/LICENSE-2.0">Apache License v2.0</a>.<br/>
        Based on <a href="http://twitter.github.com/bootstrap/">Bootstrap</a>. Hosted on <a href="http://pages.github.com/">GitHub</a>. Icons from <a href="http://fortawesome.github.com/Font-Awesome/">Font Awesome</a>. Web fonts from <a href="http://www.google.com/webfonts">Google</a>.</p>
      </footer>

    </div><!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="js/jquery.dataTables.js"></script>
    <script src="js/bootstrap-modal.js"></script>
    <script src="js/jquery.smooth-scroll.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/bootswatch.js"></script>
    <script src="js/jquery.foundation.reveal.js"></script>


	<script>
	$(document).ready(function() {
		$('#tickets').dataTable( {} );

    $("body").on("click", "#ticket_submit", function(e){
      e.preventDefault();
      
      //validate form
      if($("#ticket_subject").val() != "" && $("#ticket_message").val() != "")
      {
        $('#ticket_submit').click(false);
        //post values to end point ticket_post
        $.post("controller.php", { 
            'type' : 'ticket',
            'first_name' : $("#ticket_first_name").val(),
            'last_name' : $("#ticket_last_name").val(),
            'email' : $("#ticket_email").val(),
            'subject' : $("#ticket_subject").val(), 
            'message' : $("#ticket_message").val() 
          }, function(msg) {
          if(msg == "Success"){
            $('.close-reveal-modal').trigger('click'); //close dialogue
            $("#ticket_first_name").val('');
            $("#ticket_last_name").val('');
            $("#ticket_email").val('');
            $("#ticket_subject").val('');
            $("#ticket_message").val('');
            $('#ticket_submit').click(true);
            alert("Thanks for submitting your ticket. We’re totally on it.");
            location.reload();
          }
          else alert("Oops, something went wrong... how embarassing... please let us know via email and we can fix this!");
        });
      }
      else alert("Both subject and message are required.");
     });
	
    $('body').on("change", ".update_status", function(e) {
      //post values to end point ticket_post
      $.post("controller.php", { 
          'type' : 'status',
          'ticket_id' : $("#ticket_id").val(), 
          'status' : $(".update_status").val() 
        }, function(msg) {
          if(msg=="Success") alert("Ticket status succesfully updated.");
          else alert("There was an error updating the ticket.");
      });
    });

    $('body').on("click", "#comment_submit", function(e) {
        e.preventDefault();
        $("#comment_form").submit();
    });

  });
	</script>
  </body>
</html>