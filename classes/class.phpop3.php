<?php
define ("VERSION_PHPOP3","1.0.1");
/*
Version History
  1.0.1 (2009-11-16)
    1) Changed eregi() to preg_match() in phPOP3::pop3_stat() for php 5.3+
  1.0.0 (2009-07-11)
    Initial release
*/


  /**
  *	phPOP3 - A PHP implementation of the Post Office Protocol 3 (POP3)
  *	See readme.htm for detailed instructions on installation and usage.
  *
  *	This software and all associated files are released
  *	under the GNU Lesser Public License (LGPL), see license.txt for details.
  *
  * @version 1.0.2
  * @author  Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
  */

	class phPOP3
	{
		/**
		* socket identifier
		*
		* @var		int	socket identifier
		* @access	private
		*/

		var $socket = -1;

		/**
		* status
		*
		* @var		array	status
		* @access	private
		*/

		var $status;
		
		/**
		* constructor
		*
		* @param	string	server
		* @param	string	port
		* @param	string	username
		* @param	string	password
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function phPOP3( $server='', $port='', $username='', $password='' ){
		  if ($server) {
			if( $this->pop3_connect( $server, $port ) )	{
				if( $this->pop3_user( $username ) ){
					$this->pop3_pass( $password );
				}
			}
          }
		}

		/**
		* pop3_command
		*
		*	pop3_command passes a command to the socket
		*
		* @param	string	command
		*
		* @access	private
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_command( $command )
		{
			// check socket
			if( $this->socket!==-1)
			{
				// send command through socket
				$command	=	$command . "\r\n";
				fputs($this->socket, $command );
		
				// read status from socket
				$line			= fgets( $this->socket, 1024 );
		
				// update class status
				$this->status[ "lastresult" ]					= substr( $line, 0, 1 );
				$this->status[ "lastresultmessage" ]	= $line;
		
				// command was not executed successfully, return 0
				if($this->status[ "lastresult" ] != "+" ) return 0;
			}

			else
			{
				// command execution failed, return 0
				return 0;
			}

			// command was executed successfully, return 1
			return 1;
		}
	
		/**
		* pop3_connect
		*
		*	pop3_connect connects to a pop3 server
		*
		* @param	string	server
		* @param	string	port
		*
		* @access	private
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_connect( $server, $port )
		{
			// open socket connection to POP3 server
			if (!$this->socket = @fsockopen( $server, $port )) {
              print('Unknown server for mail identity: '.$server.", port:".$port);
              return;
            }

			// check if connection failed
			if( !$this->socket ) return 0;

			// read status from socket
			$line	= fgets( $this->socket, 1024 );

			// update class status
			$this->status[ "lastresult" ] = substr( $line, 0, 1 );
			$this->status[ "lastresultmessage" ] = $line;

			// check for an error
			if( $this->status[ "lastresult" ] != "+" ) return 0;

			// connection established, return 1
			return 1;
		}
	
		/**
		* pop3_user
		*
		*	pop3_user passes the username to a pop3 server connection
		*
		* @param	string	username
		*
		* @access	private
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_user( $username )
		{
			// pass username to socket
			$command	=	"USER " . $username;
			$result		=	$this->pop3_command( $command );
	
			// check, if command succeeded
			if( !$result )
			{
				// command failed, close socket
				fclose( $this->socket );
				$this->socket = -1;
			}

			// return result
			return $result;
		}
		
		/**
		* pop3_pass
		*
		*	pop3_pass passes the password to a pop3 server connection
		*
		* @param	string	password
		*
		* @access	private
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_pass( $password )
		{
			// pass password to socket
			$command	=	"PASS " . $password;
			$result		=	$this->pop3_command( $command );
	
			// check, if command succeeded
			if( !$result )
			{
				// command failed, close socket
				fclose( $this->socket );
				$this->socket = -1;
			}

			// retrun result
			return $result;
		}
		
		/**
		* pop3_stat
		*
		*	pop3_stat sends the STAT command to the socket
		*
		* @access	public
		* @author	Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_stat(){
			// pass command to socket
			$this->pop3_command( "STAT" );
	
			// parse result
			if( !preg_match("/+OK (.*) (.*)/i", $this->status[ "lastresultmessage" ], $result ) ) return 0;

			// return result
			return $result[1];
		}
		
		/**
		* pop3_list
		*
		*	pop3_list passes the LIST command to the socket
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_list()
		{
			// pass command to socket
			$this->pop3_command( "LIST" );
	
			// check, if command succeeded
			if( $this->status[ "lastresult" ] != "+" ) return 0;
	
			// init loop variable
			$i = 0;

			while( substr( $line = fgets( $this->socket, 1024 ), 0, 1 ) != "." )
			{
				// add new mail to mailbox array
				$mailbox[ $i ] = $line;

				// proceed to next mail
				$i++;
			}

			// set number of messages
			$mailbox[ "messages" ] = $i;

			// return mailbox list
			return $mailbox;
		}
		
		/**
		* pop3_retrieve
		*
		*	pop3_retrieve retrieves a message with a given message id from the server
		*
		* @param	string	message_id
		*
		*	@return	message	message
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>
		*/

		function pop3_retrieve( $message_id )
		{
			// pass command to socket
			$command	=	"RETR " . $message_id;
			$this->pop3_command( $command );
	
			// check, if command succeeded
			if( $this->status[ "lastresult" ] != "+" ) return 0;
	
			// init loop variable
			$i			= 0;
			$header	= 1;
	
			// parse message
			while(!(substr( $line = fgets( $this->socket, 1024 ), 0, 1 ) == "." and substr( $line, 1, 1) != "."))
			{
				if( !$header )
				{
                    $body[ $i ] = preg_replace("/^\.\./", ".", $line);
					$i++;
				}

				else
				{
					if( substr( $line, 0, 6 ) == "Date: " )
					{
						$date = substr( $line, 6 );
					}

					else if( substr( $line, 0, 6 ) == "From: " )
					{
						$from = substr( $line, 6 );
					}

					else if( substr( $line, 0, 10 ) == "Reply-To: " )
					{
						$reply_to = substr( $line, 10 );
					}

					else if( substr( $line, 0, 9 ) == "Subject: " )
					{
						$subject = substr( $line, 9 );
					}

					else if( substr( $line, 0, 4 ) == "To: " )
					{
						$to = substr( $line, 4 );
					}
				}

				if( ( $header == 1 ) && ( strlen( $line ) == 2 ) )
				{
					$header = 0;
				}
			}
	
			$body[ "lines" ] = $i;
	
			// create and return message object
			return new message( @$body, @$date, @$from, @$reply_to, @$subject, @$to );
		}
	
		/**
		* pop3_delete
		*
		*	pop3_delete delete a message with a given message id on the server
		*
		* @param	string	message id
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_delete( $message_id )
		{
			// pass command to socket
			$command	=	"DELE " . $message_id;
	
			// return result
			return $this->pop3_command( $command );
		}
		
		/**
		* pop3_quit
		*
		*	pop3_quit closes a pop3 socket connection
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_quit()
		{
			// send POP3 quit command
			return $this->pop3_command( "QUIT" );

			// close socket
			fclose( $this->socket );
			$this->socket = -1;
		}
		
		/**
		* pop3_show_error
		*
		*	pop3_show_error shows result of last issued command
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>, Mathias Meyer <pom@beatsteaks.de>
		*/

		function pop3_show_error()
		{
			// output last result message
			echo $this->status[ "lastresultmessage" ] . "<br>";
		}
  public function get_version(){
    return VERSION_PHPOP3;
  }

	}

  /**
  *	message class
  *
  * @version 1.0.0
  * @author  Sebastian Bergmann <sebastian.bergmann@web.de>
  */

	class message
	{
		/**
		* body
		*
		* @var		string	
		* @access	public
		*/

		var $body;

		/**
		* date
		*
		* @var		string	
		* @access	public
		*/

		var $date;

		/**
		* from
		*
		* @var		string	
		* @access	public
		*/

		var $from;

		/**
		* reply_to
		*
		* @var		string	
		* @access	public
		*/

		var $reply_to;

		/**
		* subject
		*
		* @var		string	
		* @access	public
		*/

		var $subject;

		/**
		* to
		*
		* @var		string	
		* @access	public
		*/

		var $to;

		/**
		* constructor
		*
		* @param	string	body
		* @param	string	date
		* @param	string	from
		* @param	string	reply_to
		* @param	string	subject
		* @param	string	to
		*
		* @access	public
		* @author	Sebastian Bergmann <sebastian.bergmann@web.de>
		*/

		function message( $body, $date, $from, $reply_to, $subject, $to )
		{
			$this->body			=	$body;
			$this->date			=	$date;
			$this->from			=	$from;
			$this->reply_to	=	$reply_to;
			$this->subject	=	$subject;
			$this->to				=	$to;
		}
	}
?>
