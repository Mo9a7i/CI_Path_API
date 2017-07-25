<?php

class Path_model extends CI_Model{

	var $path_vars;
	var $headers = array();
	
	public function __construct() {
		parent::__construct();
		$this->path_vars  = new stdClass();
		# if you want to auto-load the model and not use $this->authorize function, put your credentials below.
		$this->path_vars->username = 'your@email.com';
		$this->path_vars->password = 'password';
		$this->path_vars->authorization = base64_encode($this->path_vars->username.':'.$this->path_vars->password);
		
		#if you want to obtain oauth_token, you have to get your client_id by sniffing your device's (mobile) 
		#client_id while doing normal requests. Then call authenticate function to get oauth_token
		$this->path_vars->oauth_token = '';  //user login function to get token if needed
		$this->path_vars->client_id = '';

		#Location parameter is needed in most of the post requests
		#Update the location if you want your posts to originate from a default location
		$this->path_vars->location = $this->getLocation('24.242424', '11.111111', '10.000');  //Long, Lat, Distance(maybe accuracy)
		
		#Do not touch below
		$this->path_vars->api_url = "https://api.path.com/3/";	
		$this->path_vars->time = (string)$this->current_millis();
		$this->headers = array(
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FAILONERROR => false,
			CURLOPT_HTTPHEADER => array(
				'xPath Wrapper','Authorization: Basic '.$this->path_vars->authorization, 
				'Accept-Charset: utf-8'
			),
		);	
	}

#Post Functions
	#Seen it
	public function postSeenIt($moment_ids = array())
	{	
		#Set request url
		$url = $this->path_vars->api_url."moment/seenit";

		#Set Post Content
		$post_array = array(
			'moment_ids' => implode(',',$moment_ids),
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
		
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Played it
	public function postPlayedIt($moment_id)
	{	
		#Set request url
		$url = $this->path_vars->api_url."moment/played/add";

		#Set Post Content
		$post_array = array(
			'moment_id' => $moment_id,
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
		
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}

	#Post a thought
	public function postThought($thought,$private = false)
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/add";
		
		# Special Request Variables
		$custom_id = $this->getUUID();
				
		#Set Post Content
		$post_array = array(
			'location' => $this->path_vars->location,
			'type' => 'thought',
			'custom_id' => $custom_id, 
			'thought' => array(
				'body' => $thought,
			),
			'created' => $this->path_vars->time,
		);
		
		if($private)
		{
			$post_array['ic'] = 1;
		}
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers (Length might not be necessary)
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
	
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Post a photo
	# $photo should be the content of a jpeg file. Either read a file through read_file() function or produce one using GD or ImageMagick
	public function postPhoto($photo,$private = false,$filename=null)
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/add";
		
		# Special Request Variables
		$custom_id = $this->getUUID();
		
		#Set Post Content
		$post_array = array(
			'location' => $this->path_vars->location,
			'type' => 'photo',
			'custom_id' => $custom_id, 
			'photo' => array(),
			'created' => $this->path_vars->time,
		);
		
		if($private)
		{
			$post_array['ic'] = 1;
		}
		
		$data = $this->getPhotoMime($post_array,$photo,$filename);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary']));
		
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Add a comment
	public function postComment($comment, $moment_id)
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/comments/add";
		
		# Special Request Variables
		
		$custom_id = $this->getUUID();
				
		#Set Post Content
		$post_array = array(
			'location' => $this->path_vars->location,
			'moment_id' => $moment_id,
			'comment_custom_id' => $custom_id, 
			'body' => $comment,
			'created' => $this->path_vars->time,
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
	
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Add an emotion to a moment
	# emotion_types:
	#	- love
	#	- sad
	#	- surprise
	#	- laugh
	#	- happy
	public function addEmotion($moment_id, $emotion_type = 'love')
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/emotion/add";
		
		#Set Post Content
		$post_array = array(
			'location' => $this->path_vars->location,
			'emotion_type' => $emotion_type,
			'moment_id' => $moment_id,
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
		
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		//var_dump($response);
		return $response;
	}
	
	public function addFriend($user_id)
	{
		#Set request url
		$url = $this->path_vars->api_url."friend_request/add";
		
		#Set Post Content
		$post_array = array(
			'user_ids' => array($user_id),
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
		
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		//var_dump($response);
		return $response;
	}
	
	#Delete moment
	public function deleteMoment($moment_id)
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/delete";
		
		# Special Request Variables
						
		#Set Post Content
		$post_array = array(
			'moment_id' => $moment_id,
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
	
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#login to get token
	public function authenticate()
	{
		#Set request url
		$url = $this->path_vars->api_url."user/authenticate";

		#Set Post Content
		$post_array = array(
			'login' => $this->path_vars->username,
			'password' => $this->path_vars->password,
			'client_id' => $this->path_vars->client_id, 
			'reactivate_user' => 1,
		);
		
		$data = $this->getMime($post_array);
		
		#Set Request Headers
		$this->updateHttpHeader(array('Content-Type: multipart/form-data; boundary='.$data['mime_boundary'],'Content-Length: '.$data['length']));
		
		#Do the request		
		$response = $this->curl->simple_post($url,$data['content'],$this->headers);
		$response = json_decode($response);
		
		return $response;
	}

#Get Functions
	#Get Home screen
	public function getPath($limit = 48, $since = 86400 ,$all_friends = 1) /* /default one day*/
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/feed/home";
				
		#Set Post Content
		$get_array = array(
			'newer_than' => $this->path_vars->time - $since,
			/* 'older_than' => , */ #Add older than while paging
			'limit' => $limit,
			'all_friends' => $all_friends,
		);
		
		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,$get_array,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Get a user's home
	public function getUserFeed($limit = 48,$user_id = null,$newer_than='',$older_than='')
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/feed";
				
		#Set Post Content
		$get_array = array(
			'limit' => $limit,
		);
		
		if(!is_null($user_id))
		{
			$get_array['user_id'] = $user_id;
		}
		if(!empty($older_than))
		{
			$get_array['older_than'] = $older_than;
		}
		if(!empty($newer_than))
		{
			$get_array['newer_than'] = $newer_than;
		}
		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,$get_array,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Get Moment
	public function getMoment($moment_id)
	{
		#Set request url
		$url = $this->path_vars->api_url."moment";
				
		#Set Post Content
		$get_array = array(
			'id' => $moment_id,
		);
		
		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,$get_array,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#Get seen_its
	public function getSeenIt($moment_id)
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/feed";
				
		#Set Post Content
		$get_array = array(
			'moment_id' => $moment_id,
		);
		
		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,$get_array,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#get user friends
	public function getFriends($user_id = null)
	{
		#Set request url
		$url = $this->path_vars->api_url."user/friends";
				
		#Set Post Content
		$get_array = array();
		if(!empty($user_id))
		{
			$get_array['user_id'] = $user_id;
		}
		
		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,$get_array,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
	#get comments (moment ids)
	public function getComments($moment_ids = array())
	{
		#Set request url
		$url = $this->path_vars->api_url."moment/comments";
				
		#Set Post Content
		$get_array = array(
			'moment_ids' => implode(',',$moment_ids),
		);
		
		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,$get_array,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}

	#get Activities
	public function getActivity()
	{
		#Set request url
		$url = $this->path_vars->api_url."activity";

		#Set Request Headers
		$this->updateHttpHeader();
		
		#Do the request		
		$response = $this->curl->simple_get($url,null,$this->headers);
		$response = json_decode($response);
		
		return $response;
	}
	
#Support Functions
	#Create Mime data for post requests.
	public function getMime($array)
	{
		$eol = "\r\n";
		$mime_boundary = md5(time());
		
		$data = '';
		$data .= '--' . $mime_boundary . $eol;
		$data .= 'Content-Disposition: form-data; name="post"' . $eol;
		$data .= 'Content-Type: text/plain; charset=UTF-8' . $eol;
		$data .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
		$data .= json_encode($array) . $eol;
		$data .= "--" . $mime_boundary . "--" . $eol;
		
		return array('length'=> strlen($data), 'mime_boundary'=> $mime_boundary, 'content'=>$data);
	}
	
	#Create Special Mime data for postPhoto requests
	public function getPhotoMime($array,$img,$image_name = null)
	{
		$eol = "\r\n";
		$mime_boundary = md5(time());
		if(is_null($image_name))
		{
			$image_name = "mtf_pbdyr_".mt_rand(1200,1999) . ".jpg";
		}
		$data = '';
		$data .= '--' . $mime_boundary . $eol;
		$data .= 'Content-Disposition: form-data; name="post"' . $eol;
		$data .= 'Content-Type: text/plain; charset=UTF-8' . $eol;
		$data .= 'Content-Transfer-Encoding: 8bit' . $eol . $eol;
		$data .= str_replace("]","}",str_replace("[","{",json_encode($array))) . $eol;
		$data .= "--" . $mime_boundary . $eol;
		$data .= 'Content-Disposition: form-data; name="photo"; filename="'.$image_name.'"' . $eol;
		$data .= 'Content-Type: image/jpeg' . $eol;
		$data .= 'Content-Transfer-Encoding: binary' . $eol. $eol;
		$data2 = $img . $eol;
		$data3 = "--" . $mime_boundary . "--" . $eol;
		
		$length = strlen($data) + mb_strlen($data2,'utf-8') + strlen($data3);
		$data = $data . $data2 . $data3;
		return array('length'=> $length, 'mime_boundary'=> $mime_boundary, 'content'=>$data);
	}
	
	#use this function to get a random location around the $long $lat $distance you provide to the function
	public function getLocation($long = '15.1111', $lat = '11.1111', $distance = '10.0000')
	{
		return array(
				"distance" => $this->randomizeLocation($long),
				"lng" => $this->randomizeLocation($lat),
				"lat" => $this->randomizeLocation($distance),
			);
	}
	
	#Add or subtract a random number
	public function randomizeLocation($location)
	{
		return $location + (rand(-1,1) * $this->rand(0.000111111,0.000999990));
	}
	
	#Better rand() function
	public function rand($min = 0.000111111, $max = 0.000999999)
    {
        return ($min + ($max - $min) * (mt_rand() / mt_getrandmax()));
    }
	
	#Return timestamp in milliseconds similar to Java CurrentMillis function
	function current_millis() 
	{
		list($usec, $sec) = explode(" ", microtime());
		return round(((float)$usec + (float)$sec) * 1000);
	}
	
	#Produce a Version 4 UUID for the custom_id parameter of post requests.
	public function getUUID() 
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand(0, 0xffff), mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0x0fff) | 0x4000,
		mt_rand(0, 0x3fff) | 0x8000,
		mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		)."local";
	}
		
	public function updateHttpHeader($array = array())
	{
		$initiatl_httpheaders = array(
			'xPath Wrapper','Authorization: Basic '.$this->path_vars->authorization, 
			'Accept-Charset: utf-8'
		);
		$this->headers[CURLOPT_HTTPHEADER] = array_merge($initiatl_httpheaders,$array);
	}

	public function authorize($username, $password)
	{
		if (!isset($username) || !isset($password) || empty($username) || empty($password)) {
			throw new Exception("Incorrect username or password"); 	
		}
		$this->path_vars->username = $username;
		$this->path_vars->password = $password;
		$this->path_vars->authorization = base64_encode($this->path_vars->username.':'.$this->path_vars->password);

	}

}
?>