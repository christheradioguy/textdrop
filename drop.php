<?php
	$filePath='/tmp/';
	$maxLength=86400;

	function fail($reason)
	{
		echo '{"success": 0, "data":{"message": "'. $reason.'"}}';
	}

	function success($message,$id,$isJson=false)
	{
		if($isJson)
			echo '{"success": 1, "data":{"payload": '. $message.',"post_id":"'.$id.'"}}';
		else
			echo '{"success": 1, "data":{"message": "'. $message.'","post_id":"'.$id.'"}}';
	}

	//we are setting data
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if($payload=json_decode($_POST['payload'],true))
		{
			if (strlen($payload['key'])>5)
			{
				$payload['time']=time();
				if (preg_match("/^[a-zA-Z0-9]+$/", $payload['id']))
				{
					if (file_exists($filePath.$payload['id']))
					{
						$old=file_get_contents($filePath.$payload['id']);
						if ($old=json_decode($old,true))
						{
							if ($old['key']==$payload['key'])
							{
								$contents=json_encode($payload);
								if(file_put_contents($filePath.$payload['id'],$contents))
									success("sucessfully saved POST ID ". $payload['id'],$payload['id']);
								else
									fail("System error while saving payload");
							}
							else
								fail("key does not match old record");
						}
					}
					else
					{
						$contents=json_encode($payload);
                                                if(file_put_contents($filePath.$payload['id'],$contents))
	                                                success("sucessfully saved POST ID ". $payload['id'],$payload['id']);
                                                else
                                                       fail("System error while saving payload");

					}
				}
				else
				{
					fail("id is not suppled or is invlaid (only a-z, 0-9 allowed)");
				}
			}
			else
				fail("key is a required field.  It must be greater the 5 characters");
		}
		else
			fail("Payload is not valid JSON");
	}

	//we are retriving data
	else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		if (preg_match("/^[a-zA-Z0-9]+$/", $_GET['id']))
		{
			if (strlen($_GET['key'])>5)
			{
				if ($contents=file_get_contents($filePath.$_GET['id']))
				{
					$contents=json_decode($contents,true);
					if (time()-$maxLength > $contents['time'] )
						fail("It has been longer then ". $maxLength. " seconds since data was uploaded");
					else
					{
						if ($contents['key']==$_GET['key'])
						{
							unset($contents['key']);
							unset($contents['time']);
							unset($contents['id']);
							$output=json_encode($contents);
							success($output,$_GET['id'],true);
						}
						else
							fail("Key does not match");
					}
				}
				else
					fail("saved ID ". $_GET['id']. " not found");
			}
			else
				fail("key is a required GET field");
		}
		else
			fail("id is a required GET field");
	}
