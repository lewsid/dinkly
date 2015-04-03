<?php
/* This is the base class for MiniTicket.  Don't change functionality here.  You can overload anything here
   in the Mini_Ticket.class.php file for your custom application. */

class BaseMiniTicket
{
	public static function getAllTickets() { return array(); }

    //This returns an array of a users tickets
	// public static function getUsersTickets($user_id, $associated_id = null) 
	// {
	// 	$pdo = self::getDatabaseConnection();
	// 	$associated_where = "";
	// 	if($associated_id) $associated_where = " AND associated_id = :associated_id";
	// 	$query = "SELECT t.*, (SELECT count(tc.id) FROM ticket_comment tc WHERE tc.ticket_id = t.id) AS count 
	// 				FROM ticket t 
	// 				WHERE t.user_id = :user_id AND t.is_deleted=0 $associated_where
	// 				ORDER BY t.updated_at DESC";
	// 	$params = array("user_id"  => $user_id);
	// 	if($associated_id) $params = array("user_id"  => $user_id, "associated_id"  => $associated_id);
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);

	// 	return $stmt->fetchAll();
	// }

	// //This returns an array of all tickets
	// public static function getAllTickets() 
	// {
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "SELECT t.*, (SELECT count(tc.id) FROM ticket_comment tc WHERE tc.ticket_id = t.id) AS count 
	// 				FROM ticket t 
	// 				WHERE t.is_deleted=0
	// 				ORDER BY t.updated_at DESC";
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute();

	// 	return $stmt->fetchAll();
	// }

	// //This returns a single ticket record
	// public static function getTicket($ticket_id) 
	// {
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "SELECT t.* FROM ticket t 
	// 				WHERE t.id = :param1 AND t.is_deleted=0";
	// 	$params = array("param1"  => $ticket_id);
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);

	// 	return $stmt->fetch();
	// }

	// //This returns an array of all comments for a single ticket
	// public static function getTicketsComments($ticket_id) 
	// {
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "SELECT tc.* FROM ticket_comment tc 
	// 				WHERE tc.ticket_id = :param1  AND tc.is_deleted=0
	// 				ORDER BY tc.created_at";
	// 	$params = array("param1"  => $ticket_id);
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);

	// 	return $stmt->fetchAll();
	// }

	// public static function newTicket($subject, $message, $status = "open", $first_name, $last_name, $email, $associated_id = 0, $user_id, $is_deleted = 0, $session = null, $time_spent = 0)
	// {
	// 	//create a new ticket
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "INSERT INTO ticket SET associated_id = :associated_id, user_id = :user_id, first_name  = :first_name, last_name  = :last_name, email  = :email, subject  = :subject, message  = :message, status  = :status, is_deleted  = :is_deleted, session  = :session, time_spent  = :time_spent, created_at = NOW(), updated_at = NOW() ";
	// 	$params = array("associated_id"  => $associated_id, "user_id"  => $user_id, "first_name"  => $first_name, "last_name"  => $last_name, "email"  => $email, "subject"  => $subject, "message"  => $message, "status"  => $status, "is_deleted"  => $is_deleted, "session"  => $session, "time_spent"  => $time_spent);
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);
	// }

	// public static function newTicketComment($comment, $first_name, $last_name, $email, $ticket_id, $associated_id = 0, $user_id, $is_deleted = 0)
	// {
	// 	//create a new ticket comment
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "INSERT INTO ticket_comment SET ticket_id = :ticket_id, associated_id = :associated_id, user_id = :user_id, first_name  = :first_name, last_name  = :last_name, email  = :email, comment  = :comment, is_deleted  = :is_deleted, created_at = NOW()";
	// 	$params = array("ticket_id"  => $ticket_id, "associated_id"  => $associated_id, "user_id"  => $user_id, "first_name"  => $first_name, "last_name"  => $last_name, "email"  => $email, "comment"  => $comment, "is_deleted"  => $is_deleted);
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);

	// 	//update parent ticket updated_at
	// 	self::updateTicket($ticket_id);
	// }

	// public static function updateTicketStatus($ticket_id, $status = "open")
	// {
	// 	//update the status of the ticket
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "UPDATE ticket SET status  = :status, updated_at = NOW() WHERE id = :ticket_id ";
	// 	$params = array("ticket_id"  => $ticket_id, "status"  => $status);
	// 	error_log($query);
	// 	error_log(print_r($params, true));
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);
	// }

	// public static function updateTicket($ticket_id)
	// {
	// 	//sets updated_at to right now
	// 	$pdo = self::getDatabaseConnection();
	// 	$query = "UPDATE ticket SET updated_at = NOW() WHERE id = :ticket_id ";
	// 	$params = array("ticket_id"  => $ticket_id);
	// 	$stmt = $pdo->prepare($query);
	// 	$stmt->execute($params);
	// }

	// //This generates a gravatar link
	// public static function getGravatarImage($email, $size = 80, $defaultImage = 'mm', $rating = 'G')
	// {
	//         return  $grav_url = "http://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . $defaultImage . "&s=" . $size . '&r=' . $rating;
	// }

	// public static function getDatabaseConnection()
	// {
	//         $host = 'localhost';
	// 	$dbname = 'mini_ticket';
	// 	$user = 'root';
	// 	$pass = 'root';
	
	//         $pdo = new PDO("mysql:host=".$host.";dbname=".$dbname."", $user, $pass);
	//         $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//         // always disable emulated prepared statement when using the MySQL driver
	//         $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
	//         return $pdo;
	// }
}
