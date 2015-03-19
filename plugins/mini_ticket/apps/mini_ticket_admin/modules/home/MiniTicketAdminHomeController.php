<?php

class MiniTicketAdminHomeController extends MiniTicketAdminController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function loadView($parameters = array())
	{
		//If comment post insert it
		if(isset($_POST['type']) AND $_POST['type'] == "comment")
		{ 
			$ticket_id = strip_tags($_POST['ticket_id']);
			$comment = strip_tags($_POST['comment']);
			$first_name = strip_tags($_POST['first_name']); //this could come from a logged in user instead
			$last_name= strip_tags($_POST['last_name']); //this could come from a logged in user instead
			$email = strip_tags($_POST['email']); //this could come from a logged in user instead
			$associated_id = 1; //This would be set from your framework
			$user_id = 1; //this would be set from your framework

			MiniTicket::newTicketComment($comment, $first_name, $last_name, $email, $ticket_id, $associated_id, $user_id);

			//Email notification goes here...
		}

		//Fetch the appropriate data
		$this->ticket = MiniTicket::getTicket($_GET['id']);
		$this->comments = MiniTicket::getTicketsComments($_GET['id']);
	}

	public function loadDefault($parameters = array())
	{
		$this->tickets = MiniTicket::getAllTickets();
	}
}